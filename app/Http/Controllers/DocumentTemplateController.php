<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTemplateController extends Controller
{
    use ResolvesCompanyId;

    private const DOCUMENT_TYPES = [
        'penawaran',
        'invoice',
        'surat_jalan',
        'berita_acara',
        'faktur_pajak',
        'nota_toko',
    ];

    private function companyTemplates(int $companyId): Builder
    {
        return DocumentTemplate::query()->where('company_id', $companyId);
    }

    private function serializeTemplate(DocumentTemplate $template): array
    {
        return [
            'id' => $template->id,
            'company_id' => $template->company_id,
            'document_type' => $template->document_type,
            'name' => $template->name,
            'file_path' => $template->file_path,
            'is_default' => (bool) $template->is_default,
            'created_at' => optional($template->created_at)->toISOString(),
            'updated_at' => optional($template->updated_at)->toISOString(),
            'storage_url' => $template->file_path && ! str_contains($template->file_path, 'resources/views/')
                ? route('document-templates.preview', [
                    'path' => $template->file_path,
                    'v' => $this->templateVersion($template->file_path),
                ])
                : null,
        ];
    }

    private function templateVersion(?string $filePath): ?int
    {
        if (! $filePath) {
            return null;
        }

        $relativePath = ltrim($filePath, '/\\');

        return Storage::disk('public')->exists($relativePath)
            ? Storage::disk('public')->lastModified($relativePath)
            : null;
    }

    private function persistFilePath(Request $request, ?string $existingPath = null): ?string
    {
        if ($request->hasFile('template_file')) {
            $file = $request->file('template_file');
            return $file->store('document-templates', 'public');
        }

        $filePath = trim((string) $request->input('file_path', ''));

        return $filePath !== '' ? $filePath : $existingPath;
    }

    private function maybeDeleteOldFile(?string $filePath): void
    {
        if (! $filePath) {
            return;
        }

        if (str_starts_with($filePath, 'document-templates/')) {
            Storage::disk('public')->delete($filePath);
        }
    }

    private function baseValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', Rule::in(self::DOCUMENT_TYPES)],
            'file_path' => ['nullable', 'string', 'max:255'],
            'template_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,svg', 'max:10240'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $query = $this->companyTemplates($companyId);

        $templates = (clone $query)
            ->orderBy('document_type')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (DocumentTemplate $template) => $this->serializeTemplate($template))
            ->values();

        return Inertia::render('DocumentTemplates/Index', [
            'templates' => $templates,
            'stats' => [
                'total' => (clone $query)->count(),
                'default' => (clone $query)->where('is_default', true)->count(),
                'document_types' => (clone $query)->distinct('document_type')->count('document_type'),
            ],
            'options' => [
                'document_types' => self::DOCUMENT_TYPES,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->getCompanyIdOrRedirect();

        return Inertia::render('DocumentTemplates/Create', [
            'options' => [
                'document_types' => self::DOCUMENT_TYPES,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $validated = $request->validate($this->baseValidationRules());

        return DB::transaction(function () use ($request, $validated, $companyId) {
            $filePath = $this->persistFilePath($request);

            $template = DocumentTemplate::create([
                'company_id' => $companyId,
                'document_type' => $validated['document_type'],
                'name' => $validated['name'],
                'file_path' => $filePath,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);

            if ($template->is_default) {
                $this->companyTemplates($companyId)
                    ->where('document_type', $template->document_type)
                    ->whereKeyNot($template->id)
                    ->update(['is_default' => false]);
            }

            return redirect()->route('document-templates.show', $template)->with('success', 'Template dokumen berhasil dibuat.');
        });
    }

    public function show(DocumentTemplate $documentTemplate): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($documentTemplate->company_id !== $companyId, 403);

        return Inertia::render('DocumentTemplates/Show', [
            'template' => $this->serializeTemplate($documentTemplate),
            'options' => [
                'document_types' => self::DOCUMENT_TYPES,
            ],
        ]);
    }

    public function edit(DocumentTemplate $documentTemplate): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($documentTemplate->company_id !== $companyId, 403);

        return Inertia::render('DocumentTemplates/Edit', [
            'template' => $this->serializeTemplate($documentTemplate),
            'options' => [
                'document_types' => self::DOCUMENT_TYPES,
            ],
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($documentTemplate->company_id !== $companyId, 403);

        $validated = $request->validate($this->baseValidationRules());
        $oldFilePath = $documentTemplate->file_path;

        return DB::transaction(function () use ($request, $validated, $documentTemplate, $companyId, $oldFilePath) {
            $filePath = $this->persistFilePath($request, $oldFilePath);

            $documentTemplate->update([
                'document_type' => $validated['document_type'],
                'name' => $validated['name'],
                'file_path' => $filePath,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]);

            if ($documentTemplate->is_default) {
                $this->companyTemplates($companyId)
                    ->where('document_type', $documentTemplate->document_type)
                    ->whereKeyNot($documentTemplate->id)
                    ->update(['is_default' => false]);
            }

            if ($request->hasFile('template_file') && $oldFilePath !== $filePath) {
                $this->maybeDeleteOldFile($oldFilePath);
            }

            return redirect()->route('document-templates.show', $documentTemplate)->with('success', 'Template dokumen berhasil diperbarui.');
        });
    }

    public function destroy(DocumentTemplate $documentTemplate): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($documentTemplate->company_id !== $companyId, 403);

        $filePath = $documentTemplate->file_path;
        $documentTemplate->delete();
        $this->maybeDeleteOldFile($filePath);

        return redirect()->route('document-templates.index')->with('success', 'Template dokumen berhasil dihapus.');
    }

    public function preview(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $path = ltrim((string) $request->query('path', ''), '/\\');

        abort_if($path === '', 404);

        $template = DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('file_path', $path)
            ->first();

        abort_unless($template || Storage::disk('public')->exists($path), 404);

        if (str_contains($path, 'resources/views/')) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
