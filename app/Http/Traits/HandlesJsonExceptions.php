<?php

namespace App\Http\Traits;

use Illuminate\Validation\ValidationException;
use Throwable;

trait HandlesJsonExceptions
{
    protected function jsonExceptionResponse(Throwable $e, string $message, int $status = 500)
    {
        if ($e instanceof ValidationException) {
            $message = $e->getMessage();
            $status = 422;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => $e->getMessage(),
        ], $status);
    }
}
