<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileValidationService
{
    /**
     * Maximum file size in bytes (20MB)
     */
    const MAX_FILE_SIZE = 20 * 1024 * 1024;

    /**
     * Maximum total upload size in bytes (100MB)
     */
    const MAX_TOTAL_SIZE = 100 * 1024 * 1024;

    /**
     * Allowed MIME types
     */
    const ALLOWED_MIME_TYPES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
    ];

    /**
     * Dangerous file extensions that should never be allowed
     */
    const DANGEROUS_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
        'sh', 'app', 'deb', 'rpm', 'msi', 'dmg', 'pkg', 'apk',
    ];

    /**
     * Validate a single file
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds 20MB limit';
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            $errors[] = 'File type not allowed: '.$file->getMimeType();
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            $errors[] = "Dangerous file extension detected: .{$extension}";
        }

        // Check for double extensions (e.g., file.pdf.exe)
        $filename = $file->getClientOriginalName();
        if (preg_match('/\.[^.]+\.[^.]+$/', $filename)) {
            $parts = explode('.', $filename);
            if (count($parts) > 2) {
                $secondToLastExt = strtolower($parts[count($parts) - 2]);
                if (in_array($secondToLastExt, self::DANGEROUS_EXTENSIONS)) {
                    $errors[] = 'Suspicious double extension detected';
                }
            }
        }

        // Scan for viruses if ClamAV is available
        if ($this->isClamAVAvailable()) {
            $scanResult = $this->scanFileWithClamAV($file);
            if (! $scanResult['clean']) {
                $errors[] = 'Virus detected: '.$scanResult['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
            ],
        ];
    }

    /**
     * Validate multiple files
     */
    public function validateFiles(array $files): array
    {
        $results = [];
        $totalSize = 0;
        $allValid = true;

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $result = $this->validateFile($file);
            $results[$index] = $result;
            $totalSize += $file->getSize();

            if (! $result['valid']) {
                $allValid = false;
            }
        }

        // Check total size
        if ($totalSize > self::MAX_TOTAL_SIZE) {
            $allValid = false;
            $results['total_size_error'] = 'Total upload size exceeds 100MB limit';
        }

        return [
            'valid' => $allValid,
            'total_size' => $totalSize,
            'results' => $results,
        ];
    }

    /**
     * Check if ClamAV is available
     */
    protected function isClamAVAvailable(): bool
    {
        // Check if ClamAV socket exists (Linux/Unix)
        if (file_exists('/var/run/clamav/clamd.ctl')) {
            return true;
        }

        // Check if clamd is running (Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('sc query "ClamAV"', $output, $return);
            return $return === 0;
        }

        // Try to connect to ClamAV daemon
        $socket = @fsockopen('localhost', 3310, $errno, $errstr, 1);
        if ($socket) {
            fclose($socket);
            return true;
        }

        return false;
    }

    /**
     * Scan file with ClamAV
     */
    protected function scanFileWithClamAV(UploadedFile $file): array
    {
        try {
            $filePath = $file->getRealPath();

            // Try using clamdscan first (faster)
            if ($this->commandExists('clamdscan')) {
                exec("clamdscan --no-summary {$filePath}", $output, $return);

                if ($return === 0) {
                    return ['clean' => true, 'message' => 'File is clean'];
                } else {
                    $message = implode(' ', $output);
                    Log::warning('ClamAV detected threat', ['file' => $file->getClientOriginalName(), 'result' => $message]);

                    return ['clean' => false, 'message' => $message];
                }
            }

            // Fallback to clamscan
            if ($this->commandExists('clamscan')) {
                exec("clamscan --no-summary {$filePath}", $output, $return);

                if ($return === 0) {
                    return ['clean' => true, 'message' => 'File is clean'];
                } else {
                    $message = implode(' ', $output);
                    Log::warning('ClamAV detected threat', ['file' => $file->getClientOriginalName(), 'result' => $message]);

                    return ['clean' => false, 'message' => $message];
                }
            }

            // ClamAV not available
            Log::info('ClamAV not available, skipping virus scan');

            return ['clean' => true, 'message' => 'Virus scan skipped (ClamAV not available)'];
        } catch (\Exception $e) {
            Log::error('Error scanning file with ClamAV', ['error' => $e->getMessage()]);

            return ['clean' => true, 'message' => 'Virus scan failed: '.$e->getMessage()];
        }
    }

    /**
     * Check if a command exists
     */
    protected function commandExists(string $command): bool
    {
        $return = shell_exec(sprintf('which %s', escapeshellarg($command)));

        return ! empty($return);
    }

    /**
     * Get human-readable file size
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Get allowed file extensions as string
     */
    public static function getAllowedExtensionsString(): string
    {
        return 'PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF, ZIP, RAR';
    }

    /**
     * Get max file size as string
     */
    public static function getMaxFileSizeString(): string
    {
        return self::formatFileSize(self::MAX_FILE_SIZE);
    }
}
