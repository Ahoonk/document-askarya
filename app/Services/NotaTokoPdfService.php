<?php

namespace App\Services;

use App\Models\NotaToko;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use setasign\Fpdi\Fpdi;

class NotaTokoPdfService
{
    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function renderPreview(NotaToko $notaToko): string
    {
        $notaToko->loadMissing(['company', 'items']);
        $snapshot = $notaToko->snapshot_data ?: app(DocumentSnapshotService::class)->forNotaToko($notaToko);
        $templatePath = $snapshot['template']['path'] ?? $this->templateResolver->resolveTemplatePath($notaToko->company_id, 'nota_toko');
        $qrCodePath = $this->createSignatureQrCodePath('Bayu Suderajat, S.Kom');

        $pdf = new Fpdi();
        $pdf->SetTitle('Nota Toko ' . ($notaToko->nomor ?? ''));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCompression(false);

        $pageSize = [
            'width' => 210,
            'height' => 148.5,
        ];

        $templateImported = false;
        if ($templatePath) {
            $absolutePath = storage_path('app/public/' . ltrim($templatePath, '/\\'));

            if (is_file($absolutePath)) {
                $templateImported = $this->tryImportTemplate($pdf, $absolutePath, $pageSize);
            }
        }

        if (! $templateImported) {
            $pdf->AddPage('L', [$pageSize['width'], $pageSize['height']]);
        }

        try {
            $this->drawContent($pdf, $notaToko, $snapshot, $pageSize, $templateImported, $qrCodePath);

            return $pdf->Output('S', 'nota-toko-preview.pdf');
        } finally {
            if ($qrCodePath && is_file($qrCodePath)) {
                @unlink($qrCodePath);
            }
        }
    }

