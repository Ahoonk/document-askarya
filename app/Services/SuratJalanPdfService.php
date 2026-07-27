<?php

namespace App\Services;

use App\Models\SuratJalan;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class SuratJalanPdfService
{
    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function renderPreview(SuratJalan $suratJalan): string
    {
        $suratJalan->loadMissing(['invoice.penawaran.company', 'invoice.penawaran.mitra', 'invoice.penawaran.items', 'invoice.purchasingOrder']);
        $snapshot = $suratJalan->snapshot_data ?: app(DocumentSnapshotService::class)->forSuratJalan($suratJalan);
        $templatePath = $snapshot['template']['path'] ?? $this->templateResolver->resolveTemplatePath($suratJalan->invoice?->penawaran?->company_id, 'surat_jalan', $suratJalan->invoice?->penawaran?->mitra);

        $pdf = new Fpdi();
        $pdf->SetTitle('Surat Jalan ' . ($suratJalan->nomor ?? ''));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCompression(false);

        $pageSize = null;
        $templateImported = false;

        if ($templatePath) {
            $absolutePath = storage_path('app/public/' . ltrim($templatePath, '/\\'));

            if (is_file($absolutePath)) {
                try {
                    $pdf->setSourceFile($absolutePath);
                    $templateId = $pdf->importPage(1);
                    $pageSize = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                    $pdf->useImportedPage($templateId, 0, 0, $pageSize['width'], $pageSize['height']);
                    $templateImported = true;
                } catch (\Throwable) {
                    $pageSize = null;
                }
            }
        }

        if (! $pageSize) {
            $pdf->AddPage('P', 'A4');
            $pageSize = ['width' => 210, 'height' => 297];
        }

        $this->drawContent($pdf, $snapshot, $pageSize, $templateImported);

        return $pdf->Output('S', 'surat-jalan-preview.pdf');
    }

    private function drawContent(Fpdi $pdf, array $snapshot, array $pageSize, bool $templateImported): void
    {
        $width = (float) $pageSize['width'];
        $company = $snapshot['company'] ?? [];
        $items = $snapshot['items'] ?? [];
        $contentShiftY = $templateImported ? 22.0 : 0.0;

        $title = 'SURAT JALAN';
        $companyName = trim((string) ($company['name'] ?? 'PT Aldera Saddatech Karya'));
        $nomor = trim((string) ($snapshot['nomor'] ?? '-'));

        $pdf->SetTextColor(0, 0, 0);
        $this->setText($pdf, $title, 0, 14 + $contentShiftY, $width, 6, 15, 'B', 'C');
        $this->setText($pdf, 'No. ' . $nomor, 0, 21 + $contentShiftY, $width, 5, 10, '', 'C');

        $leftX = 10;
        $topY = 32 + $contentShiftY;
        $rightX = 115;

        $this->labelLine($pdf, 'Invoice', (string) ($snapshot['invoice_number'] ?? '-'), $leftX, $topY, 90);
        $this->labelLine($pdf, 'Tanggal', $this->formatDate($snapshot['invoice_date'] ?? null), $leftX, $topY + 5, 90);
        $this->labelLine($pdf, 'Customer', (string) ($snapshot['customer_name'] ?? '-'), $leftX, $topY + 10, 180);
        $this->multiLine($pdf, 'Alamat', (string) ($snapshot['customer_address'] ?? '-'), $leftX, $topY + 15, 180, 4.2, 9);

        $this->labelLine($pdf, 'Penerima', (string) ($snapshot['receiver_name'] ?? '-'), $rightX, $topY, 90);
        $this->labelLine($pdf, 'No HP', (string) ($snapshot['receiver_phone'] ?? '-'), $rightX, $topY + 5, 90);
        $this->labelLine($pdf, 'Pengirim', (string) ($snapshot['sender_name'] ?? '-'), $rightX, $topY + 10, 90);
        $this->labelLine($pdf, 'Jabatan', (string) ($snapshot['sender_title'] ?? '-'), $rightX, $topY + 15, 90);

        $tableX = 10;
        $tableY = 72 + $contentShiftY;
        $columns = [
            ['label' => 'No', 'width' => 12],
            ['label' => 'Item', 'width' => 120],
            ['label' => 'Qty', 'width' => 20],
            ['label' => 'Satuan', 'width' => 25],
        ];
        $tableWidth = array_sum(array_column($columns, 'width'));
        $rowHeight = 10.5;
        $rows = array_slice($items, 0, 10);
        $bodyHeight = $rowHeight * max(1, count($rows));

        $this->drawTableFrame($pdf, $tableX, $tableY, $tableWidth, 10 + $bodyHeight, $columns, 10, $bodyHeight);

        if (! $rows) {
            $this->drawTableRow($pdf, $tableX, $tableY + 10, $columns, ['-', '-', '-', '-'], $rowHeight);
        } else {
            foreach ($rows as $index => $item) {
                $this->drawTableRow($pdf, $tableX, $tableY + 10 + ($index * $rowHeight), $columns, [
                    (string) ($index + 1),
                    $this->formatItemLabel((string) ($item['nama'] ?? '-'), (string) ($item['rincian'] ?? '')),
                    (string) ($item['qty'] ?? '-'),
                    strtoupper((string) ($item['satuan'] ?? '-')),
                ], $rowHeight);
            }
        }

        $signatureY = 232 + $contentShiftY;
        $this->setText($pdf, $companyName, 120, $signatureY, 80, 5, 10, 'B', 'C');
        $this->setText($pdf, 'Hormat Kami', 120, $signatureY + 5, 80, 5, 10, '', 'C');
        $this->setText($pdf, (string) ($snapshot['sender_name'] ?? $companyName), 120, $signatureY + 24, 80, 5, 10, 'BU', 'C');
        $this->setText($pdf, (string) ($snapshot['sender_title'] ?? '-'), 120, $signatureY + 29, 80, 5, 10, '', 'C');
    }

    private function drawTableFrame(Fpdi $pdf, float $x, float $y, float $width, float $height, array $columns, float $headerHeight, float $bodyHeight): void
    {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
        $pdf->Rect($x, $y, $width, $height);
        $pdf->Line($x, $y + $headerHeight, $x + $width, $y + $headerHeight);

        $cursor = $x;
        foreach (array_slice($columns, 0, -1) as $column) {
            $cursor += $column['width'];
            $pdf->Line($cursor, $y, $cursor, $y + $height);
        }

        $pdf->SetFillColor(245, 245, 245);
        $pdf->Rect($x, $y, $width, $headerHeight, 'F');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($x, $y);
        foreach ($columns as $column) {
            $pdf->Cell($column['width'], $headerHeight, $this->toPdfText($column['label']), 0, 0, 'C');
        }
    }

    private function drawTableRow(Fpdi $pdf, float $x, float $y, array $columns, array $values, float $rowHeight): void
    {
        $pdf->SetFont('Arial', '', 9);
        $cursor = $x;
        foreach ($columns as $index => $column) {
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $rowHeight, '', 0, 0, 'L');
            $text = (string) ($values[$index] ?? '-');
            $pdf->SetXY($cursor + 1, $y + 0.7);
            $pdf->Cell($column['width'] - 2, 3.5, $this->toPdfText($text), 0, 0, 'C');
            $cursor += $column['width'];
        }
    }

    private function labelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width): void
    {
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY($x, $y);
        $pdf->Cell(25, 4, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, 4, ':', 0, 0, 'L');
        $pdf->Cell($width - 29, 4, $this->toPdfText($value), 0, 0, 'L');
    }

    private function multiLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, float $lineHeight, float $fontSize): void
    {
        $pdf->SetFont('Arial', '', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell(25, $lineHeight, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, $lineHeight, ':', 0, 0, 'L');
        $pdf->MultiCell($width - 29, $lineHeight, $this->toPdfText($value), 0, 'L');
    }

    private function setText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize, string $style = '', string $align = 'L'): void
    {
        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $this->toPdfText($text), 0, 0, $align);
    }

    private function formatItemLabel(string $name, string $detail): string
    {
        $name = trim($name);
        $detail = trim($detail);

        return $detail === '' ? $name : $name . "\n" . $detail;
    }

    private function formatDate(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function toPdfText(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $converted !== false ? $converted : $value;
    }
}
