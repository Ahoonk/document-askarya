<?php

namespace App\Services;

use App\Models\NotaToko;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Process;

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

        // Prefer the currently active template so re-uploaded layouts are reflected
        // immediately, while still falling back to the snapshot for older records.
        $currentTemplatePath = $this->templateResolver->resolveTemplatePath($notaToko->company_id, 'nota_toko');
        $templatePath = $currentTemplatePath ?? ($snapshot['template']['path'] ?? null);
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
        $nomor = trim((string) ($snapshot['nomor'] ?? ($notaToko->nomor ?? '-')));
        $tanggal = $this->formatDate($snapshot['tanggal'] ?? $notaToko->tanggal);
        $customerName = strtoupper(trim((string) ($snapshot['customer_name'] ?? $notaToko->customer_nama ?? '-')));
        $notes = trim((string) ($snapshot['notes'] ?? $notaToko->keterangan ?? ''));
        $total = (float) ($snapshot['total'] ?? $notaToko->total ?? 0);
        $contentShiftY = $templateImported ? 12.0 : 0.0;
        $amountInWords = strtoupper($this->amountToWords($total)) . ' RUPIAH';
        $paymentDescription = $this->buildPaymentDescription($notes, $items);
        $cityLine = $this->buildCityLine((string) ($company['address'] ?? ''), $this->formatDate($snapshot['payment_date'] ?? $notaToko->payment_date ?? $snapshot['tanggal'] ?? $notaToko->tanggal));
        $signatureName = 'Bayu Suderajat S.Kom';
        $signatureRole = 'MANAGER';

        $pdf->SetTextColor(0, 0, 0);

        $titleY = 22 + $contentShiftY;
        $labelX = 10;
        $colonX = 74;
        $valueX = 84;
        $valueWidth = 110;

        $this->setText($pdf, 'KUITANSI', 0, $titleY, $width, 8, 16, 'B', 'C');
        $pdf->Line(($width / 2) - 12, $titleY + 7.8, ($width / 2) + 12, $titleY + 7.8);
        $this->setText($pdf, 'No. ' . $nomor, 0, $titleY + 9.5, $width, 5, 10.5, '', 'C');

        $this->setText($pdf, 'SUDAH TERIMA DARI', $labelX, 47 + $contentShiftY, 55, 5, 10, '', 'L');
        $this->setText($pdf, ':', $colonX, 47 + $contentShiftY, 4, 5, 10, '', 'C');
        $this->setText($pdf, $customerName, $valueX, 47 + $contentShiftY, $valueWidth, 5, 10, '', 'L');

        $this->setText($pdf, 'BANYAKNYA UANG', $labelX, 55 + $contentShiftY, 55, 5, 10, '', 'L');
        $this->setText($pdf, ':', $colonX, 55 + $contentShiftY, 4, 5, 10, '', 'C');
        $this->setText($pdf, 'Rp. ' . number_format($total, 2, ',', '.'), $valueX, 55 + $contentShiftY, $valueWidth, 5, 10, '', 'L');

        $pdf->SetFont('Arial', 'I', 9.4);
        $pdf->SetXY($valueX, 61 + $contentShiftY);
        $pdf->MultiCell($valueWidth, 4.7, $this->toPdfText('(' . $amountInWords . ')'), 0, 'L');

        $this->setText($pdf, 'UNTUK PEMBAYARAN', $labelX, 68 + $contentShiftY, 55, 5, 10, '', 'L');
        $this->setText($pdf, ':', $colonX, 68 + $contentShiftY, 4, 5, 10, '', 'C');
        $pdf->SetFont('Arial', '', 9.8);
        $pdf->SetXY($valueX, 68 + $contentShiftY);
        $pdf->MultiCell($valueWidth, 5.0, $this->toPdfText($paymentDescription), 0, 'L');

        $signatureBlockX = 122;
        $signatureBlockWidth = 90;

        $this->setText($pdf, $cityLine, $signatureBlockX, 89 + $contentShiftY, $signatureBlockWidth, 5, 10, '', 'C');
        $this->setText($pdf, 'HORMAT KAMI', $signatureBlockX, 93 + $contentShiftY, $signatureBlockWidth, 5, 10, '', 'C');

        $this->setText($pdf, $signatureName !== '' ? $signatureName : '-', $signatureBlockX, 119 + $contentShiftY, $signatureBlockWidth, 5, 11, 'BU', 'C');
        $this->setText($pdf, $signatureRole !== '' ? $signatureRole : '-', $signatureBlockX, 125 + $contentShiftY, $signatureBlockWidth, 5, 10, '', 'C');

        if (! $templateImported && ! empty($companyName)) {
            $pdf->SetTextColor(235, 235, 240);
            $pdf->SetFont('Arial', 'B', 54);
            $pdf->SetXY(76, 86 + $contentShiftY);
            $pdf->Cell(60, 20, 'ASKA', 0, 0, 'C');
            $pdf->SetTextColor(0, 0, 0);
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
                return $this->tryRenderPdfTemplateAsImage($pdf, $absolutePath, $pageSize);
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

    private function tryRenderPdfTemplateAsImage(Fpdi $pdf, string $absolutePath, ?array &$pageSize): bool
    {
        $pngPath = $this->renderPdfPageToPng($absolutePath);

        if (! $pngPath || ! is_file($pngPath)) {
            return false;
        }

        try {
            $pageSize = [
                'width' => 210,
                'height' => 148.5,
            ];

            $pdf->AddPage('L', [$pageSize['width'], $pageSize['height']]);
            $pdf->Image($pngPath, 0, 0, $pageSize['width'], $pageSize['height']);

            return true;
        } finally {
            @unlink($pngPath);
        }
    }

    private function renderPdfPageToPng(string $absolutePath): ?string
    {
        $pngPath = tempnam(sys_get_temp_dir(), 'nota_toko_template_');

        if ($pngPath === false) {
            return null;
        }

        $pngPath .= '.png';

        $script = <<<'PY'
import fitz
import sys

pdf_path = sys.argv[1]
png_path = sys.argv[2]

doc = fitz.open(pdf_path)
page = doc.load_page(0)
pix = page.get_pixmap(matrix=fitz.Matrix(2, 2), alpha=False)
pix.save(png_path)
PY;

        foreach (['python3', 'python', 'py'] as $binary) {
            $process = new Process([$binary, '-c', $script, $absolutePath, $pngPath]);
            $process->setTimeout(30);
            $process->run();

            if ($process->isSuccessful() && is_file($pngPath)) {
                return $pngPath;
            }
        }

        @unlink($pngPath);

        return null;
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
        $textY = $y + 1.3;

        foreach ($columns as $index => $column) {
            $pdf->SetXY($cursor, $y);
            $pdf->Cell($column['width'], $rowHeight, '', 0, 0, 'L');

            $text = (string) ($values[$index] ?? '');
            $align = in_array($index, [0, 2, 3, 4], true) ? 'C' : 'L';

            if ($index === 1) {
                $pdf->SetXY($cursor + 1.2, $textY);
                $pdf->Cell($column['width'] - 2.4, 3.5, $this->toPdfText($text), 0, 0, 'L');
            } else {
                $pdf->SetXY($cursor, $textY);
                $pdf->Cell($column['width'], 3.5, $this->toPdfText($text), 0, 0, $align);
            }
            $cursor += $column['width'];
        }
    }

    private function summaryRow(Fpdi $pdf, float $x, float $y, float $width, string $label, string $value, bool $bold = false): void
    {
        $pdf->SetFont('Arial', '', 8.2);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width * 0.58, 4.6, $this->toPdfText($label), 0, 0, 'L');
        $pdf->SetFont('Arial', $bold ? 'B' : '', 8.2);
        $pdf->Cell($width * 0.42, 4.6, $this->toPdfText($value), 0, 0, 'R');
        $this->drawDashedLine($pdf, $x, $y + 4.2, $x + $width);
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

    private function buildPaymentDescription(string $notes, array $items): string
    {
        $notes = trim($notes);

        if ($notes !== '') {
            return strtoupper($notes);
        }

        $itemNames = array_values(array_filter(array_map(static function (array $item): string {
            return trim((string) ($item['nama'] ?? ''));
        }, $items)));

        if ($itemNames === []) {
            return '-';
        }

        $summary = implode(', ', array_slice($itemNames, 0, 3));

        if (count($itemNames) > 3) {
            $summary .= ' DAN ' . (count($itemNames) - 3) . ' ITEM LAINNYA';
        }

        return strtoupper($summary);
    }

    private function buildCityLine(string $companyAddress, string $dateText): string
    {
        $city = 'CILEGON';

        if ($companyAddress !== '') {
            if (preg_match('/Kota\s+([A-Za-z]+)/i', $companyAddress, $match)) {
                $city = strtoupper($match[1]);
            } elseif (preg_match('/Kabupaten\s+([A-Za-z]+)/i', $companyAddress, $match)) {
                $city = strtoupper($match[1]);
            }
        }

        return strtoupper($city) . ', ' . strtoupper($dateText);
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
