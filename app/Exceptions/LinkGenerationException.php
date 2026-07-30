<?php

namespace App\Exceptions;

use Exception;
use App\Enums\LinkGenerationError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        parent::__construct("Short link generation failed [{$errorType->value}].");

        $this->errorType = $errorType;
        $this->context = $context;
    }

    public function report(): void
    {
        Log::critical('Short link generation failed.', array_merge(
            $this->context,
            [
                'error_type' => $this->errorType->value,
                'user_id' => Auth::id(),
                'exception' => $this,
            ],
        ));
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
               'status' => 'error',
               'code' => $this->errorType->value,
               'message' => $this->errorType->label(),
            ], $this->errorType->httpStatus());
        }

        return back()->withErrors(['code' => $this->errorType->label()]);
    }
}
