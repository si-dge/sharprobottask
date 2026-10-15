<?php

declare(strict_types=1);

namespace App\Domain;

use App\Repository\ImportRepository;
use RdKafka\Producer;

final class ImportService
{
    public function __construct(
        private ImportRepository $repository,
        private array $kafka,
        private string $directory,
        private int $maxFileSize,
    ) {}

    public function queue(?array $uploaded): array
    {
        if (!$uploaded) {
            throw new \InvalidArgumentException('An .xlsx file is required');
        }

        if (($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException($this->uploadErrorMessage((int) $uploaded['error']));
        }

        $fileName = (string) ($uploaded['name'] ?? '');
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'xlsx') {
            throw new \InvalidArgumentException('Only .xlsx files are allowed');
        }

        $temporaryPath = (string) ($uploaded['tmp_name'] ?? '');
        if ($temporaryPath === '' || !is_file($temporaryPath) || !is_readable($temporaryPath)) {
            throw new \InvalidArgumentException('Uploaded file is unavailable');
        }

        $fileSize = filesize($temporaryPath);
        if ($fileSize === false || $fileSize > $this->maxFileSize) {
            throw new \InvalidArgumentException(sprintf(
                'The file size must not exceed %d MB',
                (int) ceil($this->maxFileSize / 1024 / 1024),
            ));
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if (!in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ], true)) {
            throw new \InvalidArgumentException('The uploaded file must be a valid XLSX document');
        }

        $id = $this->uuid();
        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Unable to create import directory');
        }
        $filePath = $this->directory . '/' . $id . '.xlsx';
        if (!move_uploaded_file($temporaryPath, $filePath)) {
            throw new \RuntimeException('Unable to store uploaded file');
        }
        $this->repository->createQueued($id);

        try {
            $producer = new Producer();
            $producer->addBrokers($this->kafka['brokers']);
            $producer->newTopic($this->kafka['topic'])->produce(
                RD_KAFKA_PARTITION_UA,
                0,
                (string) json_encode(['id' => $id, 'file' => $filePath], JSON_THROW_ON_ERROR),
                $id,
            );
            $producer->flush(10000);
        } catch (\Throwable $exception) {
            $this->repository->markQueueFailed($id);
            throw new \RuntimeException('Queue unavailable', 0, $exception);
        }

        return ['importId' => $id, 'status' => 'queued'];
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large',
            UPLOAD_ERR_PARTIAL => 'The file upload was interrupted',
            UPLOAD_ERR_NO_TMP_DIR => 'The upload temporary directory is unavailable',
            UPLOAD_ERR_CANT_WRITE => 'The uploaded file could not be written',
            UPLOAD_ERR_NO_FILE => 'An .xlsx file is required',
            default => 'The file upload failed',
        };
    }

    private function uuid(): string
    {
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }

        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
