<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use RuntimeException;

class EkinerjaPdfImportService
{
    public function import(UploadedFile|string $pdfFile, Collection $pegawais, int $bulan, int $tahun): array
    {
        $path = $pdfFile instanceof UploadedFile ? $pdfFile->getRealPath() : (string) $pdfFile;
        if (!$path || !is_file($path)) {
            throw new RuntimeException('File PDF tidak ditemukan.');
        }

        $text = $this->extractText($path);
        if (trim($text) === '') {
            throw new RuntimeException('Isi PDF tidak dapat dibaca. Pastikan file PDF berupa teks, bukan hanya hasil scan gambar.');
        }

        $parsedPeriod = $this->extractPeriod($text);
        if ($parsedPeriod && ((int) $parsedPeriod['bulan'] !== $bulan || (int) $parsedPeriod['tahun'] !== $tahun)) {
            throw new RuntimeException(sprintf(
                'Periode PDF tidak sesuai. PDF terbaca untuk %s %d, sedangkan input TPP menggunakan %s %d.',
                $this->monthName((int) $parsedPeriod['bulan']),
                (int) $parsedPeriod['tahun'],
                $this->monthName($bulan),
                $tahun
            ));
        }

        $records = $this->parseRecords($text);

        $pegawaiByNip = $pegawais
            ->filter(fn ($pegawai) => filled($pegawai->nip))
            ->keyBy(fn ($pegawai) => $this->normalizeNip((string) $pegawai->nip));

        $pegawaiByNama = $pegawais
            ->filter(fn ($pegawai) => filled($pegawai->nama))
            ->groupBy(fn ($pegawai) => $this->normalizeNama((string) $pegawai->nama));

        $matched = [];
        $unmatched = [];
        $matchedBy = [
            'nip' => 0,
            'nama' => 0,
        ];

        foreach ($records as $record) {
            $pegawai = null;
            $matchBy = null;

            $nip = $this->normalizeNip((string) ($record['nip'] ?? ''));
            if ($nip !== '') {
                $pegawai = $pegawaiByNip->get($nip);
                if ($pegawai) {
                    $matchBy = 'nip';
                }
            }

            if (!$pegawai) {
                $pegawai = $this->findPegawaiByNama((string) ($record['nama'] ?? ''), $pegawaiByNama, $pegawais);
                if ($pegawai) {
                    $matchBy = 'nama';
                }
            }

            if (!$pegawai) {
                $unmatched[] = $record;
                continue;
            }

            $matchedBy[$matchBy]++;
            $matched[(int) $pegawai->id] = [
                'nip' => $nip,
                'nama_pdf' => $record['nama'],
                'nama_pegawai' => $pegawai->nama,
                'match_by' => $matchBy,
                'kehadiran' => $record['kehadiran'],
                'perilaku' => $record['perilaku'],
                'produktivitas' => $record['produktivitas'],
            ];
        }

        return [
            'period' => $parsedPeriod,
            'records' => $records,
            'record_count' => count($records),
            'matched' => $matched,
            'matched_count' => count($matched),
            'matched_by' => $matchedBy,
            'unmatched' => $unmatched,
        ];
    }

    public function extractText(string $path): string
    {
        $layoutText = $this->extractViaPdftotext($path, true);
        if (trim($layoutText) !== '') {
            return $this->normalizeExtractedText($layoutText);
        }

        $plainText = $this->extractViaPdftotext($path, false);
        if (trim($plainText) !== '') {
            return $this->normalizeExtractedText($plainText);
        }

        return $this->normalizeExtractedText($this->extractPurePhp($path));
    }

    private function extractViaPdftotext(string $path, bool $layout): string
    {
        $binary = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? 'pdftotext.exe' : 'pdftotext';
        $check = @shell_exec($binary . ' -v 2>&1');
        if (!is_string($check) || trim($check) === '') {
            return '';
        }

        $layoutFlag = $layout ? ' -layout' : '';
        $command = sprintf('%s%s %s - 2>NUL', $binary, $layoutFlag, escapeshellarg($path));
        if (PHP_OS_FAMILY !== 'Windows') {
            $command = sprintf('%s%s %s - 2>/dev/null', $binary, $layoutFlag, escapeshellarg($path));
        }

        $output = @shell_exec($command);
        return is_string($output) ? $output : '';
    }

