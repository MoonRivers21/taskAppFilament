<?php

namespace App\Filament\Resources;

use App\Enums\PublishStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource\Api\Transformers\TaskTransformer;
use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers\TasksRelationManager;
use App\Models\Task;
use App\Rules\CheckUniqueTaskTitle;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @param  Table  $table  where user_id = current login User
     * @return Table
     * @throws Exception
     *
     * Render the Table Tasks
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->whereOwnTasks($query))
            ->paginated([10, 25, 50, 100])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->formatStateUsing(fn($state) => $state.'.')
                    ->rowIndex(),

                ImageColumn::make('image')
                    ->alignCenter()
                    ->defaultImageUrl(url('images/default.png')),

                TextColumn::make('title')
                    ->sortable()
                    ->words('5')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->tooltip(fn($state) => TaskStatus::fromValue($state)->description(trim($state)))
                    ->icon(fn($state) => TaskStatus::fromValue($state)->icon(trim($state)))
                    ->color(fn($state) => TaskStatus::fromValue($state)->color(trim($state))),

                ToggleColumn::make('published')
                    ->afterStateUpdated(function ($record, $state) {
                        $record->handleTogglePublish($record, $state);
                    })
                    ->label('Draft/Published')
                    ->alignCenter()
                    ->disabled(fn(Task $record): bool => $record->handleTogglePublished($record))
                    ->tooltip(fn($state) => PublishStatus::fromValue($state)->descriptionHint($state))
                    ->offIcon(fn($state) => PublishStatus::fromValue($state)->icon($state))
                    ->onIcon(fn($state) => PublishStatus::fromValue($state)->icon($state)),

                TextColumn::make('created_at')
                    ->label('Created date')
                    ->dateTime('M d, Y, h:iA')
                    ->sortable(),


            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TaskStatus::toSelectArray()),
                TrashedFilter::make()->label('Trashed tasks')

            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->stickyModalHeader()
                        ->modalFooterActionsAlignment(Alignment::End)
                        ->modalHeading('Task Information'),

                    EditAction::make()
                        ->hidden(fn(Task $record) => $record->status == TaskStatus::DONE),

                    Action::make('markTodo')
                        ->hidden(fn(Task $record): bool => $record->handlePolicyMarkAsTodo($record))
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('warning')
                        ->label('Mark as to-do')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-clipboard-document-list')
                        ->modalDescription()
                        ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::TODO)),

                    Action::make('markInProgress')
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
                        ->label('Mark as done')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-clipboard-document-check')
                        ->modalDescription()
                        ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::DONE)),

                    Action::make('editSubTask')
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

                                ])->columns(3)->orderColumn(false)->addActionLabel('Add more')
                        ])
                        ->action(function (array $data, Task $record): void {
                            $record->updateSubTask($record, $data['subtask']);
                        }),


                    RestoreAction::make(),

                ])
                    ->size(ActionSize::Small)
                    ->icon('heroicon-o-list-bullet')
                    ->tooltip('Show more actions')
                    ->extraAttributes([
                        'class' => 'border'
                    ]),


                DeleteAction::make()
                    ->size(ActionSize::Small)
                    ->hiddenLabel()
                    ->iconButton()
                    ->extraAttributes(['class' => 'border ml-3'])
                    ->modalHeading('Move to trash')
                    ->modalDescription('Are you  sure you  want to move the task to trash?')
                    ->tooltip('Move to trash'),


            ])
            ->recordUrl('');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->autofocus()
                            ->required()
                            ->maxLength(100)
                            ->minLength(3)
                            ->rule(function ($record) {
                                // Check if $record is an instance of Task or null (for new tasks)
                                if ($record) {
                                    return new CheckUniqueTaskTitle($record->id, $record->title);
                                }
                                // For new tasks, return the CheckUniqueTaskTitle rule without arguments
                                return new CheckUniqueTaskTitle(null, null);
                            }),

                        Textarea::make('content')
                            ->rows(10)
                            ->required(),


                    ])->columnSpan(2),

                \Filament\Forms\Components\Group::make([
                    Section::make()
                        ->schema([
                            FileUpload::make('image')
                                ->label('Task Image')
                                ->hint('(Optional)')
                                ->image()
                                ->maxSize('4000')
                                ->directory('task-image'),

                            Select::make('status')
                                ->required()
                                ->options(TaskStatus::toSelectArray())
                                ->default(TaskStatus::TODO),

                        ])->columnSpan(1),

                    \Filament\Forms\Components\Fieldset::make('')
                        ->schema([
                            Toggle::make('published')
                                ->default(1)
                                ->offIcon('heroicon-s-document')
                                ->onIcon('heroicon-s-document-check'),
                        ])->columns(1)
                ]),

                Section::make()
                    ->schema([
                        Repeater::make('subtask')
                            ->schema([
                                TextInput::make('subTaskTitle')->columnSpan(2)
                                    ->label('Title')
                                    ->live(onBlur: true),

                                Select::make('subtaskStatus')
                                    ->required(fn(Get $get
                                    ): bool => !empty(trim($get('subTaskTitle'))))
                                    ->label('Status')
                                    ->options(TaskStatus::toSelectArray())
                            ])->columns(3)->orderColumn(false)->addActionLabel('Add more')
                    ])->columnSpan(2)


            ])->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make()
                    ->schema([
                        Group::make([
                            TextEntry::make('title'),

                            TextEntry::make('status')
                                ->badge()
                                ->icon(fn($state) => TaskStatus::fromValue($state)->icon(trim($state)))
                                ->color(fn($state) => TaskStatus::fromValue($state)->color(trim($state)))
                                ->columnSpan(1),
                        ])->columnSpan(2),

                        ImageEntry::make('image')
                            ->hiddenLabel()
                            ->alignCenter()
                            ->defaultImageUrl(url('images/default.png'))
                            ->columnSpan(1),

                        TextEntry::make('content')
                            ->columnSpanFull(),

                        Fieldset::make('Other Info')
                            ->schema([
                                TextEntry::make('published')
                                    ->default('-')
                                    ->size(TextEntrySize::ExtraSmall)
                                    ->label('Publish Status')
                                    ->formatStateUsing(fn($state) => PublishStatus::fromValue($state)->label($state))
                                    ->color(fn($state) => PublishStatus::fromValue($state)->color($state))
                                    ->iconColor(fn($state) => PublishStatus::fromValue($state)->color($state))
                                    ->icon(fn($state) => PublishStatus::fromValue($state)->icon($state)),

                                TextEntry::make('published_at')
                                    ->size(TextEntrySize::ExtraSmall)
                                    ->label('Published date')
                                    ->dateTime('M d, Y - h:iA'),

                                TextEntry::make('created_at')
                                    ->size(TextEntrySize::ExtraSmall)
                                    ->label('Created date')
                                    ->dateTime('M d, Y - h:iA'),

                                TextEntry::make('deleted_at')
                                    ->size(TextEntrySize::ExtraSmall)
                                    ->label('Moved to trash date')
                                    ->icon('heroicon-o-trash')
                                    ->dateTime('M d, Y - h:iA'),


                            ])->columns(4),

                        \Filament\Infolists\Components\Section::make('Subtask')
                            ->icon('heroicon-o-document-duplicate')
                            ->collapsible()
                            ->schema([
                                RepeatableEntry::make('subtask')
                                    ->hintAction(
                                        \Filament\Infolists\Components\Actions\Action::make('manageSubTask')
                                            ->iconButton()
                                            ->icon('heroicon-o-document-text')
                                    )
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('subTaskTitle')
                                            ->columnSpan(2)
                                            ->default('-'),

                                        TextEntry::make('subtaskStatus')
                                            ->badge()
                                            ->icon(fn($state) => TaskStatus::fromValue($state)->icon(trim($state)))
                                            ->color(fn($state) => TaskStatus::fromValue($state)->color(trim($state)))
                                            ->label('Status'),

                                    ])->columns(4)->columnSpanFull()
                            ])
                    ])->columns(3)
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getApiTransformer()
    {
        return TaskTransformer::class;
    }
}
