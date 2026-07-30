<?php

namespace App\Filament\Resources\LinkResource\Pages;

use App\Exceptions\LinkGenerationException;
use App\Filament\Resources\LinkResource;
use App\Services\LinkShortenerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateLink extends CreateRecord
{
    protected static string $resource = LinkResource::class;

    public function getTitle(): string
    {
        return __('links.pages.create.heading');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('links.actions.create_another'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->alpineClickHandler(null)
            ->url(static::getResource()::getUrl('index'));
    }

    /**
     * @throws Halt
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();

        if (!$user) {
            Log::warning('Link creation attempted without an authenticated user.');

            Notification::make()
                ->title(__('links.notifications.unauthorized.title'))
                ->body(__('links.notifications.unauthorized.body'))
                ->danger()
                ->send();

            $this->halt();
        }

        try {
            $service = app(LinkShortenerService::class);
            return $service->createShortLink($user, $data['url']);

        } catch (LinkGenerationException $e) {
            report($e);

            Notification::make()
                ->title(__('links.notifications.generation_failed.title'))
                ->body($e->getErrorType()->label())
                ->danger()
                ->send();

            $this->halt();

        } catch (QueryException $e) {
            report($e);

            Notification::make()
                ->title(__('links.notifications.database_error.title'))
                ->body(__('links.notifications.database_error.body'))
                ->danger()
                ->send();

            $this->halt();

        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title(__('links.notifications.unexpected_error.title'))
                ->body(__('links.notifications.unexpected_error.body'))
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('links.notifications.created');
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
