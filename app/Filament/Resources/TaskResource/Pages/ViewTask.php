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

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editTask')
                ->hidden(fn(Task $record): bool => $record->status == TaskStatus::DONE)
                ->label('Edit')
                ->icon('heroicon-o-pencil-square'),

            Action::make('manageSubtask')
                ->hidden(fn(Task $record): bool => $record->status == TaskStatus::DONE)
                ->label('Edit Subtask')
                ->icon('heroicon-o-document-text')
                ->modalFooterActionsAlignment(Alignment::End)
                ->stickyModalHeader()
                ->fillForm(fn(Task $record): array => [
                    'subtask' => $record->subtask,
                ])
                ->form([
                    Repeater::make('subtask')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('subTaskTitle')
                                ->required()
                                ->columnSpan(2)
                                ->label('Title')
                                ->live(onBlur: true),

                            Select::make('subtaskStatus')
                                ->required(fn(Get $get): bool => !empty(trim($get('subTaskTitle'))))
                                ->label('Status')
                                ->options(TaskStatus::toSelectArray())

                        ])->columns(3)->orderColumn(false)->addActionLabel('Add more')
                ])
                ->action(function (array $data, Task $record): void {
                    $record->updateSubTask($record, $data['subtask']);
                }),
        ];
    }

}
