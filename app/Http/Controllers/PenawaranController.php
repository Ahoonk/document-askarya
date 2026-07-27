<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use App\Models\Mitra;
use App\Models\Penawaran;
use App\Models\PenawaranItem;
use App\Services\DocumentSnapshotService;
use App\Services\PenawaranPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenawaranController extends Controller
{
    use ResolvesCompanyId;

    private function companyPenawarans(int $companyId): Builder
    {
        return Penawaran::query()->where('company_id', $companyId);
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

    private function isNewPenawaranFormat(?string $nomor): bool
    {
        return is_string($nomor) && preg_match('/^PNW\/\d{4}\/[IVXLCDM]+\/\d{3}-ASK$/', $nomor) === 1;
    }

    private function extractPenawaranSequence(?string $nomor): ?int
    {
        if (! is_string($nomor) || $nomor === '') {
            return null;
        }

        if (preg_match('/^PNW\/\d{4}\/[IVXLCDM]+\/(\d{3})-ASK$/', $nomor, $match) === 1) {
            return (int) $match[1];
        }

        if (preg_match('/^PNW\/(\d{3,4})\/[IVXLCDM]+\/\d{4}$/', $nomor, $match) === 1) {
            return (int) $match[1];
        }

        return null;
    }

    private function nextPenawaranNumber(int $companyId, ?string $tanggal = null, ?int $ignorePenawaranId = null): string
    {
        $date = $tanggal ? \Illuminate\Support\Carbon::parse($tanggal) : now();
        $query = $this->companyPenawarans($companyId)
            ->whereYear('tanggal', $date->year)
            ->whereMonth('tanggal', $date->month);

        if ($ignorePenawaranId !== null) {
            $query->whereKeyNot($ignorePenawaranId);
        }

        $maxSequence = $query
            ->pluck('nomor')
            ->reduce(function (int $carry, ?string $nomor) {
                return max($carry, $this->extractPenawaranSequence($nomor) ?? 0);
            }, 0);

        return sprintf('PNW/%04d/%s/%03d-ASK', $date->year, $this->monthCode($date->month), $maxSequence + 1);
    }

    private function previewNomor(int $companyId, ?string $tanggal = null): string
    {
        return $this->nextPenawaranNumber($companyId, $tanggal);
    }

    private function meta(int $companyId): array
    {
        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

        $mitras = Mitra::where('company_id', $companyId)
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'email',
                'alamat',
                'nomor_penawaran',
                'nomor_invoice',
                'nomor_surat_jalan',
                'nomor_berita_acara',
                'template_penawaran_path',
                'template_invoice_path',
                'template_surat_jalan_path',
                'template_berita_acara_path',
            ]);

        return [
            'nomor_preview' => $this->previewNomor($companyId),
            'customers' => $customers,
            'mitras' => $mitras,
            'defaults' => [
                'tanggal' => now()->format('Y-m-d'),
                'tax_percent' => 11,
                'status' => 'draft',
                'jenis_kontrak' => 'satuan',
                'signature_role' => 'Direktur',
                'keterangan' => "1. Masa berlaku penawaran 7 Hari\n2. Garansi produk selama 1 Tahun\n3. Harga sudah termasuk pajak 11%",
            ],
            'options' => [
                'jenis_kontrak' => ['kontrak', 'satuan'],
                'signature_role' => ['Direktur', 'Manager', 'Sales'],
                'satuan' => ['month', 'pcs', 'item', 'unit'],
                'status' => ['draft', 'submitted', 'approved', 'rejected'],
            ],
        ];
    }

    private function loadPenawaranRelations(Penawaran $penawaran): Penawaran
    {
        return $penawaran->load([
            'company',
            'user:id,name,email',
            'approver:id,name,email',
            'mitra',
            'items',
        ]);
    }

    private function serializePenawaran(Penawaran $penawaran): array
    {
        return [
            'id' => $penawaran->id,
            'company_id' => $penawaran->company_id,
            'mitra_id' => $penawaran->mitra_id,
            'user_id' => $penawaran->user_id,
            'nomor' => $penawaran->nomor,
            'tanggal' => optional($penawaran->tanggal)->format('Y-m-d'),
            'customer_nama' => $penawaran->customer_nama,
            'to_company' => $penawaran->to_company,
            'to_address' => $penawaran->to_address,
            'jenis_kontrak' => $penawaran->jenis_kontrak,
            'signature_role' => $penawaran->signature_role,
            'keterangan' => $penawaran->keterangan,
            'subtotal' => (float) $penawaran->subtotal,
            'tax_percent' => (float) $penawaran->tax_percent,
            'tax_amount' => (float) $penawaran->tax_amount,
            'total' => (float) $penawaran->total,
            'status' => $penawaran->status,
            'invoice_date' => optional($penawaran->invoice_date)->format('Y-m-d'),
            'invoice_number' => $penawaran->invoice_number,
            'invoice_sequence' => $penawaran->invoice_sequence,
            'approved_by' => $penawaran->approved_by,
            'approved_at' => optional($penawaran->approved_at)->toISOString(),
            'snapshot_data' => $penawaran->snapshot_data,
            'company' => $penawaran->company ? [
                'id' => $penawaran->company->id,
                'name' => $penawaran->company->name,
                'address' => $penawaran->company->address,
                'logo' => $penawaran->company->logo,
            ] : null,
            'user' => $penawaran->user ? [
                'id' => $penawaran->user->id,
                'name' => $penawaran->user->name,
                'email' => $penawaran->user->email,
            ] : null,
            'approver' => $penawaran->approver ? [
                'id' => $penawaran->approver->id,
                'name' => $penawaran->approver->name,
                'email' => $penawaran->approver->email,
            ] : null,
            'mitra' => $penawaran->mitra ? [
                'id' => $penawaran->mitra->id,
                'nama' => $penawaran->mitra->nama,
                'email' => $penawaran->mitra->email,
                'alamat' => $penawaran->mitra->alamat,
                'nomor_penawaran' => $penawaran->mitra->nomor_penawaran,
                'nomor_invoice' => $penawaran->mitra->nomor_invoice,
                'nomor_surat_jalan' => $penawaran->mitra->nomor_surat_jalan,
                'nomor_berita_acara' => $penawaran->mitra->nomor_berita_acara,
                'template_penawaran_path' => $penawaran->mitra->template_penawaran_path,
                'template_invoice_path' => $penawaran->mitra->template_invoice_path,
                'template_surat_jalan_path' => $penawaran->mitra->template_surat_jalan_path,
                'template_berita_acara_path' => $penawaran->mitra->template_berita_acara_path,
            ] : null,
            'items' => $penawaran->items->map(fn (PenawaranItem $item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
        ];
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
                'rincian' => $item['rincian'] ?? null,
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

    private function resolveToAddress(int $companyId, string $toCompany, ?string $fallbackAddress): string
    {
        $resolvedAddress = Customer::where('company_id', $companyId)
            ->where('nama', $toCompany)
            ->value('alamat');

        $resolvedAddress = $resolvedAddress ?: $fallbackAddress;

        if (! $resolvedAddress) {
            throw ValidationException::withMessages([
                'to_company' => 'Alamat customer tidak ditemukan.',
            ]);
        }

        return $resolvedAddress;
    }

    private function resolveNomorForCreate(int $companyId, ?int $mitraId, ?string $tanggal): string
    {
        if ($mitraId) {
            $nomor = Mitra::where('company_id', $companyId)
                ->where('id', $mitraId)
                ->value('nomor_penawaran');

            if (! $nomor) {
                throw ValidationException::withMessages([
                    'mitra_id' => 'Nomor penawaran mitra harus diisi manual di master Mitra.',
                ]);
            }

            if (Penawaran::where('nomor', $nomor)->exists()) {
                throw ValidationException::withMessages([
                    'mitra_id' => 'Nomor penawaran untuk mitra ini sudah digunakan.',
                ]);
            }

            return $nomor;
        }

        return $this->nextPenawaranNumber($companyId, $tanggal);
    }

    private function validateCreatePayload(Request $request, int $companyId): array
    {
        return $request->validate([
            'mitra_id' => [
                'nullable',
                'integer',
                Rule::exists('mitras', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', Rule::in(['kontrak', 'satuan'])],
            'signature_role' => ['required', Rule::in(['Direktur', 'Manager', 'Sales'])],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'submitted'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', Rule::in(['month', 'pcs', 'item', 'unit'])],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function validateUpdatePayload(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'to_company' => ['required', 'string', 'max:255'],
            'to_address' => ['nullable', 'string', 'max:500'],
            'jenis_kontrak' => ['required', Rule::in(['kontrak', 'satuan'])],
            'signature_role' => ['required', Rule::in(['Direktur', 'Manager', 'Sales'])],
            'keterangan' => ['nullable', 'string'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'submitted', 'approved', 'rejected'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.rincian' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', Rule::in(['month', 'pcs', 'item', 'unit'])],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $penawarans = $this->companyPenawarans($companyId)
            ->with(['mitra:id,nama', 'items', 'purchasingOrder', 'invoices' => fn ($query) => $query->orderByDesc('tanggal')->orderByDesc('id')])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Penawaran $penawaran) => $this->serializePenawaran($this->loadPenawaranRelations($penawaran)))
            ->values();

        return Inertia::render('Penawaran/Index', [
            'penawarans' => $penawarans,
            'meta' => $this->meta($companyId),
        ]);
    }

    public function create(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        return Inertia::render('Penawaran/Create', [
            'meta' => $this->meta($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $validated = $this->validateCreatePayload($request, $companyId);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;
        $resolvedAddress = $this->resolveToAddress($companyId, $validated['to_company'], $validated['to_address'] ?? null);
        $nomor = $this->resolveNomorForCreate($companyId, $validated['mitra_id'] ?? null, $validated['tanggal']);

        $penawaran = DB::transaction(function () use ($companyId, $validated, $calculated, $taxPercent, $taxAmount, $total, $resolvedAddress, $nomor) {
            $penawaran = Penawaran::create([
                'company_id' => $companyId,
                'mitra_id' => $validated['mitra_id'] ?? null,
                'user_id' => auth()->id(),
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? 'draft',
            ]);

            $penawaran->items()->createMany($calculated['items']);

            return $penawaran;
        });

        app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

        return redirect()->route('penawaran.show', $penawaran)->with('success', 'Surat penawaran berhasil dibuat.');
    }

    public function show(Penawaran $penawaran): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $this->loadPenawaranRelations($penawaran);

        return Inertia::render('Penawaran/Show', [
            'penawaran' => $this->serializePenawaran($penawaran),
            'snapshot' => app(DocumentSnapshotService::class)->forPenawaran($penawaran),
            'meta' => $this->meta($companyId),
        ]);
    }

    public function previewPdf(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $pdf = app(PenawaranPdfService::class)->renderPreview($this->loadPenawaranRelations($penawaran));
        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="penawaran-preview.pdf"',
        ]);
    }

    public function edit(Penawaran $penawaran): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $this->loadPenawaranRelations($penawaran);

        return Inertia::render('Penawaran/Edit', [
            'penawaran' => $this->serializePenawaran($penawaran),
            'meta' => $this->meta($companyId),
        ]);
    }

    public function update(Request $request, Penawaran $penawaran): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $validated = $this->validateUpdatePayload($request);
        $calculated = $this->calculateItems($validated['items']);
        $taxPercent = (float) ($validated['tax_percent'] ?? 11);
        $taxAmount = $calculated['subtotal'] * ($taxPercent / 100);
        $total = $calculated['subtotal'] + $taxAmount;
        $resolvedAddress = $this->resolveToAddress($companyId, $validated['to_company'], $validated['to_address'] ?? null);
        $currentDate = optional($penawaran->tanggal)?->format('Y-m-d');
        $currentYearMonth = $currentDate ? substr($currentDate, 0, 7) : null;
        $nextYearMonth = \Illuminate\Support\Carbon::parse($validated['tanggal'])->format('Y-m');
        $shouldRenumber = ! $this->isNewPenawaranFormat($penawaran->nomor) || $currentYearMonth !== $nextYearMonth;
        $nomor = $shouldRenumber
            ? $this->nextPenawaranNumber($companyId, $validated['tanggal'], $penawaran->id)
            : $penawaran->nomor;

        DB::transaction(function () use ($penawaran, $validated, $calculated, $taxPercent, $taxAmount, $total, $resolvedAddress, $nomor) {
            $penawaran->update([
                'tanggal' => $validated['tanggal'],
                'customer_nama' => $validated['to_company'],
                'to_company' => $validated['to_company'],
                'to_address' => $resolvedAddress,
                'jenis_kontrak' => $validated['jenis_kontrak'],
                'signature_role' => $validated['signature_role'],
                'keterangan' => $validated['keterangan'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => $validated['status'] ?? $penawaran->status,
                'nomor' => $nomor,
            ]);

            $penawaran->items()->delete();
            $penawaran->items()->createMany($calculated['items']);
        });

        app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

        return redirect()->route('penawaran.show', $penawaran)->with('success', 'Surat penawaran berhasil diperbarui.');
    }

    public function destroy(Penawaran $penawaran): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->delete();

        return redirect()->route('penawaran.index')->with('success', 'Surat penawaran berhasil dihapus.');
    }

    public function approveForInvoice(Penawaran $penawaran): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);

        $penawaran->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

        return back()->with('success', 'Penawaran disetujui. Lanjutkan ke tahap Purchasing Order.');
    }
}
