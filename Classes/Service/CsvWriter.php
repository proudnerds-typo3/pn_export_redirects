<?php

declare(strict_types=1);

namespace Proudnerds\PnExportRedirects\Service;

/**
 * CSV that opens directly in a Dutch Excel: semicolon-separated, UTF-8 with BOM.
 */
final class CsvWriter
{
    private const DELIMITER = ';';
    private const UTF8_BOM = "\xEF\xBB\xBF";

    /**
     * @param list<list<string>> $rows
     */
    public function write(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Could not open a temporary stream for the CSV export', 1762430001);
        }
        foreach ($rows as $row) {
            fputcsv($stream, array_map($this->neutralizeFormula(...), $row), self::DELIMITER, '"', '', "\r\n");
        }
        rewind($stream);
        $csv = (string)stream_get_contents($stream);
        fclose($stream);

        return self::UTF8_BOM . $csv;
    }

    /**
     * Spreadsheets evaluate cells starting with these characters as formulas (CSV injection),
     * and redirect fields are editor input.
     */
    private function neutralizeFormula(string $value): string
    {
        if ($value !== '' && str_contains("=+-@\t\r", $value[0])) {
            return "'" . $value;
        }
        return $value;
    }
}
