<?php

namespace App\Services;

use App\Models\BeritaAcara;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class BeritaAcaraPdfService
{
    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function renderPreview(BeritaAcara $beritaAcara): string
    {
        $beritaAcara->loadMissing(['invoice.penawaran.company', 'invoice.penawaran.mitra', 'invoice.penawaran.items', 'invoice.purchasingOrder']);
        $snapshot = $beritaAcara->snapshot_data ?: app(DocumentSnapshotService::class)->forBeritaAcara($beritaAcara);
        $templatePath = $this->templateResolver->resolveTemplatePath(
            $beritaAcara->invoice?->penawaran?->company_id,
            'berita_acara',
            $beritaAcara->invoice?->penawaran?->mitra
        );

        $pdf = new Fpdi();
        $pdf->SetTitle('Berita Acara ' . ($beritaAcara->nomor ?? ''));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCompression(false);

        $pageSize = null;
        $templateImported = false;

        if ($templatePath) {
            $absolutePath = storage_path('app/public/' . ltrim($templatePath, '/\\'));

            if (is_file($absolutePath)) {
                $templateImported = $this->tryImportTemplate($pdf, $absolutePath, $pageSize);
            }
        }

        if (! $pageSize) {
            $pdf->AddPage('P', 'A4');
            $pageSize = ['width' => 210, 'height' => 297];
        }

        $this->drawContent($pdf, $beritaAcara, $snapshot, $pageSize, $templateImported);

        return $pdf->Output('S', 'berita-acara-preview.pdf');
    }

    private function drawContent(Fpdi $pdf, BeritaAcara $beritaAcara, array $snapshot, array $pageSize, bool $templateImported): void
    {
        $width = (float) $pageSize['width'];
        $company = $snapshot['company'] ?? [];
        $contentShiftY = $templateImported ? 22.0 : 0.0;

        $companyName = trim((string) ($company['name'] ?? 'PT Aldera Saddatech Karya'));
        $nomor = trim((string) ($snapshot['nomor'] ?? ($beritaAcara->nomor ?? '-')));
        $subject = trim((string) ($snapshot['subject'] ?? 'Penggunaan Internet'));
        $openingDate = trim((string) ($snapshot['tanggal_teks_manual'] ?? ''));
        if ($openingDate === '') {
            $openingDate = $this->formatDate($snapshot['tanggal'] ?? null, 'l, d F Y');
        }

        $pihakPertamaNama = trim((string) ($snapshot['pihak_pertama_nama'] ?? ($snapshot['customer_name'] ?? '-')));
        $pihakPertamaAlamat = trim((string) ($snapshot['pihak_pertama_alamat'] ?? ($snapshot['customer_address'] ?? '-')));
        $pihakKeduaNama = trim((string) ($snapshot['pihak_kedua_nama'] ?? $companyName));
        $pihakKeduaAlamat = trim((string) ($snapshot['pihak_kedua_alamat'] ?? ($company['address'] ?? '-')));
        $nomorPerjanjian = trim((string) ($snapshot['nomor_perjanjian'] ?? ($snapshot['po_number'] ?? '')));
        $pekerjaan = trim((string) ($snapshot['pekerjaan_manual'] ?? ''));
        $periode = trim((string) ($snapshot['periode_manual'] ?? ''));
        $predikat = trim((string) ($snapshot['predikat_manual'] ?? ''));
        $closingNote = trim((string) ($snapshot['closing_note'] ?? 'Demikian Berita Acara ini dibuat dan dapat digunakan sebagai mana mestinya.'));
        $signatureRole = trim((string) ($snapshot['signature_role'] ?? 'Direktur'));

        $pdf->SetTextColor(0, 0, 0);

        $this->setText($pdf, 'Berita Acara', 0, 11.5 + $contentShiftY, $width, 7, 15, 'B', 'C');

        $separatorWidth = 38.0;
        $separatorX = ($width - $separatorWidth) / 2;
        $separatorY = 18.5 + $contentShiftY;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.35);
        $pdf->Line($separatorX, $separatorY, $separatorX + $separatorWidth, $separatorY);

        $this->setText($pdf, 'No. ' . $nomor, 0, 20.5 + $contentShiftY, $width, 5, 10, '', 'C');
        $this->centeredLabelLine($pdf, 'Perihal', $subject, 0, 24.8 + $contentShiftY, $width);

        $left = 15;
        $textWidth = $width - ($left * 2);
        $cursorY = 38.2 + $contentShiftY;

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($left, $cursorY);
        $pdf->MultiCell($textWidth, 5.1, $this->toPdfText('Pada hari ini, ' . $openingDate . ', yang bertanda tangan dibawah ini'), 0, 'L');
        $cursorY = $pdf->GetY() + 2;

        $this->romanPartyBlock($pdf, 'I.', 'Nama', $pihakPertamaNama, 'Alamat', $pihakPertamaAlamat, $left, $cursorY, $textWidth);
        $cursorY = $pdf->GetY() + 2;
        $this->setText($pdf, 'Yang selanjutnya disebut PIHAK PERTAMA', $left, $cursorY, $textWidth, 5, 10, 'B', 'L');
        $cursorY += 9;

        $this->romanPartyBlock($pdf, 'II.', 'Nama', $pihakKeduaNama, 'Alamat', $pihakKeduaAlamat, $left, $cursorY, $textWidth);
        $cursorY = $pdf->GetY() + 2;
        $this->setText($pdf, 'Yang selanjutnya disebut PIHAK KEDUA', $left, $cursorY, $textWidth, 5, 10, 'B', 'L');
        $cursorY += 10;

        $paragraphLines = [
            'Berdasarkan Surat Perjanjian Kerjasama Nomor : ' . $nomorPerjanjian . ', PIHAK KEDUA telah',
            'melaksanakan pekerjaan untuk PIHAK PERTAMA ' . $this->composeFlexibleWorkSentence($pekerjaan, $periode, $predikat),
        ];

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($left, $cursorY);
        $pdf->MultiCell($textWidth, 5.1, $this->toPdfText(implode("\n", $paragraphLines)), 0, 'L');
        $cursorY = $pdf->GetY() + 4;

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($left, $cursorY);
        $pdf->MultiCell($textWidth, 5.1, $this->toPdfText($closingNote), 0, 'L');

        $signatureTop = 188 + $contentShiftY;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(34, $signatureTop);
        $pdf->Cell(70, 5, $this->toPdfText('PIHAK PERTAMA'), 0, 0, 'C');
        $pdf->SetXY(124, $signatureTop);
        $pdf->Cell(70, 5, $this->toPdfText('PIHAK KEDUA'), 0, 0, 'C');

        $pdf->SetFont('Arial', 'BU', 10);
        $pdf->SetXY(124, $signatureTop + 38);
        $pdf->Cell(70, 5, $this->toPdfText($pihakKeduaNama !== '' ? $pihakKeduaNama : '-'), 0, 0, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(124, $signatureTop + 43);
        $pdf->Cell(70, 5, $this->toPdfText($signatureRole !== '' ? $signatureRole : '-'), 0, 0, 'C');

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
                $pageSize = null;

                return false;
            }
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            try {
                [$imageWidth, $imageHeight] = getimagesize($absolutePath);

                if (! $imageWidth || ! $imageHeight) {
                    return false;
                }

                $orientation = $imageWidth >= $imageHeight ? 'L' : 'P';
                $pageSize = [
                    'width' => $orientation === 'L' ? 297 : 210,
                    'height' => $orientation === 'L' ? 210 : 297,
                ];

                $pdf->AddPage($orientation, [$pageSize['width'], $pageSize['height']]);
                $pdf->Image($absolutePath, 0, 0, $pageSize['width'], $pageSize['height']);

                return true;
            } catch (\Throwable) {
                $pageSize = null;

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

    private function labelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width, bool $centeredValue = false): void
    {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($x, $y);
        $labelWidth = 25;
        $pdf->Cell($labelWidth, 4.5, $this->toPdfText($label), 0, 0, 'L');
        $pdf->Cell(4, 4.5, ':', 0, 0, 'L');

        $align = $centeredValue ? 'C' : 'L';
        $pdf->Cell($width - $labelWidth - 4, 4.5, $this->toPdfText($value), 0, 0, $align);
    }

    private function centeredLabelLine(Fpdi $pdf, string $label, string $value, float $x, float $y, float $width): void
    {
        $pdf->SetFont('Arial', '', 10);
        $text = $label . ' : ' . $value;
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, 4.5, $this->toPdfText($text), 0, 0, 'C');
    }

    private function romanPartyBlock(Fpdi $pdf, string $roman, string $firstLabel, string $firstValue, string $secondLabel, string $secondValue, float $x, float $y, float $width): void
    {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($x, $y);
        $pdf->Cell(8, 5, $this->toPdfText($roman), 0, 0, 'L');
        $labelWidth = 18;
        $pdf->Cell($labelWidth, 5, $this->toPdfText($firstLabel), 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'L');
        $pdf->Cell($width - 8 - $labelWidth - 4, 5, $this->toPdfText($firstValue), 0, 0, 'L');

        $pdf->Ln(6);
        $pdf->SetX($x + 8);
        $pdf->Cell($labelWidth, 5, $this->toPdfText($secondLabel), 0, 0, 'L');
        $pdf->Cell(4, 5, ':', 0, 0, 'L');
        $pdf->MultiCell($width - 8 - $labelWidth - 4, 5, $this->toPdfText($secondValue), 0, 'L');
    }

    private function setText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize, string $style = '', string $align = 'L'): void
    {
        $pdf->SetFont('Arial', $style, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $this->toPdfText($text), 0, 0, $align);
    }

    private function composeFlexibleWorkSentence(string $pekerjaan, string $periode, string $predikat): string
    {
        $sentence = $pekerjaan;

        if ($periode !== '') {
            $sentence .= ' pada periode ' . $periode;
        }

        if ($predikat !== '') {
            $sentence .= ' dengan predikat ' . $predikat;
        }

        return $sentence;
    }

    private function formatDate(mixed $value, string $format = 'd F Y'): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat($format);
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
