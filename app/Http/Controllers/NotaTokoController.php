<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use App\Models\NotaToko;
use App\Models\NotaTokoItem;
use App\Services\DocumentTemplateResolver;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use App\Services\NotaTokoPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class NotaTokoController extends Controller
{
    use ResolvesCompanyId;

    private function companyNotaTokos(int $companyId): Builder
    {
        return NotaToko::query()->where('company_id', $companyId);
    }

    private function monthCode(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month] ?? 'I';
    }

    private function previewNomor(int $companyId, ?string $tanggal = null): string
    {
        $date = $tanggal ? \Illuminate\Support\Carbon::parse($tanggal) : now();
        $last = $this->companyNotaTokos($companyId)
            ->whereYear('tanggal', $date->year)
            ->whereMonth('tanggal', $date->month)
            ->count();

        return sprintf('NT/%04d/%s/%s', $last + 1, $this->monthCode($date->month), $date->year);
    }

    private function loadRelations(NotaToko $notaToko): NotaToko
    {
        return $notaToko->load(['company', 'items']);
    }

    private function serializeNotaToko(NotaToko $notaToko): array
    {
        $previewVersion = $this->previewVersion($notaToko);

        return [
            'id' => $notaToko->id,
            'company_id' => $notaToko->company_id,
            'user_id' => $notaToko->user_id,
            'nomor' => $notaToko->nomor,
            'tanggal' => optional($notaToko->tanggal)->format('Y-m-d'),
            'customer_nama' => $notaToko->customer_nama,
            'customer_email' => $notaToko->customer_email,
            'alamat' => $notaToko->alamat,
            'keterangan' => $notaToko->keterangan,
            'subtotal' => (float) $notaToko->subtotal,
            'tax_percent' => (float) $notaToko->tax_percent,
            'tax_amount' => (float) $notaToko->tax_amount,
            'total' => (float) $notaToko->total,
            'payment_status' => $notaToko->payment_status,
            'payment_date' => optional($notaToko->payment_date)->format('Y-m-d'),
            'snapshot_data' => $notaToko->snapshot_data,
            'created_at' => optional($notaToko->created_at)->toISOString(),
            'updated_at' => optional($notaToko->updated_at)->toISOString(),
            'preview_url' => route('nota-toko.preview-pdf', [
                'notaToko' => $notaToko->id,
                'v' => $previewVersion,
            ]),
            'items' => $notaToko->items->map(fn (NotaTokoItem $item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
        ];
    }

    private function previewVersion(NotaToko $notaToko): int
    {
        $noteVersion = optional($notaToko->updated_at)->timestamp ?? now()->timestamp;
        $templatePath = app(DocumentTemplateResolver::class)->resolveTemplatePath($notaToko->company_id, 'nota_toko');

        if (! $templatePath) {
            return $noteVersion;
        }

        $relativePath = ltrim($templatePath, '/\\');

        if (! Storage::disk('public')->exists($relativePath)) {
            return $noteVersion;
        }

        return max($noteVersion, Storage::disk('public')->lastModified($relativePath));
    }

    private function calculateItems(array $items): array
    {
        $subtotal = 0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $qty = (float) $item['qty'];
            $unitPrice = (float) $item['unit_price'];
            $amount = $qty * $unitPrice;
            $subtotal += $amount;

            $normalizedItems[] = [
                'nama' => $item['nama'],
                'qty' => $qty,
                'satuan' => $item['satuan'],
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        return [
            'items' => $normalizedItems,
            'subtotal' => $subtotal,
        ];
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'customer_nama' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'paid'])],
            'payment_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $query = $this->companyNotaTokos($companyId);

        $notaTokos = (clone $query)
            ->with('items')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (NotaToko $notaToko) => $this->serializeNotaToko($this->loadRelations($notaToko)))
            ->values();

        return Inertia::render('NotaToko/Index', [
            'notaTokos' => $notaTokos,
            'stats' => [
                'total' => (clone $query)->count(),
                'unpaid' => (clone $query)->where('payment_status', 'unpaid')->count(),
                'paid' => (clone $query)->where('payment_status', 'paid')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

        return Inertia::render('NotaToko/Create', [
            'meta' => [
                'nomor_preview' => $this->previewNomor($companyId),
                'customers' => $customers,
                'defaults' => [
                    'tanggal' => now()->format('Y-m-d'),
                    'tax_percent' => 0,
                    'payment_status' => 'unpaid',
                ],
                'options' => [
                    'payment_status' => ['unpaid', 'paid'],
                    'satuan' => ['pcs', 'item', 'unit', 'month'],
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $validated = $this->validatePayload($request);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 0);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;
        $numberService = app(DocumentNumberService::class);
        $nomor = $numberService->next($companyId, 'nota_toko', $validated['tanggal']);

        $notaToko = DB::transaction(function () use ($companyId, $validated, $calculated, $taxPercent, $taxAmount, $total, $nomor) {
            $notaToko = NotaToko::create([
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['customer_nama'],
                'customer_email' => $validated['customer_email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'payment_date' => $validated['payment_date'] ?? null,
            ]);

            $notaToko->items()->createMany($calculated['items']);

            $notaToko->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forNotaToko($notaToko->load('items')),
            ]);

            return $notaToko;
        });

        return redirect()->route('nota-toko.show', $notaToko)->with('success', 'Nota Toko berhasil dibuat.');
    }

    public function show(NotaToko $notaToko): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($notaToko->company_id !== $companyId, 403);

        $this->loadRelations($notaToko);

        return Inertia::render('NotaToko/Show', [
            'notaToko' => $this->serializeNotaToko($notaToko),
            'snapshot' => $notaToko->snapshot_data ?? [],
        ]);
    }

    public function previewPdf(NotaToko $notaToko)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($notaToko->company_id !== $companyId, 403);

        $pdf = app(NotaTokoPdfService::class)->renderPreview($this->loadRelations($notaToko));
        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="nota-toko-preview.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function edit(NotaToko $notaToko): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($notaToko->company_id !== $companyId, 403);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

        $this->loadRelations($notaToko);

        return Inertia::render('NotaToko/Edit', [
            'notaToko' => $this->serializeNotaToko($notaToko),
            'meta' => [
                'customers' => $customers,
                'defaults' => [
                    'tanggal' => now()->format('Y-m-d'),
                    'tax_percent' => 0,
                    'payment_status' => 'unpaid',
                ],
                'options' => [
                    'payment_status' => ['unpaid', 'paid'],
                    'satuan' => ['pcs', 'item', 'unit', 'month'],
                ],
            ],
        ]);
    }

    public function update(Request $request, NotaToko $notaToko): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($notaToko->company_id !== $companyId, 403);

        $validated = $this->validatePayload($request);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 0);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;

        DB::transaction(function () use ($notaToko, $validated, $calculated, $taxPercent, $taxAmount, $total) {
            $notaToko->update([
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['customer_nama'],
                'customer_email' => $validated['customer_email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_status' => $validated['payment_status'] ?? $notaToko->payment_status,
                'payment_date' => $validated['payment_date'] ?? null,
            ]);

            $notaToko->items()->delete();
            $notaToko->items()->createMany($calculated['items']);

            $notaToko->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forNotaToko($notaToko->load('items')),
            ]);
        });

        return redirect()->route('nota-toko.show', $notaToko)->with('success', 'Nota Toko berhasil diperbarui.');
    }

    public function destroy(NotaToko $notaToko): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($notaToko->company_id !== $companyId, 403);

        $notaToko->delete();

        return redirect()->route('nota-toko.index')->with('success', 'Nota Toko berhasil dihapus.');
    }
}
