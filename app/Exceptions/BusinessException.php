<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{
    protected array $errors;
    protected int $statusCode;

    public function __construct(
        string $message = 'Business rule violation',
        array $errors = [],
        int $statusCode = 409
    ) {
        parent::__construct($message);

        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render exception sebagai JSON response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], $this->statusCode);
    }
}