    private function drawContent(Fpdi $pdf, NotaToko $notaToko, array $snapshot, array $pageSize, bool $templateImported, ?string $qrCodePath = null): void
    {
        $width = (float) $pageSize['width'];
        $items = array_values($snapshot['items'] ?? []);
        $company = $snapshot['company'] ?? [];
        $companyName = trim((string) ($company['name'] ?? config('app.name')));
        $companyAddress = trim((string) ($company['address'] ?? ''));
        $nomor = trim((string) ($snapshot['nomor'] ?? ($notaToko->nomor ?? '-')));
        $tanggal = $this->formatDate($snapshot['tanggal'] ?? $notaToko->tanggal);
        $customerName = trim((string) ($snapshot['customer_name'] ?? $notaToko->customer_nama ?? '-'));
        $alamat = trim((string) ($snapshot['address'] ?? $notaToko->alamat ?? '-'));
        $notes = trim((string) ($snapshot['notes'] ?? $notaToko->keterangan ?? ''));
        $subtotal = (float) ($snapshot['subtotal'] ?? $notaToko->subtotal ?? 0);
        $taxPercent = (float) ($snapshot['tax_percent'] ?? $notaToko->tax_percent ?? 0);
        $taxAmount = (float) ($snapshot['tax_amount'] ?? $notaToko->tax_amount ?? 0);
        $total = (float) ($snapshot['total'] ?? $notaToko->total ?? 0);
        $contentShiftY = $templateImported ? 12.0 : 0.0;

        $pdf->SetTextColor(0, 0, 0);

        if ($companyAddress !== '') {
            $this->setText($pdf, $companyAddress, 0, 13 + $contentShiftY, $width, 4, 8.5, '', 'C');
        }

        $leftX = 8;
        $rightX = 122;
        $topY = ($companyAddress !== '' ? 30 : 27) + $contentShiftY;

        $this->labelLine($pdf, 'Customer', $customerName, $leftX, $topY, 100);
        $this->labelLine($pdf, 'Nomor', $nomor, $rightX, $topY, 80);

        $this->multiLine($pdf, 'Alamat', $alamat !== '' ? $alamat : '-', $leftX, $topY + 5, 100, 4.0, 8.8);
        $this->labelLine($pdf, 'Tanggal', $tanggal, $rightX, $topY + 5, 80);

        $tableX = 8;
        $tableY = 46 + $contentShiftY;
        $columns = [
            ['label' => 'No', 'width' => 10],
            ['label' => 'Item', 'width' => 92],
            ['label' => 'Qty', 'width' => 18],
            ['label' => 'Harga', 'width' => 35],
            ['label' => 'Jumlah', 'width' => 37],
        ];
        $tableWidth = array_sum(array_column($columns, 'width'));
        $displayRows = array_slice($items, 0, 6);
        $remainingRows = max(count($items) - count($displayRows), 0);
        $rowHeight = 8.8;

        if ($remainingRows > 0) {
            $displayRows[] = [
                'nama' => 'Dan ' . $remainingRows . ' item lagi',
                'rincian' => '',
                'qty' => '',
                'unit_price' => '',
                'amount' => '',
            ];
        }

        $bodyHeight = $rowHeight * max(1, count($displayRows));
        $this->drawTableFrame($pdf, $tableX, $tableY, $tableWidth, 10 + $bodyHeight, $columns, 10, $bodyHeight);

        if (! $displayRows) {
            $this->drawTableRow($pdf, $tableX, $tableY + 10, $columns, ['-', '-', '-', '-', '-'], $rowHeight);
        } else {
            foreach ($displayRows as $index => $item) {
                $this->drawTableRow($pdf, $tableX, $tableY + 10 + ($index * $rowHeight), $columns, [
                    (string) ($index + 1),
                    $this->formatItemLabel((string) ($item['nama'] ?? '-'), (string) ($item['rincian'] ?? '')),
                    $item['qty'] === '' ? '' : (string) ($item['qty'] ?? ''),
                    $item['unit_price'] === '' ? '' : $this->formatMoney((float) ($item['unit_price'] ?? 0)),
                    $item['amount'] === '' ? '' : $this->formatMoney((float) ($item['amount'] ?? 0)),
                ], $rowHeight);
            }
        }

        $summaryX = 122;
        $tableBottomY = $tableY + 10 + $bodyHeight;
        $terbilangY = $tableBottomY + 2.5;
        $terbilangValueY = $terbilangY + 4.0;
        $summaryY = $tableBottomY + 2.0;
        $summaryWidth = 80;

        $pdf->SetFont('Arial', 'I', 8.8);
        $pdf->SetXY(8, $terbilangY);
        $pdf->Cell(105, 4.0, $this->toPdfText('terbilang :'), 0, 0, 'L');
        $pdf->SetXY(8, $terbilangValueY);
        $pdf->MultiCell(105, 4.0, $this->toPdfText($this->amountToWords($total) . ' Rupiah'), 0, 'L');

        $this->summaryRow($pdf, $summaryX, $summaryY, $summaryWidth, 'Subtotal', $this->formatMoney($subtotal));
        $this->summaryRow($pdf, $summaryX, $summaryY + 6.2, $summaryWidth, 'Tax (' . $this->formatPercent($taxPercent) . '%)', $this->formatMoney($taxAmount));
        $this->summaryRow($pdf, $summaryX, $summaryY + 12.4, $summaryWidth, 'Total', $this->formatMoney($total), true);

        if ($notes !== '') {
            $pdf->SetFont('Arial', 'I', 8.4);
            $pdf->SetXY(8, $tableBottomY + 2.0);
            $pdf->MultiCell(100, 4.0, $this->toPdfText('Keterangan: ' . $notes), 0, 'L');
        }

        if ($qrCodePath && is_file($qrCodePath)) {
            $this->drawSignatureBlock($pdf, (float) $pageSize['width'], (float) $pageSize['height'], $qrCodePath);
        }

    }

    private function tryImportTemplate(Fpdi $pdf, string $absolutePath, ?array &$pageSize): bool
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            try {
                $pdf->setSourceFile($absolutePath);
                $templateId = $pdf->importPage(1);
                $pageSize = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                $pdf->useImportedPage($templateId, 0, 0, $pageSize['width'], $pageSize['height']);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            try {
                [$imageWidth, $imageHeight] = getimagesize($absolutePath);

                if (! $imageWidth || ! $imageHeight) {
                    return false;
                }

                $pageSize = [
                    'width' => 210,
                    'height' => 148.5,
                ];

                $pdf->AddPage('L', [$pageSize['width'], $pageSize['height']]);
                $pdf->Image($absolutePath, 0, 0, $pageSize['width'], $pageSize['height']);

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
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

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetXY($x, $y);
        foreach ($columns as $column) {
            $pdf->Cell($column['width'], $headerHeight, $this->toPdfText($column['label']), 0, 0, 'C');
        }
    }

    private function drawTableRow(Fpdi $pdf, float $x, float $y, array $columns, array $values, float $rowHeight): void
    {
        $pdf->SetFont('Arial', '', 8.5);
        $cursor = $x;

        foreach ($columns as $index => $column) {
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $rowHeight, '', 0, 0, 'L');

            $text = (string) ($values[$index] ?? '');
            $pdf->SetXY($cursor + 1, $y + 0.7);
            $align = in_array($index, [0, 2, 3, 4], true) ? 'C' : 'L';
            $pdf->Cell($column['width'] - 2, 3.5, $this->toPdfText($text), 0, 0, $align);
            $cursor += $column['width'];
        }
    }

