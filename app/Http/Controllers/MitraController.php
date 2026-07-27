<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Mitra;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MitraController extends Controller
{
    use ResolvesCompanyId;

    private function companyMitras(int $companyId): Builder
    {
        return Mitra::query()->where('company_id', $companyId);
    }

    private function serializeMitra(Mitra $mitra): array
    {
        return [
            'id' => $mitra->id,
            'company_id' => $mitra->company_id,
            'nama' => $mitra->nama,
            'email' => $mitra->email,
            'alamat' => $mitra->alamat,
            'nomor_penawaran' => $mitra->nomor_penawaran,
            'nomor_invoice' => $mitra->nomor_invoice,
            'nomor_surat_jalan' => $mitra->nomor_surat_jalan,
            'nomor_berita_acara' => $mitra->nomor_berita_acara,
            'template_penawaran_path' => $mitra->template_penawaran_path,
            'template_invoice_path' => $mitra->template_invoice_path,
            'template_surat_jalan_path' => $mitra->template_surat_jalan_path,
            'template_berita_acara_path' => $mitra->template_berita_acara_path,
            'created_at' => optional($mitra->created_at)->toISOString(),
            'updated_at' => optional($mitra->updated_at)->toISOString(),
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $query = $this->companyMitras($companyId);

        $mitras = (clone $query)
            ->orderBy('nama')
            ->get()
            ->map(fn (Mitra $mitra) => $this->serializeMitra($mitra))
            ->values();

        return Inertia::render('Mitra/Index', [
            'mitras' => $mitras,
            'stats' => [
                'total' => (clone $query)->count(),
                'dengan_nomor_penawaran' => (clone $query)->whereNotNull('nomor_penawaran')->where('nomor_penawaran', '!=', '')->count(),
                'dengan_template' => (clone $query)->where(function ($subQuery) {
                    $subQuery->whereNotNull('template_penawaran_path')
                        ->orWhereNotNull('template_invoice_path')
                        ->orWhereNotNull('template_surat_jalan_path')
                        ->orWhereNotNull('template_berita_acara_path');
                })->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->getCompanyIdOrRedirect();

        return Inertia::render('Mitra/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nomor_penawaran' => ['nullable', 'string', 'max:255'],
            'nomor_invoice' => ['nullable', 'string', 'max:255'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:255'],
            'nomor_berita_acara' => ['nullable', 'string', 'max:255'],
            'template_penawaran_path' => ['nullable', 'string', 'max:255'],
            'template_invoice_path' => ['nullable', 'string', 'max:255'],
            'template_surat_jalan_path' => ['nullable', 'string', 'max:255'],
            'template_berita_acara_path' => ['nullable', 'string', 'max:255'],
        ]);

        $mitra = Mitra::create([
            'company_id' => $companyId,
            ...$validated,
        ]);

        return redirect()->route('mitra.show', $mitra)->with('success', 'Mitra berhasil dibuat.');
    }

    public function show(Mitra $mitra): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($mitra->company_id !== $companyId, 403);

        return Inertia::render('Mitra/Show', [
            'mitra' => $this->serializeMitra($mitra),
        ]);
    }

    public function edit(Mitra $mitra): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($mitra->company_id !== $companyId, 403);

        return Inertia::render('Mitra/Edit', [
            'mitra' => $this->serializeMitra($mitra),
        ]);
    }

    public function update(Request $request, Mitra $mitra): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($mitra->company_id !== $companyId, 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nomor_penawaran' => ['nullable', 'string', 'max:255'],
            'nomor_invoice' => ['nullable', 'string', 'max:255'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:255'],
            'nomor_berita_acara' => ['nullable', 'string', 'max:255'],
            'template_penawaran_path' => ['nullable', 'string', 'max:255'],
            'template_invoice_path' => ['nullable', 'string', 'max:255'],
            'template_surat_jalan_path' => ['nullable', 'string', 'max:255'],
            'template_berita_acara_path' => ['nullable', 'string', 'max:255'],
        ]);

        $mitra->update($validated);

        return redirect()->route('mitra.show', $mitra)->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($mitra->company_id !== $companyId, 403);

        $mitra->delete();

        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil dihapus.');
    }
}
