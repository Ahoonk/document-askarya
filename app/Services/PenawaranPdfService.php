<?php

namespace App\Services;

use App\Models\Penawaran;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class PenawaranPdfService
{
    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function renderPreview(Penawaran $penawaran): string
    {
        $penawaran->loadMissing(['company', 'mitra', 'items', 'user']);
        $snapshot = $penawaran->snapshot_data ?: app(DocumentSnapshotService::class)->forPenawaran($penawaran);
        $templatePath = $snapshot['template']['path'] ?? $this->templateResolver->resolveTemplatePath($penawaran->company_id, 'penawaran', $penawaran->mitra);

        $pdf = new Fpdi();
        $pdf->SetTitle('Penawaran ' . ($penawaran->nomor ?? ''));
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
                } catch (\Throwable $e) {
                    $pageSize = null;
                }
            }
        }

        if (! $pageSize) {
            $pdf->AddPage('P', 'A4');
            $pageSize = [
                'width' => 210,
                'height' => 297,
            ];
        }

        $this->drawContent($pdf, $snapshot, $pageSize, $templateImported);

        return $pdf->Output('S', 'penawaran-preview.pdf');
    }

    private function drawContent(Fpdi $pdf, array $snapshot, array $pageSize, bool $templateImported): void
    {
        $width = (float) $pageSize['width'];
        $height = (float) $pageSize['height'];
        $contentShiftY = $templateImported ? 24.0 : 0.0;

        $company = $snapshot['company'] ?? [];
        $items = $snapshot['items'] ?? [];

        $marginLeft = 9.5;
        $marginRight = 9.5;
        $contentWidth = $width - $marginLeft - $marginRight;

        $pdf->SetTextColor(0, 0, 0);

        $this->centerText($pdf, 'SURAT PENAWARAN', 14.5 + $contentShiftY, 5, 12.5, $contentWidth, 'BU', 4.0);
        $this->centerText($pdf, 'No. ' . ($snapshot['nomor'] ?? '-'), 21 + $contentShiftY, 4, 9.5, $contentWidth, '', 4.0);

        $this->labelLine($pdf, 'To', (string) ($snapshot['customer_name'] ?? '-'), 8, 33 + $contentShiftY, 100);
        $this->labelLine($pdf, 'At', (string) ($snapshot['customer_address'] ?? '-'), 8, 37.5 + $contentShiftY, 100);
        $this->labelLine($pdf, 'Tanggal', $this->formatDate($snapshot['tanggal'] ?? null), 146, 37.5 + $contentShiftY, 50, true);

        $tableX = 5.5;
        $tableY = 46 + $contentShiftY;
        $tableWidth = 198.5;
        $headerHeight = 6.5;
        $bodyHeight = 78;
        $tableHeight = $headerHeight + $bodyHeight;

        $columns = [
            ['label' => 'No', 'width' => 9],
            ['label' => 'Item', 'width' => 84],
            ['label' => 'Qty', 'width' => 13],
            ['label' => 'Satuan', 'width' => 21],
            ['label' => 'Unit Price', 'width' => 35],
            ['label' => 'Amount', 'width' => 36.5],
        ];

        $this->drawTableFrame($pdf, $tableX, $tableY, $tableWidth, $tableHeight, $columns, $headerHeight, $bodyHeight, $templateImported);

        $rowY = $tableY + $headerHeight;
        $rowHeight = 18;
        $maxRows = max(1, (int) floor($bodyHeight / $rowHeight));
        $rows = array_slice($items, 0, $maxRows);

        if (! $rows) {
            $this->drawTableRow($pdf, $tableX, $rowY, $columns, ['-', '-', '-', '-', '-'], $rowHeight, true);
        } else {
            foreach ($rows as $index => $item) {
                $this->drawTableRow(
                    $pdf,
                    $tableX,
                    $rowY + ($index * $rowHeight),
                    $columns,
                    [
                        (string) ($index + 1),
                        $this->formatItemLabel((string) ($item['nama'] ?? '-'), (string) ($item['rincian'] ?? '')),
                        (string) ($item['qty'] ?? '-'),
                        $this->formatSatuan((string) ($item['satuan'] ?? '-')),
                        $this->formatCurrency((float) ($item['unit_price'] ?? 0)),
                        $this->formatCurrency((float) ($item['amount'] ?? 0)),
                    ],
                    $rowHeight,
                    $index === count($rows) - 1
                );
            }
        }

        $summaryX = 127;
        $summaryY = 134.5 + $contentShiftY;
        $summaryWidth = 77;

        $this->summaryRow($pdf, $summaryX, $summaryY, $summaryWidth, 'Subtotal', $this->formatCurrency((float) ($snapshot['subtotal'] ?? 0)));
        $this->summaryRow($pdf, $summaryX, $summaryY + 6.8, $summaryWidth, 'Pajak ' . ((int) round((float) ($snapshot['tax_percent'] ?? 0))) . '%', $this->formatCurrency((float) ($snapshot['tax_amount'] ?? 0)));
        $this->summaryRow($pdf, $summaryX, $summaryY + 13.6, $summaryWidth, 'Total', $this->formatCurrency((float) ($snapshot['total'] ?? 0)), true);

        $keterangan = trim((string) ($snapshot['keterangan'] ?? ''));
        if ($keterangan !== '') {
            $notesX = 8;
            $notesY = 134.5 + $contentShiftY;
            $notesWidth = 104;

            $pdf->SetFont('Arial', 'I', 8.5);
            $pdf->SetXY($notesX, $notesY);
            $pdf->Cell($notesWidth, 4.5, $this->toPdfText('Keterangan'), 0, 0, 'L');

            $pdf->SetFont('Arial', 'I', 8.2);
            $pdf->SetXY($notesX, $notesY + 4.8);
            $pdf->MultiCell($notesWidth, 4.2, $this->toPdfText($keterangan), 0, 'L');
        }

        $signatureY = 191 + $contentShiftY;
        $signatureX = 126;
        $signatureWidth = 76;
        $signatureName = trim((string) ($snapshot['creator_name'] ?? ($company['name'] ?? '')));
        $signatureRole = trim((string) ($snapshot['signature_role'] ?? ''));

        $this->rightAlignedText($pdf, 'Hormat Kami', $signatureX, $signatureY, $signatureWidth, 4, 10);
        $this->rightAlignedText($pdf, (string) ($company['name'] ?? '-'), $signatureX, $signatureY + 6, $signatureWidth, 4, 10, 'B');
        $this->rightAlignedText($pdf, $signatureName !== '' ? $signatureName : '-', $signatureX, $signatureY + 31, $signatureWidth, 4, 10, 'BU');
        $this->rightAlignedText($pdf, $signatureRole !== '' ? $signatureRole : '-', $signatureX, $signatureY + 37, $signatureWidth, 4, 10, 'B');

        // Keep the page visually balanced when the imported template is not available.
        if (! $templateImported && ! empty($company['name'])) {
            $pdf->SetTextColor(235, 235, 240);
            $pdf->SetFont('Arial', 'B', 60);
            $pdf->SetXY(66, 89 + $contentShiftY);
            $pdf->Cell(80, 25, 'ASKA', 0, 0, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    private function drawTableFrame(
        Fpdi $pdf,
        float $x,
        float $y,
        float $width,
        float $height,
        array $columns,
        float $headerHeight,
        float $bodyHeight,
        bool $templateImported
    ): void {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Rect($x, $y, $width, $headerHeight, 'F');
        if (! $templateImported) {
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($x, $y + $headerHeight, $width, $bodyHeight, 'F');
        }
        $pdf->Rect($x, $y, $width, $height);
        $pdf->Line($x, $y + $headerHeight, $x + $width, $y + $headerHeight);

        $cursor = $x;
        foreach (array_slice($columns, 0, -1) as $column) {
            $cursor += $column['width'];
            $pdf->Line($cursor, $y, $cursor, $y + $height);
        }

        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->SetTextColor(35, 35, 35);
        $pdf->SetXY($x, $y);

        foreach ($columns as $column) {
            $pdf->Cell($column['width'], $headerHeight, $this->toPdfText($column['label']), 0, 0, 'C');
        }

        $pdf->SetTextColor(0, 0, 0);
    }

    private function drawTableRow(
        Fpdi $pdf,
        float $x,
        float $y,
        array $columns,
        array $values,
        float $rowHeight,
        bool $drawBottomLine
    ): void {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        $cursor = $x;
        foreach ($columns as $index => $column) {
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $rowHeight, '', 0, 0, 'L');

            $text = (string) ($values[$index] ?? '-');
            $paddingX = $cursor + 1.2;
            $paddingY = $y + 1.2;

            if ($index === 1) {
                [$name, $detail] = array_pad(preg_split("/\r\n|\r|\n/", $text, 2), 2, '');

                $pdf->SetXY($paddingX, $paddingY);
                $pdf->SetFont('Arial', 'B', 8.2);
                $pdf->MultiCell($column['width'] - 2.4, 4.0, $this->toPdfText('• ' . trim($name)), 0, 'L');

                if (trim($detail) !== '') {
                    $pdf->SetFont('Arial', 'I', 7.4);
                    $pdf->SetTextColor(90, 90, 90);
                    $pdf->SetXY($paddingX + 3.0, $paddingY + 4.1);
                    $pdf->MultiCell($column['width'] - 5.4, 3.5, $this->toPdfText(trim($detail)), 0, 'L');
                    $pdf->SetTextColor(0, 0, 0);
                }

                $pdf->SetFont('Arial', '', 8);
            } else {
                $pdf->SetXY($paddingX, $paddingY + 0.5);
                $pdf->Cell($column['width'] - 2.4, 3.5, $this->toPdfText($text), 0, 0, $index === 2 ? 'C' : ($index >= 4 ? 'C' : 'C'));
            }

            $cursor += $column['width'];
        }

        if ($drawBottomLine) {
            return;
        }
    }
    private function summaryRow(Fpdi $pdf, float $x, float $y, float $width, string $label, string $value, bool $bold = false): void
    {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width * 0.58, 6.5, $this->toPdfText($label), 0, 0, 'L');
        $pdf->SetFont('Arial', $bold ? 'B' : '', 8.5);
        $pdf->Cell($width * 0.42, 6.5, $this->toPdfText($value), 0, 0, 'R');
        $this->drawDashedLine($pdf, $x, $y + 6.4, $x + $width);
    }

    private function drawDashedLine(Fpdi $pdf, float $x1, float $y1, float $x2, float $dashLength = 1.6, float $gapLength = 0.9): void
    {
        $distance = max(0.0, $x2 - $x1);
        $cursor = $x1;

        while ($cursor < $x2) {
            $end = min($cursor + $dashLength, $x2);
            $pdf->Line($cursor, $y1, $end, $y1);
            $cursor = $end + $gapLength;
        }
    }

    private function labelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, bool $rightAligned = false): void
    {
        $pdf->SetFont('Arial', '', 8.8);
        $pdf->SetXY($x, $y);
        $pdf->Cell(12, 4, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, 4, ':', 0, 0, 'L');

        if ($rightAligned) {
            $pdf->Cell($width - 16, 4, $this->toPdfText($value), 0, 0, 'R');
            return;
        }

        $pdf->MultiCell($width - 16, 4, $this->toPdfText($value), 0, 'L');
    }

    private function centerText(Fpdi $pdf, string $text, float $y, float $lineHeight, float $fontSize, float $width, string $style = '', float $x = 0.0): void
    {
        if ($style === 'R') {
            $style = '';
        }

        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $lineHeight, $this->toPdfText($text), 0, 0, 'C');
    }

    private function rightAlignedText(
        Fpdi $pdf,
        string $text,
        float $x,
        float $y,
        float $width,
        float $lineHeight,
        float $fontSize,
        string $style = ''
    ): void {
        if ($style === 'R') {
            $style = '';
        }

        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $lineHeight, $this->toPdfText($text), 0, 0, 'C');
    }

    private function formatItemLabel(string $name, string $detail): string
    {
        $name = trim($name);
        $detail = trim($detail);

        if ($detail === '') {
            return $name;
        }

        return $name . "\n" . $detail;
    }

    private function formatSatuan(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        return ucfirst(strtolower($value));
    }

    private function formatCurrency(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private function formatDate(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable $e) {
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
