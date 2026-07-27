<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Mitra;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateResolver
{
    public function resolveTemplatePath(?int $companyId, string $documentType, ?Mitra $mitra = null): ?string
    {
        if ($mitra) {
            $mitraPath = $this->resolveMitraTemplatePath($mitra, $documentType);

            if ($mitraPath) {
                return $mitraPath;
            }
        }

        if (! $companyId) {
            return null;
        }

        $templates = DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($templates as $template) {
            if (empty($template->file_path)) {
                continue;
            }

            $relativePath = ltrim($template->file_path, '/\\');

            if (Storage::disk('public')->exists($relativePath)) {
                return $relativePath;
            }
        }

        return null;
    }

    public function resolveView(?int $companyId, string $documentType, string $defaultView, ?Mitra $mitra = null): string
    {
        if ($mitra) {
            $mitraView = $this->resolveMitraView($mitra, $documentType);

            if ($mitraView) {
                return $mitraView;
            }
        }

        if (! $companyId) {
            return $defaultView;
        }

        $template = DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->where('is_default', true)
            ->first();

        if (! $template || empty($template->file_path)) {
            return $defaultView;
        }

        $candidate = $this->normalizeViewName($template->file_path);

        return View::exists($candidate) ? $candidate : $defaultView;
    }

    private function resolveMitraTemplatePath(Mitra $mitra, string $documentType): ?string
    {
        $pathField = match ($documentType) {
            'penawaran' => $mitra->template_penawaran_path,
            'invoice' => $mitra->template_invoice_path,
            'surat_jalan' => $mitra->template_surat_jalan_path,
            'berita_acara' => $mitra->template_berita_acara_path,
            default => null,
        };

        if (! $pathField) {
            return null;
        }

        $relativePath = ltrim($pathField, '/\\');

        return Storage::disk('public')->exists($relativePath) ? $relativePath : null;
    }

    private function resolveMitraView(Mitra $mitra, string $documentType): ?string
    {
        $pathField = match ($documentType) {
            'penawaran' => $mitra->template_penawaran_path,
            'invoice' => $mitra->template_invoice_path,
            'surat_jalan' => $mitra->template_surat_jalan_path,
            'berita_acara' => $mitra->template_berita_acara_path,
            default => null,
        };

        if (! $pathField) {
            return null;
        }

        $candidate = $this->normalizeViewName($pathField);

        return View::exists($candidate) ? $candidate : null;
    }

    private function normalizeViewName(string $value): string
    {
        $value = trim(str_replace(['\\', '/'], '.', $value));
        $value = preg_replace('/\.blade(\.php)?$/', '', $value) ?? $value;
        $value = preg_replace('/^resources\.views\./', '', $value) ?? $value;
        $value = preg_replace('/^views\./', '', $value) ?? $value;

        return $value;
    }

}
