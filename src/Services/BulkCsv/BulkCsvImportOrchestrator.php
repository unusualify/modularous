<?php

namespace Unusualify\Modularity\Services\BulkCsv;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Unusualify\Modularity\Contracts\CanBulkSheet;

/**
 * Shared CSV parsing and dry-run / commit / export orchestration for {@see CanBulkSheet} controllers.
 */
class BulkCsvImportOrchestrator
{
    /**
     * @return array{
     *     ok: bool,
     *     dry_run: bool,
     *     tool_key: string,
     *     message?: string,
     *     rows: list<array<string, mixed>>,
     *     summary: array<string, int|bool>
     * }
     */
    public function import(string $csvContent, bool $dryRun, CanBulkSheet $bulkSheet, string $toolKey): array
    {
        $parsed = $this->parseCsv($csvContent, $bulkSheet);

        if (! $parsed['ok']) {
            return [
                'ok' => false,
                'dry_run' => $dryRun,
                'tool_key' => $toolKey,
                'message' => $parsed['error'] ?? 'Invalid CSV.',
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'valid' => 0,
                    'invalid' => 0,
                    'warnings' => 0,
                ],
            ];
        }

        $prepared = $bulkSheet->bulkSheetPrepareAndValidateRows($parsed['records']);

        $invalidCount = collect($prepared)->where('valid', false)->count();
        $validCount = collect($prepared)->where('valid', true)->count();
        $warningCount = collect($prepared)->sum(fn ($r) => count($r['warnings'] ?? []));

        if ($invalidCount > 0) {
            return [
                'ok' => false,
                'dry_run' => $dryRun,
                'tool_key' => $toolKey,
                'message' => 'One or more rows failed validation.',
                'rows' => $prepared,
                'summary' => [
                    'total' => count($prepared),
                    'valid' => $validCount,
                    'invalid' => $invalidCount,
                    'warnings' => $warningCount,
                ],
            ];
        }

        if ($dryRun) {
            $created = 0;
            $updated = 0;
            foreach ($prepared as $row) {
                if (($row['action'] ?? '') === 'create') {
                    $created++;
                } elseif (($row['action'] ?? '') === 'update') {
                    $updated++;
                }
            }

            return [
                'ok' => true,
                'dry_run' => true,
                'tool_key' => $toolKey,
                'rows' => $prepared,
                'summary' => [
                    'total' => count($prepared),
                    'valid' => $validCount,
                    'invalid' => 0,
                    'warnings' => $warningCount,
                    'created' => $created,
                    'updated' => $updated,
                ],
            ];
        }

        $counts = ['created' => 0, 'updated' => 0];

        DB::transaction(function () use ($bulkSheet, $prepared, &$counts): void {
            $counts = $bulkSheet->bulkSheetCommitPreparedRows($prepared);
        }, 3);

        return [
            'ok' => true,
            'dry_run' => false,
            'tool_key' => $toolKey,
            'rows' => $prepared,
            'summary' => [
                'total' => count($prepared),
                'valid' => $validCount,
                'invalid' => 0,
                'warnings' => $warningCount,
                'created' => $counts['created'] ?? 0,
                'updated' => $counts['updated'] ?? 0,
            ],
        ];
    }

    public function streamExport(CanBulkSheet $bulkSheet, ?string $filename = null): StreamedResponse
    {
        $filename = $filename ?? $bulkSheet->bulkSheetExportDownloadFilename();

        $filename = date('Y-m-d_His') . '_' . $filename;
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($bulkSheet): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            $bulkSheet->bulkSheetStreamExport($out);
            fclose($out);
        }, 200, $headers);
    }

    /**
     * Canonical field key => CSV header synonyms (lowercase, without spaces). First synonym is the export header.
     *
     * @return array<string, list<string>>
     */
    public function csvCanonicalToHeaderAliases(CanBulkSheet $bulkSheet): array
    {
        $out = [];
        foreach ($bulkSheet->bulkSheetFields() as $field) {
            $out[$field['key']] = array_values(array_unique(array_merge(
                [$field['key']],
                $field['aliases'] ?? []
            )));
        }

        return $out;
    }

    /**
     * @return array{ok: bool, error?: string, records?: list<array{line: int, values: array<string, string>}>}
     */
    protected function parseCsv(string $csvContent, CanBulkSheet $bulkSheet): array
    {
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent) ?? $csvContent;
        $csvContent = str_replace("\r\n", "\n", $csvContent);
        $csvContent = str_replace("\r", "\n", $csvContent);
        $lines = array_filter(explode("\n", $csvContent), static fn ($l) => trim((string) $l) !== '');

        if ($lines === []) {
            return ['ok' => false, 'error' => 'CSV is empty.'];
        }

        $headerLine = (string) array_shift($lines);
        $delimiter = $this->detectCsvDelimiter($headerLine, $bulkSheet);
        $headerCells = str_getcsv($headerLine, $delimiter);

        $columnMap = $this->mapHeaderRow($headerCells, $bulkSheet);

        if ($columnMap === null) {
            return [
                'ok' => false,
                'error' => 'CSV is missing required columns. Expected headers: ' . $this->expectedHeadersHint($bulkSheet),
            ];
        }

        $canonicalKeys = array_keys($this->csvCanonicalToHeaderAliases($bulkSheet));

        $records = [];
        $lineNumber = 2;

        foreach ($lines as $line) {
            $values = str_getcsv((string) $line, $delimiter);
            $row = [];
            foreach ($canonicalKeys as $canonical) {
                $idx = $columnMap[$canonical] ?? null;
                $row[$canonical] = $idx !== null ? trim((string) ($values[$idx] ?? '')) : '';
            }
            $records[] = ['line' => $lineNumber, 'values' => $row];
            $lineNumber++;
        }

        return ['ok' => true, 'records' => $records];
    }

    /**
     * Prefer comma (RFC-style), then semicolon (common Excel EU locale), then tab.
     */
    protected function detectCsvDelimiter(string $headerLine, CanBulkSheet $bulkSheet): string
    {
        foreach ([',', ';', "\t"] as $delimiter) {
            $cells = str_getcsv($headerLine, $delimiter);
            if ($this->mapHeaderRow($cells, $bulkSheet) !== null) {
                return $delimiter;
            }
        }

        return ',';
    }

    /**
     * @param list<string> $headerCells
     * @return array<string, int>|null canonical => column index
     */
    protected function mapHeaderRow(array $headerCells, CanBulkSheet $bulkSheet): ?array
    {
        $headerToCanonical = [];
        foreach ($this->csvCanonicalToHeaderAliases($bulkSheet) as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                $headerToCanonical[mb_strtolower(trim($alias))] = $canonical;
            }
        }

        $map = [];
        foreach ($headerCells as $i => $h) {
            $low = mb_strtolower(trim((string) $h));
            if (isset($headerToCanonical[$low])) {
                $map[$headerToCanonical[$low]] = $i;
            }
        }

        foreach ($bulkSheet->bulkSheetFields() as $field) {
            if (($field['required'] ?? false) && ! isset($map[$field['key']])) {
                return null;
            }
        }

        return $map;
    }

    protected function expectedHeadersHint(CanBulkSheet $bulkSheet): string
    {
        $parts = [];
        foreach ($bulkSheet->bulkSheetFields() as $field) {
            $aliases = array_merge([$field['key']], $field['aliases'] ?? []);
            $parts[] = implode('/', $aliases);
        }

        return implode(', ', $parts);
    }
}
