<?php
declare(strict_types=1);

namespace App\Domain;

use App\Repository\ImportRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class XlsxImportService
{
    public function __construct(
        private ImportRepository $imports,
        private ProductImportService $products,
    ) {
    }

    public function import(string $importId, string $filePath): void
    {
        $rows = IOFactory::load($filePath)->getActiveSheet()->toArray(null, true, true, true);
        $headers = array_shift($rows) ?: [];
        $this->imports->markProcessing($importId, count($rows));
        $errors = [];

        foreach ($rows as $rowNumber => $row) {
            try {
                $data = [];
                foreach ($headers as $column => $header) {
                    $data[trim((string) $header)] = $row[$column] ?? '';
                }
                $this->products->importRow($data);
            } catch (\Throwable $exception) {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $exception->getMessage(),
                ];
            }
            $this->imports->incrementProcessed($importId);
        }

        $this->imports->complete($importId, $errors);
    }
}