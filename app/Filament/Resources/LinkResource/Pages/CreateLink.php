<?php

namespace App\Filament\Resources\LinkResource\Pages;

use App\Exceptions\LinkGenerationException;
use App\Filament\Resources\LinkResource;
use App\Services\LinkShortenerService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Random\RandomException;
use Throwable;

class CreateLink extends CreateRecord
{
    protected static string $resource = LinkResource::class;

    /**
     * @throws RandomException
     * @throws LinkGenerationException|Halt
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();

        if (!$user) {
            Log::error('CreateLink: Пользователь не аутентифицирован');

            Notification::make()
                ->title('Ошибка авторизации')
                ->body('Необходимо войти в систему для создания ссылки')
                ->danger()
                ->send();

            $this->halt();
        }

        try {
            $service = app(LinkShortenerService::class);
            return $service->createShortLink($user, $data['url']);

        } catch (LinkGenerationException $e) {
            // Логируем критическую ошибку (она уже логируется в LinkGenerationException::report())
            Log::warning('CreateLink: Ошибка генерации ссылки', [
                'error_type' => $e->getErrorType()->value ?? 'unknown',
                'user_id' => $user->id,
                'url' => $data['url']
            ]);

            Notification::make()
                ->title('Ошибка генерации ссылки')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();

        } catch (QueryException $e) {
            // Ошибки базы данных (дубликат кода, проблемы с БД)
            Log::error('CreateLink: Ошибка базы данных', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => $user->id,
                'url' => $data['url']
            ]);

            Notification::make()
                ->title('Ошибка базы данных')
                ->body('Не удалось сохранить ссылку. Попробуйте позже.')
                ->danger()
                ->send();

            $this->halt();

        } catch (Throwable $e) {
            // Все остальные непредвиденные ошибки
            Log::critical('CreateLink: Непредвиденная ошибка', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'url' => $data['url']
            ]);

            Notification::make()
                ->title('Произошла ошибка')
                ->body('Не удалось создать ссылку. Обратитесь к администратору.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return "Ссылка создана успешно";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
