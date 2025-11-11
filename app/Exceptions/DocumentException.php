<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentException extends Exception
{
    protected $statusCode = 400;

    protected $errorCode = 'DOCUMENT_ERROR';

    public function __construct(string $message = '', int $statusCode = 400, string $errorCode = 'DOCUMENT_ERROR', ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function render(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'status_code' => $this->getStatusCode(),
            ], $this->getStatusCode());
        }

        return response()->view('errors.document', [
            'message' => $this->getMessage(),
            'errorCode' => $this->getErrorCode(),
        ], $this->getStatusCode());
    }
}
