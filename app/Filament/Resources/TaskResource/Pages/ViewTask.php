<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return array(
            Action::make('backToPrev')
                ->label('Back')
                ->outlined()
                ->color('blue')
                ->url(fn() => $this->previousUrl ?? $this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-uturn-left'),

            Action::make('editTask')
                ->hidden(fn(Task $record): bool => $record->status == TaskStatus::DONE)
                ->label('Edit')
                ->url(fn(Model $record): string => route('filament.admin.resources.tasks.edit',
                    array('record' => $record)))
                ->icon('heroicon-o-pencil-square'),

            Action::make('manageSubtask')
                ->hidden(fn(Task $record): bool => $record->status == TaskStatus::DONE)
                ->label('Edit Subtask')
                ->icon('heroicon-o-document-text')
                ->modalFooterActionsAlignment(Alignment::End)
                ->stickyModalHeader()
                ->fillForm(fn(Task $record): array => array(
                    'subtask' => $record->subtask,
                ))
                ->form(array(
                    Repeater::make('subtask')
                        ->hiddenLabel()
                        ->schema(array(
                            TextInput::make('subTaskTitle')
                                ->required()
                                ->columnSpan(2)
                                ->label('Title')
                                ->live(onBlur: true),

                            Select::make('subtaskStatus')
                                ->required(fn(Get $get): bool => !empty(trim($get('subTaskTitle'))))
                                ->label('Status')
                                ->options(TaskStatus::toSelectArray())

                        ))->columns(3)->reorderable(false)->addActionLabel('Add more')
                ))
                ->action(function (array $data, Task $record): void {
                    $record->updateSubTask($record, $data['subtask']);
                }),
        );
    }

}
