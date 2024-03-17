<?php

namespace App\Filament\Resources\TaskResource\Widgets;

use App\Enums\PublishStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class TaskTodo extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                TaskResource::getEloquentQuery()
                    ->where('user_id', auth()->user()->getAuthIdentifier())
                    ->where('published', PublishStatus::PUBLISHED)

            )
            ->heading('Tasks ')
            ->paginationPageOptions(['5', '20', '100'])
            ->description('Task should published in order to  display here')
            ->headerActions([
                Action::make('create')
                    ->label('New task')
                    ->icon('heroicon-o-plus')
                    ->url(TaskResource\Pages\CreateTask::getUrl())
            ])
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->formatStateUsing(fn($state) => $state.'.')
                    ->rowIndex(),

                TextColumn::make('created_at')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->sortable()
                    ->words(8)
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->icon(fn($state) => TaskStatus::fromValue($state)->icon(trim($state)))
                    ->color(fn($state) => TaskStatus::fromValue($state)->color(trim($state))),


            ])
            ->actions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn(Model $record): string => route('filament.admin.resources.tasks.view',
                        ['record' => $record]))
                    ->openUrlInNewTab()
                    ->tooltip('View more details'),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit')
                    ->url(fn(Model $record): string => route('filament.admin.resources.tasks.edit',
                        ['record' => $record]))
                    ->hidden(fn(Task $record) => $record->status == TaskStatus::DONE),

                Action::make('markTodo')
                    ->iconButton()
                    ->tooltip('Mark as todo')
                    ->hidden(fn(Task $record): bool => $record->handlePolicyMarkAsTodo($record))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('warning')
                    ->label('Mark as to-do')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clipboard-document-list')
                    ->modalDescription()
                    ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::TODO)),

                Action::make('markInProgress')
                    ->iconButton()
                    ->tooltip('Mark as In-progress')
                    ->hidden(fn(Task $record): bool => $record->handlePolicyMarkInProgress($record))
                    ->icon('heroicon-o-clock')
                    ->color('blue')
                    ->label('Mark as in-progress')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clock')
                    ->modalDescription()
                    ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::IN_PROGRESS)),

                Action::make('markDone')
                    ->hidden(fn(Task $record): bool => $record->handlePolicyMarkDone($record))
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Mark as done')
                    ->label('Mark as done')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clipboard-document-check')
                    ->modalDescription()
                    ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::DONE)),

                Action::make('editSubTaskDash')
                    ->iconButton()
                    ->color('purple')
                    ->tooltip('Manage sub task')
                    ->hidden(fn(Task $record): bool => $record->status == TaskStatus::DONE)
                    ->label('Manage Subtask')
                    ->icon('heroicon-o-document-text')
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->closeModalByClickingAway(false)
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

                            ])->columns(3)->reorderable(false)->addActionLabel('Add subtask')->minItems(1)
                    ])
                    ->action(function (array $data, Task $record): void {
                        $record->updateSubTask($record, $data['subtask']);
                    }),

                DeleteAction::make()
                    ->size(ActionSize::Small)
                    ->hiddenLabel()
                    ->iconButton()
                    ->modalHeading('Move to trash')
                    ->modalDescription('Are you  sure you  want to move the task to trash?')
                    ->tooltip('Move to trash'),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->default(TaskStatus::TODO)
                    ->options(TaskStatus::toSelectArray()),
            ])
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create new')
                    ->icon('heroicon-o-plus')
                    ->url(TaskResource\Pages\CreateTask::getUrl())
            ])
            ->emptyStateHeading('No task?')
            ->emptyStateDescription('Create new to get started.');
    }
}