    private function summaryRow(Fpdi $pdf, float $x, float $y, float $width, string $label, string $value, bool $bold = false): void
    {
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width * 0.58, 6.0, $this->toPdfText($label), 0, 0, 'L');
        $pdf->SetFont('Arial', $bold ? 'B' : '', 8.5);
        $pdf->Cell($width * 0.42, 6.0, $this->toPdfText($value), 0, 0, 'R');
        $this->drawDashedLine($pdf, $x, $y + 6.0, $x + $width);
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

    private function labelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width): void
    {
        $pdf->SetFont('Arial', '', 8.8);
        $pdf->SetXY($x, $y);
        $pdf->Cell(24, 4.2, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, 4.2, ':', 0, 0, 'L');
        $pdf->Cell($width - 28, 4.2, $this->toPdfText($value), 0, 0, 'L');
    }

    private function multiLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, float $lineHeight, float $fontSize): void
    {
        $pdf->SetFont('Arial', '', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell(24, $lineHeight, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, $lineHeight, ':', 0, 0, 'L');
        $pdf->MultiCell($width - 28, $lineHeight, $this->toPdfText($value), 0, 'L');
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

    private function formatMoney(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private function formatPercent(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function amountToWords(float|int|string $amount): string
    {
        $number = (int) round((float) $amount);

        if ($number === 0) {
            return 'Nol';
        }

        if ($number < 0) {
            return 'Minus ' . $this->amountToWords(abs($number));
        }

        return $this->titleCaseWords($this->spellNumber($number));
    }

    private function spellNumber(int $number): string
    {
        $dictionary = [
            0 => 'Nol',
            1 => 'Satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
            10 => 'Sepuluh',
            11 => 'Sebelas',
        ];

        if ($number < 12) {
            return $dictionary[$number];
        }

        if ($number < 20) {
            return $this->spellNumber($number - 10) . ' Belas';
        }

        if ($number < 100) {
            return $this->spellNumber(intdiv($number, 10)) . ' Puluh' . ($number % 10 ? ' ' . $this->spellNumber($number % 10) : '');
        }

        if ($number < 200) {
            return 'Seratus' . ($number % 100 ? ' ' . $this->spellNumber($number % 100) : '');
        }

        if ($number < 1000) {
            return $this->spellNumber(intdiv($number, 100)) . ' Ratus' . ($number % 100 ? ' ' . $this->spellNumber($number % 100) : '');
        }

        if ($number < 2000) {
            return 'Seribu' . ($number % 1000 ? ' ' . $this->spellNumber($number % 1000) : '');
        }

        if ($number < 1000000) {
            return $this->spellNumber(intdiv($number, 1000)) . ' Ribu' . ($number % 1000 ? ' ' . $this->spellNumber($number % 1000) : '');
        }

        if ($number < 1000000000) {
            return $this->spellNumber(intdiv($number, 1000000)) . ' Juta' . ($number % 1000000 ? ' ' . $this->spellNumber($number % 1000000) : '');
        }

        if ($number < 1000000000000) {
            return $this->spellNumber(intdiv($number, 1000000000)) . ' Miliar' . ($number % 1000000000 ? ' ' . $this->spellNumber($number % 1000000000) : '');
        }

        return (string) $number;
    }

    private function titleCaseWords(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return $value === '' ? '' : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    private function createSignatureQrCodePath(string $value): ?string
    {
        try {
            $result = (new Builder(data: $value, size: 240, margin: 0))->build();
            $path = tempnam(sys_get_temp_dir(), 'nota_toko_qr_');

            if ($path === false) {
                return null;
            }

            $pngPath = $path . '.png';
            @unlink($path);
            $result->saveToFile($pngPath);

            return $pngPath;
        } catch (\Throwable) {
            return null;
        }
    }

    private function drawSignatureBlock(Fpdi $pdf, float $pageWidth, float $pageHeight, string $qrCodePath): void
    {
        $qrSize = 22.0;
        $qrX = $pageWidth - 12.0 - $qrSize;
        $y = min(116.0, $pageHeight - 32.0);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($qrX, $y);
        $pdf->Cell($qrSize, 4.0, $this->toPdfText('Hormat Kami'), 0, 0, 'C');

        $qrY = $y + 5.0;
        $pdf->Image($qrCodePath, $qrX, $qrY, $qrSize, $qrSize);
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
