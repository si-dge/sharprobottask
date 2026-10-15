<?php
declare(strict_types=1);

namespace Tests;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;

final class ImportApiTest extends TestCase
{
    private string $apiUrl;
    private int $timeoutSeconds;

    protected function setUp(): void
    {
        $this->apiUrl = rtrim((string) (getenv('IMPORT_API_URL') ?: 'http://localhost:8080/api'), '/');
        $this->timeoutSeconds = max(1, (int) (getenv('IMPORT_TEST_TIMEOUT') ?: 60));
    }

    /** @group api */
    public function testImportAndDatabaseResetThroughApi(): void
    {
        $file = (string) (getenv('IMPORT_TEST_FILE') ?: dirname(__DIR__, 2) . '/import_example.xlsx');
        self::assertFileExists($file);

        $this->request('POST', '/database/reset');

        try {
            $expectedProducts = $this->readExpectedProducts($file);
            [$upload, $uploadStatus] = $this->request('POST', '/imports', ['file' => $file]);

            self::assertSame(202, $uploadStatus);
            self::assertArrayHasKey('importId', $upload);

            $import = $this->waitForImport((string) $upload['importId']);
            self::assertSame('completed', $import['status']);
            self::assertSame(count($expectedProducts), (int) $import['total']);
            self::assertSame((int) $import['total'], (int) $import['processed']);
            self::assertSame([], json_decode((string) $import['errors'], true, flags: JSON_THROW_ON_ERROR));

            [$products, $productsStatus] = $this->request('GET', '/products?limit=100');
            self::assertSame(200, $productsStatus);
            self::assertSame(count($expectedProducts), (int) $products['total']);
            self::assertSame($expectedProducts, $this->normalizeProducts($products['items']));

            [$reset, $resetStatus] = $this->request('POST', '/database/reset');
            self::assertSame(200, $resetStatus);
            self::assertSame('ok', $reset['status']);

            [$productsAfterReset, $productsAfterResetStatus] = $this->request('GET', '/products');
            self::assertSame(200, $productsAfterResetStatus);
            self::assertSame(0, (int) $productsAfterReset['total']);
            self::assertSame([], $productsAfterReset['items']);
        } finally {
            $this->request('POST', '/database/reset');
        }
    }

    /** @return array{0: array<string, mixed>, 1: int} */
    private function request(string $method, string $path, ?array $files = null): array
    {
        $command = [
            'curl', '-sS', '--fail-with-body', '--max-time', (string) $this->timeoutSeconds,
            '-X', $method, '-w', '\\n%{http_code}',
        ];
        if ($files !== null) {
            $command[] = '-F';
            $command[] = 'file=@' . $files['file'] . ';type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        $command[] = $this->apiUrl . $path;

        $escapedCommand = implode(' ', array_map('escapeshellarg', $command));
        exec($escapedCommand . ' 2>&1', $output, $exitCode);
        $response = implode("\n", $output);
        $statusLine = strrpos($response, "\n");
        $status = $statusLine === false ? 0 : (int) substr($response, $statusLine + 1);
        $body = $statusLine === false ? $response : substr($response, 0, $statusLine);

        self::assertSame(0, $exitCode, sprintf('curl failed for %s %s: %s', $method, $path, $body));
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded, sprintf('Invalid JSON response for %s %s: %s', $method, $path, $body));

        return [$decoded, $status];
    }

    /** @return array<string, mixed> */
    private function waitForImport(string $importId): array
    {
        $deadline = microtime(true) + $this->timeoutSeconds;
        do {
            [$import, $status] = $this->request('GET', '/imports/' . rawurlencode($importId));
            self::assertSame(200, $status);
            if (in_array($import['status'] ?? '', ['completed', 'completed_with_errors', 'failed'], true)) {
                return $import;
            }
            usleep(250000);
        } while (microtime(true) < $deadline);

        self::fail(sprintf('Import %s did not finish within %d seconds', $importId, $this->timeoutSeconds));
    }

    /** @return list<array<string, mixed>> */
    private function readExpectedProducts(string $file): array
    {
        $rows = IOFactory::load($file)->getActiveSheet()->toArray(null, true, true, true);
        $headers = array_shift($rows) ?: [];
        $products = [];

        foreach ($rows as $row) {
            $data = [];
            foreach ($headers as $column => $header) {
                $data[trim((string) $header)] = $row[$column] ?? '';
            }
            $price = $this->number($data['Цена: Цена продажи'] ?? 0);
            $purchase = $this->number($data['Закупочная цена'] ?? $data['Цена: Закупочная цена'] ?? 0);
            $products[] = [
                'id' => trim((string) ($data['UUID'] ?? '')),
                'external_code' => trim((string) ($data['Внешний код'] ?? '')),
                'name' => trim((string) ($data['Наименование'] ?? '')),
                'description' => (string) ($data['Описание'] ?? ''),
                'price' => $price,
                'discount' => $price > 0 ? round(($price - $purchase) / $price * 100, 2) : 0.0,
            ];
        }

        usort($products, static fn (array $left, array $right): int => $left['name'] <=> $right['name']);

        return $products;
    }

    /** @param list<array<string, mixed>> $products @return list<array<string, mixed>> */
    private function normalizeProducts(array $products): array
    {
        $normalized = array_map(static fn (array $product): array => [
            'id' => (string) $product['id'],
            'external_code' => (string) $product['external_code'],
            'name' => (string) $product['name'],
            'description' => (string) $product['description'],
            'price' => (float) $product['price'],
            'discount' => (float) $product['discount'],
        ], $products);
        usort($normalized, static fn (array $left, array $right): int => $left['name'] <=> $right['name']);

        return $normalized;
    }

    private function number(mixed $value): float
    {
        return (float) str_replace(',', '.', (string) $value);
    }
}