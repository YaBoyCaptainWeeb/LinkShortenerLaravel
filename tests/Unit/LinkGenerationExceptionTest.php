<?php

namespace Tests\Unit;

use App\Enums\LinkGenerationError;
use App\Exceptions\LinkGenerationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LinkGenerationExceptionTest extends TestCase
{
    public function test_error_messages_follow_the_current_locale(): void
    {
        app()->setLocale('en');

        $this->assertSame(
            'The link generation request timed out.',
            LinkGenerationError::TIMEOUT->label(),
        );
        $this->assertSame(
            'No available short codes remain. The database is full.',
            LinkGenerationError::DATABASE_FULL->label(),
        );

        app()->setLocale('ru');

        $this->assertSame(
            'Превышено время ожидания генерации ссылки.',
            LinkGenerationError::TIMEOUT->label(),
        );
        $this->assertSame(
            'Свободные короткие коды закончились. База данных переполнена.',
            LinkGenerationError::DATABASE_FULL->label(),
        );
    }

    public function test_report_writes_the_error_type_and_generation_context(): void
    {
        app()->setLocale('en');

        $exception = new LinkGenerationException(
            LinkGenerationError::TIMEOUT,
            [
                'total_attempts' => 10,
                'time_spent' => '1000 ms',
            ],
        );

        $this->assertSame(
            'Short link generation failed [LINK_GENERATION_TIMEOUT].',
            $exception->getMessage(),
        );

        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function (string $message, array $context) use ($exception): bool {
                return $message === 'Short link generation failed.'
                    && $context['error_type'] === LinkGenerationError::TIMEOUT->value
                    && $context['total_attempts'] === 10
                    && $context['time_spent'] === '1000 ms'
                    && $context['exception'] === $exception;
            });

        $exception->report();
    }

    public function test_render_returns_a_localized_json_error(): void
    {
        app()->setLocale('ru');

        $exception = new LinkGenerationException(
            LinkGenerationError::TIMEOUT,
            [],
        );
        $request = Request::create(
            '/api/links',
            'POST',
            server: ['HTTP_ACCEPT' => 'application/json'],
        );

        $response = $exception->render($request);

        $this->assertSame(408, $response->getStatusCode());
        $this->assertSame([
            'status' => 'error',
            'code' => LinkGenerationError::TIMEOUT->value,
            'message' => 'Превышено время ожидания генерации ссылки.',
        ], $response->getData(true));
    }
}
