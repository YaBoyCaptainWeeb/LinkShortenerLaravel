<?php

namespace App\Exceptions;

use Exception;
use App\Enums\LinkGenerationError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LinkGenerationException extends Exception
{
    private LinkGenerationError $errorType;
    private array $context;

    public function getErrorType(): LinkGenerationError
    {
        return $this->errorType;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function __construct(LinkGenerationError $errorType, array $context)
    {
        parent::__construct($errorType->label());

        $this->errorType = $errorType;
        $this->context = $context;
    }

    public function report(): void
    {
        Log::critical("Сбой генерации ссылки [{$this->errorType->value}]",
            array_merge([
                'error_message' => $this->getMessage()
            ], $this->context));
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
               'status' => 'error',
               'code' => $this->errorType->value,
               'message' => $this->getMessage(),
            ], $this->errorType->httpStatus());
        }

        return back()->withErrors(['code' => $this->getMessage()]);
    }
}
