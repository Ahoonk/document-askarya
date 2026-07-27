<?php

namespace App\Services;

use App\Models\BeritaAcara;
use App\Models\Company;
use App\Models\DocumentSeries;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function next(Company|int $company, string $documentType, ?string $referenceDate = null): string
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return DB::transaction(function () use ($companyId, $documentType, $referenceDate) {
            $series = $this->ensureSeries($companyId, $documentType);
            $series = DocumentSeries::query()
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->lockForUpdate()
                ->firstOrFail();

            $series->counter = max((int) $series->counter, $this->seedCounter($companyId, $documentType));
            $series->counter++;
            $series->save();

            return $this->formatNumber($series, $referenceDate);
        });
    }

    public function nextAlderaInvoice(Company|int $company, ?string $referenceDate = null, ?int $ignoreInvoiceId = null): string
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        $date = $referenceDate ? Carbon::parse($referenceDate) : now();

        return DB::transaction(function () use ($companyId, $date, $ignoreInvoiceId) {
            $query = Invoice::query()
                ->whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId)->whereNull('mitra_id'))
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month);

            if ($ignoreInvoiceId !== null) {
                $query->whereKeyNot($ignoreInvoiceId);
            }

            $counter = $this->maxCounterFromCollection($query->pluck('nomor')->all()) + 1;

            return sprintf(
                'INV/%04d/%s/%03d-ASK',
                $date->year,
                $this->monthCode($date->month),
                $counter
            );
        });
    }

    public function alderaNumberFromInvoice(string $invoiceNumber, string $prefix): ?string
    {
        if (! in_array($prefix, ['SJ', 'BA'], true)) {
            return null;
        }

        if (preg_match('/^INV\/(\d{4})\/(\d{2})\/(\d{3})-ASK$/', $invoiceNumber, $match)) {
            return sprintf('%s/%s/%s/%s-ASK', $prefix, $match[1], $match[2], $match[3]);
        }

        if (! preg_match('/^INV\/(\d{4})\/([IVXLCDM]+)\/(\d{3})-ASK$/', $invoiceNumber, $match)) {
            return null;
        }

        $month = $this->romanMonthToNumber($match[2]);

        if ($month === null) {
            return null;
        }

        return sprintf('%s/%s/%02d/%s-ASK', $prefix, $match[1], $month, $match[3]);
    }

    private function ensureSeries(int $companyId, string $documentType): DocumentSeries
    {
        $defaults = $this->defaultsFor($documentType);

        $series = DocumentSeries::firstOrCreate(
            [
                'company_id' => $companyId,
                'document_type' => $documentType,
            ],
            $defaults
        );

        foreach ($defaults as $key => $value) {
            if ($series->{$key} === null || $series->{$key} === '') {
                $series->{$key} = $value;
            }
        }

        if ($series->isDirty()) {
            $series->save();
        }

        return $series;
    }

    private function defaultsFor(string $documentType): array
    {
        return [
            'prefix' => $this->defaultPrefix($documentType),
            'year_mode' => true,
            'month_mode' => true,
            'counter' => 0,
            'padding' => 3,
            'suffix' => $this->defaultSuffix($documentType),
        ];
    }

    private function defaultPrefix(string $documentType): string
    {
        return match ($documentType) {
            'penawaran' => 'PNW',
            'invoice' => 'INV',
            'surat_jalan' => 'SJ',
            'berita_acara' => 'BA',
            'purchasing_order' => 'PO',
            'nota_toko' => 'NT',
            default => strtoupper(str_replace('_', '-', $documentType)),
        };
    }

    private function defaultSuffix(string $documentType): ?string
    {
        return match ($documentType) {
            'invoice' => 'ASK',
            default => null,
        };
    }

    private function seedCounter(int $companyId, string $documentType): int
    {
        return match ($documentType) {
            'invoice' => $this->maxCounterFromCollection(
                Invoice::whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId))->pluck('nomor')->all()
            ),
            'surat_jalan' => $this->maxCounterFromCollection(
                SuratJalan::where('company_id', $companyId)->pluck('nomor')->all()
            ),
            'berita_acara' => $this->maxCounterFromCollection(
                BeritaAcara::where('company_id', $companyId)->pluck('nomor')->all()
            ),
            default => 0,
        };
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

    private function romanMonthToNumber(string $value): ?int
    {
        return match (strtoupper(trim($value))) {
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
            default => null,
        };
    }

    private function maxCounterFromCollection(array $numbers): int
    {
        $max = 0;

        foreach ($numbers as $number) {
            $counter = $this->extractCounter($number);

            if ($counter !== null) {
                $max = max($max, $counter);
            }
        }

        return $max;
    }

    private function extractCounter(?string $number): ?int
    {
        if (empty($number)) {
            return null;
        }

        if (preg_match('/\/(\d{3})(?:-[A-Z]+)?$/', $number, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function formatNumber(DocumentSeries $series, ?string $referenceDate = null): string
    {
        return $this->formatParts(
            prefix: $series->prefix ?: $this->defaultPrefix($series->document_type),
            referenceDate: $referenceDate,
            counter: (int) $series->counter,
            padding: (int) ($series->padding ?: 3),
            suffix: $series->suffix ?: $this->defaultSuffix($series->document_type)
        );
    }

    private function formatParts(string $prefix, ?string $referenceDate, int $counter, int $padding, ?string $suffix): string
    {
        $date = $referenceDate ? Carbon::parse($referenceDate) : now();
        $base = implode('/', [$prefix, $date->format('Y'), $date->format('m'), str_pad((string) $counter, $padding, '0', STR_PAD_LEFT)]);

        return ! empty($suffix) ? $base . '-' . $suffix : $base;
    }
}
