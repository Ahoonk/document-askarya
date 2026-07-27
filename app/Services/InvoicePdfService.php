<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class InvoicePdfService
{
    private const PAYMENT_ACCOUNT = '2950701709 (BCA)';

    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function renderPreview(Invoice $invoice): string
    {
        $invoice->loadMissing(['penawaran.company', 'penawaran.mitra', 'penawaran.items', 'purchasingOrder', 'creator']);
        $snapshot = $invoice->snapshot_data ?: app(DocumentSnapshotService::class)->forInvoice($invoice);
        $snapshot['creator_name'] = $snapshot['creator_name'] ?? $invoice->creator?->name;
        $snapshot['invoice_date'] = $invoice->tanggal ?? ($snapshot['invoice_date'] ?? null);
        // Always prefer the currently active template so layout changes are reflected
        // in preview/download, while still keeping snapshot data as a fallback.
        $currentTemplatePath = $this->templateResolver->resolveTemplatePath($invoice->penawaran?->company_id, 'invoice', $invoice->penawaran?->mitra);
        $templatePath = $currentTemplatePath ?? ($snapshot['template']['path'] ?? null);

        $pdf = new Fpdi();
        $pdf->SetTitle('Invoice ' . ($invoice->nomor ?? ''));
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
                    $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

                    if ($extension === 'pdf') {
                        $pdf->setSourceFile($absolutePath);
                        $templateId = $pdf->importPage(1);
                        $pageSize = $pdf->getTemplateSize($templateId);
                        $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                        $pdf->useImportedPage($templateId, 0, 0, $pageSize['width'], $pageSize['height']);
                        $templateImported = true;
                    } elseif (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                        $pageSize = [
                            'width' => 297,
                            'height' => 210,
                        ];
                        $pdf->AddPage('P', 'A4');
                        $pdf->Image($absolutePath, 0, 0, $pageSize['width'], $pageSize['height']);
                        $templateImported = true;
                    }
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

        return $pdf->Output('S', 'invoice-preview.pdf');
    }

    private function drawContent(Fpdi $pdf, array $snapshot, array $pageSize, bool $templateImported): void
    {
        $width = (float) $pageSize['width'];
        $company = $snapshot['company'] ?? [];
        $items = $snapshot['items'] ?? [];
        $headerShiftY = $templateImported ? 8.0 : 0.0;
        $contentShiftY = $templateImported ? 28.0 : 0.0;
        $bodyShiftY = $templateImported ? $contentShiftY : 0.0;

        $companyName = trim((string) ($company['name'] ?? 'PT Aldera Saddatech Karya'));
        $customerName = trim((string) ($snapshot['customer_name'] ?? '-'));
        $customerAddress = trim((string) ($snapshot['customer_address'] ?? '-'));
        $invoiceNumber = trim((string) ($snapshot['invoice_number'] ?? '-'));
        $invoiceDate = $this->formatInputDate($snapshot['invoice_date'] ?? null);
        $poNumber = trim((string) ($snapshot['po_number'] ?? '-'));
        $poDate = $this->formatDate($snapshot['po_date'] ?? null);
        $signatureName = trim((string) ($snapshot['creator_name'] ?? $companyName));
        $signatureRole = trim((string) ($snapshot['signature_role'] ?? 'Manager'));

        $leftX = 8;
        $rightX = $width - 8;
        $topY = 12 + $headerShiftY + $contentShiftY;

        $pdf->SetTextColor(0, 0, 0);

        $this->setText($pdf, 'Bill To', $leftX, $topY, 120, 4.5, 11, 'B', 'L');
        $this->setText($pdf, $customerName, $leftX, $topY + 5, 120, 4.5, 10, 'B', 'L');
        $this->multiText($pdf, $customerAddress, $leftX, $topY + 10, 140, 4.2, 9.5, '', 'L');

        $this->rightText($pdf, 'No Invoice', $rightX - 70, $topY, 70, 4.5, 11, 'B');
        $this->rightText($pdf, $invoiceNumber, $rightX - 70, $topY + 5, 70, 4.5, 11, 'B');
        $this->rightText($pdf, 'Date: ' . $invoiceDate, $rightX - 70, $topY + 10, 70, 4.5, 10, 'B');

        $pdf->Line(8, 35 + $contentShiftY, $width - 8, 35 + $contentShiftY);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);

        $this->setText($pdf, 'Nomor PO:', $leftX, 38 + $bodyShiftY, 90, 4.5, 10, 'B', 'L');
        $this->setText($pdf, $poNumber !== '-' ? $poNumber : '', $leftX + 24, 38 + $bodyShiftY, 120, 4.5, 10, '', 'L');
        $this->setText($pdf, 'Tanggal PO: ' . $poDate, $leftX, 43 + $bodyShiftY, 180, 4.5, 10, 'B', 'L');

        $tableX = 8;
        $tableY = 52 + $bodyShiftY;
        $columns = [
            ['label' => 'No', 'width' => 8],
            ['label' => 'Description', 'width' => 95],
            ['label' => 'Qty', 'width' => 14],
            ['label' => 'Unit', 'width' => 14],
            ['label' => 'Unit Price', 'width' => 31],
            ['label' => 'Total', 'width' => 32],
        ];
        $tableWidth = array_sum(array_column($columns, 'width'));
        $rows = array_slice($items, 0, 6);
        $rowHeight = 11.5;
        $bodyHeight = $rowHeight * max(1, count($rows));
        $tableHeight = 10 + $bodyHeight;

        $this->drawTableFrame($pdf, $tableX, $tableY, $tableWidth, $tableHeight, $columns, 10, $bodyHeight);

        if (! $rows) {
            $this->drawTableRow($pdf, $tableX, $tableY + 10, $columns, ['-', '-', '-', '-', '-', '-'], $rowHeight);
        } else {
            foreach ($rows as $index => $item) {
                $this->drawTableRow(
                    $pdf,
                    $tableX,
                    $tableY + 10 + ($index * $rowHeight),
                    $columns,
                    [
                        (string) ($index + 1),
                        $this->formatItemLabel((string) ($item['nama'] ?? '-'), (string) ($item['rincian'] ?? '')),
                        (string) ($item['qty'] ?? '-'),
                        $this->formatUnit((string) ($item['satuan'] ?? '-')),
                        $this->formatMoney((float) ($item['unit_price'] ?? 0)),
                        $this->formatMoney((float) ($item['amount'] ?? 0)),
                    ],
                    $rowHeight
                );
            }
        }

        $summaryX = 127;
        $summaryY = $tableY + $tableHeight + 4;
        $summaryWidth = 77;

        $this->summaryRow($pdf, $summaryX, $summaryY, $summaryWidth, 'Subtotal', $this->formatMoney((float) ($snapshot['subtotal'] ?? 0)));
        $this->summaryRow($pdf, $summaryX, $summaryY + 6.8, $summaryWidth, 'Tax (' . $this->formatPercent((float) ($snapshot['tax_percent'] ?? 0)) . ')', $this->formatMoney((float) ($snapshot['tax_amount'] ?? 0)));
        $this->summaryRow($pdf, $summaryX, $summaryY + 13.6, $summaryWidth, 'Total', $this->formatMoney((float) ($snapshot['total'] ?? 0)), true);

        $paymentY = $summaryY;
        $pdf->SetFont('Arial', 'I', 8.5);
        $pdf->SetXY(8, $paymentY);
        $pdf->Cell(120, 4.5, $this->toPdfText('Payment To :'), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 8.2);
        $pdf->SetXY(8, $paymentY + 4.8);
        $pdf->Cell(120, 4.5, $this->toPdfText(self::PAYMENT_ACCOUNT), 0, 0, 'L');

        $pdf->SetXY(8, $paymentY + 9.6);
        $pdf->Cell(150, 4.5, $this->toPdfText('a.n ' . $companyName), 0, 0, 'L');

        $signatureX = 126;
        $signatureY = 166 + $contentShiftY;
        $signatureWidth = 76;

        $this->setText($pdf, 'Hormat Kami', $signatureX, $signatureY, $signatureWidth, 4.5, 10, '', 'C');
        $this->setText($pdf, $companyName, $signatureX, $signatureY + 5, $signatureWidth, 4.5, 10, 'B', 'C');
        $this->setText($pdf, $signatureName !== '' ? $signatureName : '-', $signatureX, $signatureY + 28, $signatureWidth, 4.5, 10, 'BU', 'C');
        $this->setText($pdf, $signatureRole !== '' ? $signatureRole : '-', $signatureX, $signatureY + 33, $signatureWidth, 4.5, 10, '', 'C');

        if (! $templateImported && ! empty($companyName)) {
            $pdf->SetTextColor(235, 235, 240);
            $pdf->SetFont('Arial', 'B', 60);
            $pdf->SetXY(92, 90 + $contentShiftY);
            $pdf->Cell(110, 25, 'ASKA', 0, 0, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }
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
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y);

        foreach ($columns as $column) {
            $pdf->Cell($column['width'], $headerHeight, $this->toPdfText($column['label']), 0, 0, 'C');
        }
    }

    private function drawTableRow(Fpdi $pdf, float $x, float $y, array $columns, array $values, float $rowHeight): void
    {
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $cursor = $x;
        foreach ($columns as $index => $column) {
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $rowHeight, '', 0, 0, 'L');

            $text = (string) ($values[$index] ?? '');
            $paddingX = $cursor + 1.2;
            $paddingY = $y + 1.0;

            if ($index === 1) {
                [$name, $detail] = array_pad(preg_split("/\r\n|\r|\n/", $text, 2), 2, '');

                $pdf->SetXY($paddingX, $paddingY);
                $pdf->SetFont('Arial', 'B', 8.8);
                $pdf->MultiCell($column['width'] - 2.4, 3.8, $this->toPdfText(trim($name)), 0, 'L');

                if (trim($detail) !== '') {
                    $pdf->SetFont('Arial', 'I', 7.6);
                    $pdf->SetTextColor(95, 95, 95);
                    $pdf->SetXY($paddingX, $paddingY + 4.1);
                    $pdf->MultiCell($column['width'] - 2.4, 3.4, $this->toPdfText(trim($detail)), 0, 'L');
                    $pdf->SetTextColor(0, 0, 0);
                }

                $pdf->SetFont('Arial', '', 9);
            } else {
                $align = in_array($index, [0, 2, 3, 4, 5], true) ? 'C' : 'L';
                $pdf->SetXY($paddingX, $paddingY + 0.6);
                $pdf->Cell($column['width'] - 2.4, 3.5, $this->toPdfText($text), 0, 0, $align);
            }

            $cursor += $column['width'];
        }
    }

    private function summaryRow(Fpdi $pdf, float $x, float $y, float $width, string $label, string $value, bool $bold = false): void
    {
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width * 0.58, 6.4, $this->toPdfText($label), 0, 0, 'L');
        $pdf->SetFont('Arial', $bold ? 'B' : '', 8.5);
        $pdf->Cell($width * 0.42, 6.4, $this->toPdfText($value), 0, 0, 'R');
        $this->drawDashedLine($pdf, $x, $y + 6.4, $x + $width);
    }

    private function drawDashedLine(Fpdi $pdf, float $x1, float $y1, float $x2, float $dashLength = 1.6, float $gapLength = 0.9): void
    {
        $cursor = $x1;

        while ($cursor < $x2) {
            $end = min($cursor + $dashLength, $x2);
            $pdf->Line($cursor, $y1, $end, $y1);
            $cursor = $end + $gapLength;
        }
    }

    private function setText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize, string $style = '', string $align = 'L'): void
    {
        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $this->toPdfText($text), 0, 0, $align);
    }

    private function rightText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize, string $style = ''): void
    {
        $this->setText($pdf, $text, $x, $y, $width, $height, $fontSize, $style, 'R');
    }

    private function multiText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $lineHeight, float $fontSize, string $style = '', string $align = 'L'): void
    {
        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($width, $lineHeight, $this->toPdfText($text), 0, $align);
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

    private function formatUnit(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '-';
        }

        return strtoupper($value);
    }

    private function formatMoney(float $value): string
    {
        return 'Rp ' . number_format($value, 2, ',', '.');
    }

    private function formatPercent(float $value): string
    {
        return number_format($value, 2, ',', '.');
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

    private function formatInputDate(mixed $value): string
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
            return '';
        }

        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $value);

        return $converted !== false ? $converted : $value;
    }
}