    private function extractPurePhp(string $path): string
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            return '';
        }

        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $matches);
        $chunks = [];

        foreach ($matches[1] ?? [] as $stream) {
            $decoded = $this->decodePdfStream($stream);
            if (!is_string($decoded) || $decoded === '' || !str_contains($decoded, 'BT')) {
                continue;
            }

            preg_match_all('/BT(.*?)ET/s', $decoded, $blocks);
            foreach ($blocks[1] ?? [] as $block) {
                $blockText = $this->extractTextFromBlock($block);
                if ($blockText !== '') {
                    $chunks[] = $blockText;
                }
            }
        }

        return implode("\n", $chunks);
    }

    private function decodePdfStream(string $stream): string
    {
        $candidates = [
            @zlib_decode($stream),
            @gzuncompress($stream),
        ];

        if (strlen($stream) > 6) {
            $candidates[] = @gzinflate(substr($stream, 2));
            $candidates[] = @gzinflate(substr($stream, 2, -4));
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return $stream;
    }

    private function extractTextFromBlock(string $block): string
    {
        $result = '';
        $pattern = '/\[(.*?)\]\s*TJ|\(((?:\\\\.|[^\\)])*)\)\s*Tj|\(((?:\\\\.|[^\\)])*)\)\s*[\'\"]/s';
        if (preg_match_all($pattern, $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (($match[1] ?? '') !== '') {
                    $result .= $this->extractFromArrayText($match[1]);
                } else {
                    $text = ($match[2] ?? '') !== '' ? $match[2] : ($match[3] ?? '');
                    $result .= $this->decodePdfString($text);
                }
                $result .= "\n";
            }
        }

        return trim($result);
    }

    private function extractFromArrayText(string $arrayText): string
    {
        $result = '';
        if (preg_match_all('/\(((?:\\\\.|[^\\)])*)\)|<([0-9A-Fa-f]+)>/s', $arrayText, $parts, PREG_SET_ORDER)) {
            foreach ($parts as $part) {
                if (($part[1] ?? '') !== '') {
                    $result .= $this->decodePdfString($part[1]);
                } elseif (($part[2] ?? '') !== '') {
                    $result .= $this->decodeHexString($part[2]);
                }
            }
        }
        return $result;
    }

    private function decodePdfString(string $text): string
    {
        $text = preg_replace_callback('/\\\\([0-7]{1,3})/', fn ($m) => chr(octdec($m[1])), $text) ?? $text;
        return strtr($text, [
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\b' => "\b",
            '\\f' => "\f",
            '\\\\' => '\\',
            '\\(' => '(',
            '\\)' => ')',
        ]);
    }

    private function decodeHexString(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex) ?? $hex;
        if ($hex === '' || strlen($hex) % 2 !== 0) {
            return '';
        }

        $bin = @hex2bin($hex);
        return $bin === false ? '' : $bin;
    }

    private function normalizeExtractedText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-8, Windows-1252, ISO-8859-1');
            if (is_string($converted) && $converted !== '') {
                $text = $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]+/', ' ', $text) ?? $text;
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
        return trim($text);
    }

    private function extractPeriod(string $text): ?array
    {
        if (!preg_match('/Bulan\s+([A-Za-z]+)\s+Tahun\s+(\d{4})/iu', $text, $matches)) {
            return null;
        }

        $bulan = $this->parseMonthName($matches[1]);
        if (!$bulan) {
            return null;
        }

        return ['bulan' => $bulan, 'tahun' => (int) $matches[2]];
    }

    private function parseRecords(string $text): array
    {
        $layoutRecords = $this->parseLayoutRecords($text);
        if (count($layoutRecords) >= 10) {
            return $layoutRecords;
        }

        return $this->parseFallbackRecords($text);
    }

    private function parseLayoutRecords(string $text): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\n+/', $text) ?: []), fn ($line) => $line !== ''));
        $records = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            if (!preg_match('/^(\d{1,2})\s+(?:I|II|III|IV)\/[a-e]\b/i', $line)) {
                continue;
            }

            if (!preg_match('/^(?:\d{1,2})\s+(?:I|II|III|IV)\/[a-e]\s+(?:\d[\d\.]*,\d{2}\s+){4}(\d{1,3}(?:\.\d{1,2})?)\s+\d[\d\.]*,\d{2}\s+(\d{1,3}(?:\.\d{1,2})?)\s+\d[\d\.]*,\d{2}\s+(\d{1,3}(?:\.\d{1,2})?)\s+\d[\d\.]*,\d{2}/', $line, $m)) {
                continue;
            }

            $nip = '';
            for ($j = $i + 1; $j <= min($count - 1, $i + 2); $j++) {
                if (preg_match('/(\d{18})/', $lines[$j], $nipMatch)) {
                    $nip = $this->normalizeNip($nipMatch[1]);
                    break;
                }
            }
            if ($nip === '') {
                continue;
            }

            $name = $i > 0 ? ltrim($lines[$i - 1], ',. ') : '';

            $records[] = [
                'nip' => $nip,
                'nama' => $name,
                'kehadiran' => (float) $m[1],
                'perilaku' => (float) $m[2],
                'produktivitas' => (float) $m[3],
            ];
        }

        return $records;
    }

    private function findPercentageSequenceInColumns(array $columns): ?array
    {
        $count = count($columns);
        for ($i = 0; $i <= $count - 6; $i++) {
            $a = $this->parsePercentageColumn($columns[$i]);
            $b = $this->parsePercentageColumn($columns[$i + 2]);
            $c = $this->parsePercentageColumn($columns[$i + 4]);
            if ($a === null || $b === null || $c === null) {
                continue;
            }

            if (!$this->looksLikeMoney($columns[$i + 1]) || !$this->looksLikeMoney($columns[$i + 3]) || !$this->looksLikeMoney($columns[$i + 5])) {
                continue;
            }

            return [$a, $b, $c];
        }

        return null;
    }

    private function parsePercentageColumn(string $value): ?float
    {
        $value = trim($value);
        if (!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $value)) {
            return null;
        }

        $number = (float) $value;
        return ($number >= 0 && $number <= 100) ? $number : null;
    }

    private function looksLikeMoney(string $value): bool
    {
        return (bool) preg_match('/^\d[\d\.]*,\d{2}$/', trim($value));
    }

    private function parseFallbackRecords(string $text): array
    {
        $lines = array_values(array_filter(array_map(fn ($line) => trim($line), preg_split('/\n+/', $text) ?: []), fn ($line) => $line !== ''));
        $records = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            if (!preg_match('/^\d{18}$/', $lines[$i])) {
                continue;
            }

            $candidateNumbers = [];
            for ($j = $i + 1; $j < $count; $j++) {
                if (preg_match('/^\d{18}$/', $lines[$j])) {
                    break;
                }
                $line = trim($lines[$j]);
                if (preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $line)) {
                    $number = (float) $line;
                    if ($number >= 0 && $number <= 100) {
                        $candidateNumbers[] = $number;
                    }
                }
            }

            if (count($candidateNumbers) < 3) {
                continue;
            }

            $records[] = [
                'nip' => $this->normalizeNip($lines[$i]),
                'nama' => $this->findNameBefore($lines, $i),
                'kehadiran' => (float) $candidateNumbers[0],
                'perilaku' => (float) $candidateNumbers[1],
                'produktivitas' => (float) $candidateNumbers[2],
            ];
        }

        return $records;
    }

    private function findNameBefore(array $lines, int $index): string
    {
        for ($i = $index - 1; $i >= max(0, $index - 5); $i--) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^\d+$/', $line) || preg_match('/^Kelas\s*:/i', $line) || preg_match('/^(I|II|III|IV)\/[a-e]$/i', $line)) {
                continue;
            }
            if (str_contains($line, 'Tanggal Cetak') || str_contains($line, 'Daftar Perhitungan')) {
                continue;
            }
            return ltrim($line, ',. ');
        }

        return '';
    }

    private function findPegawaiByNama(string $namaPdf, Collection $pegawaiByNama, Collection $pegawais): mixed
    {
        $normalizedPdfName = $this->normalizeNama($namaPdf);
        if ($normalizedPdfName === '') {
            return null;
        }

        $exactMatches = $pegawaiByNama->get($normalizedPdfName, collect());
        if ($exactMatches->count() === 1) {
            return $exactMatches->first();
        }

        $bestPegawai = null;
        $bestScore = 0;

        foreach ($pegawais as $pegawai) {
            $normalizedPegawaiName = $this->normalizeNama((string) ($pegawai->nama ?? ''));
            if ($normalizedPegawaiName === '') {
                continue;
            }

            $score = $this->scoreNamaMatch($normalizedPdfName, $normalizedPegawaiName);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestPegawai = $pegawai;
            } elseif ($score === $bestScore && $score > 0) {
                $bestPegawai = null;
            }
        }

        return $bestScore >= 85 ? $bestPegawai : null;
    }

    private function scoreNamaMatch(string $pdfName, string $pegawaiName): int
    {
        if ($pdfName === '' || $pegawaiName === '') {
            return 0;
        }

        if ($pdfName === $pegawaiName) {
            return 100;
        }

        if (str_starts_with($pdfName, $pegawaiName . ' ') || str_ends_with($pdfName, ' ' . $pegawaiName)) {
            return 96;
        }

        if (str_contains($pdfName, ' ' . $pegawaiName . ' ') || str_contains($pegawaiName, ' ' . $pdfName . ' ')) {
            return 94;
        }

        similar_text($pdfName, $pegawaiName, $percent);
        $percent = (float) $percent;

        $pdfTokens = $this->tokenizeNama($pdfName);
        $pegawaiTokens = $this->tokenizeNama($pegawaiName);
        if ($pdfTokens === [] || $pegawaiTokens === []) {
            return (int) round($percent);
        }

        $intersection = array_values(array_intersect($pdfTokens, $pegawaiTokens));
        $tokenCoverage = count($intersection) / max(1, count($pegawaiTokens));
        $startsWithAllPegawaiTokens = array_slice($pdfTokens, 0, count($pegawaiTokens)) === $pegawaiTokens;

        if ($startsWithAllPegawaiTokens && $tokenCoverage >= 1) {
            return 95;
        }

        if ($tokenCoverage >= 1 && abs(count($pdfTokens) - count($pegawaiTokens)) <= 1) {
            return max((int) round($percent), 92);
        }

        if ($tokenCoverage >= 0.8 && $percent >= 80) {
            return max((int) round($percent), 86);
        }

        return (int) round($percent);
    }

    private function tokenizeNama(string $nama): array
    {
        $tokens = preg_split('/\s+/', trim($nama)) ?: [];
        return array_values(array_filter($tokens, fn ($token) => $token !== ''));
    }

    private function normalizeNama(string $nama): string
    {
        $nama = trim($nama);
        if ($nama === '') {
            return '';
        }

        $nama = function_exists('mb_strtoupper') ? mb_strtoupper($nama, 'UTF-8') : strtoupper($nama);
        $nama = str_replace([',', '.', "'", '"', '/', '\\', '(', ')', '[', ']'], ' ', $nama);
        $nama = preg_replace('/\s+/', ' ', $nama) ?? $nama;

        $removeTokens = [
            'DR', 'H', 'HJ', 'IR', 'HJH', 'S SOS', 'SSOS', 'S KOM', 'SKOM', 'SE', 'S E', 'SH', 'S H',
            'ST', 'S T', 'SI', 'S I', 'SIP', 'S IP', 'M SI', 'MSI', 'M M', 'MM', 'MAP', 'M AP', 'AK', 'A MD',
            'AMD', 'S TR I P', 'STRIP', 'S STP', 'SSTP', 'APT', 'SP', 'MP', 'MENG', 'M PD', 'MPD',
        ];

        foreach ($removeTokens as $token) {
            $pattern = '/(^|\s)' . preg_quote($token, '/') . '(?=\s|$)/u';
            $nama = preg_replace($pattern, ' ', $nama) ?? $nama;
        }

        $jabatanStarts = [
            'KEPALA', 'ANALIS', 'PENGELOLA', 'PENELAAH', 'PRANATA', 'PENYUSUN', 'ADMINISTRATOR',
            'PENGADMINISTRASI', 'VERIFIKATOR', 'ARSIPARIS', 'BENDAHARA', 'PELAKSANA', 'AHLI', 'MUDA', 'MADYA', 'PERTAMA',
        ];

        foreach ($jabatanStarts as $keyword) {
            $nama = preg_replace('/\b' . preg_quote($keyword, '/') . '\b.*$/u', '', $nama) ?? $nama;
        }

        $nama = preg_replace('/\s+/', ' ', $nama) ?? $nama;

        return trim($nama);
    }

    private function normalizeNip(string $nip): string
    {
        return preg_replace('/\D+/', '', $nip) ?? '';
    }

    private function parseMonthName(string $name): ?int
    {
        $map = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        $key = function_exists('mb_strtolower') ? mb_strtolower(trim($name)) : strtolower(trim($name));
        return $map[$key] ?? null;
    }

    private function monthName(int $month): string
    {
        return [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'][$month] ?? (string) $month;
    }
}
