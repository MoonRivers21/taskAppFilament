<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToPrev')
                ->label('Back')
                ->outlined()
                ->color('blue')
                ->url(fn() => $this->previousUrl ?? $this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-uturn-left'),

            Actions\DeleteAction::make()
                ->label('Move to trash')
                ->icon('heroicon-o-trash'),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

}
