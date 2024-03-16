<?php

namespace App\Filament\Resources;

use App\Enums\PublishStatus;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Policies\TaskPolicy;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->autofocus()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->minLength(5)
                            ->maxLength(100),

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
                ])


            ])->columns(3);
    }


    /**
     * @param  Table  $table  *
     * @return Table
     * @throws Exception
     *
     * Render the Table Tasks
     */
    public static function table(Table $table): Table
    {
        return $table
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

                TextColumn::make('user.name')
                    ->label('Created by'),

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
                TrashedFilter::make()->label('Trashed tasks'),
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
                        ->hidden(function (Task $record) {
                            return $record->status == TaskStatus::IN_PROGRESS ||
                                $record->status == TaskStatus::DONE ||
                                !app(TaskPolicy::class)->markInProgress(auth()->user(), $record);
                        })
                        ->icon('heroicon-o-clock')
                        ->color('blue')
                        ->label('Mark as in-progress')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-clock')
                        ->modalDescription()
                        ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::IN_PROGRESS)),

                    Action::make('markDone')
                        ->hidden(function (Task $record) {
                            return $record->status == TaskStatus::DONE ||
                                !app(TaskPolicy::class)->markDone(auth()->user(),
                                    $record);
                        })
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('success')
                        ->label('Mark as done')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-clipboard-document-check')
                        ->modalDescription()
                        ->action(fn(Task $record) => $record->setTaskStatus($record, TaskStatus::DONE)),

                    DeleteAction::make()->label('Move to trash'),
                    RestoreAction::make(),

                ]),
            ])
            ->recordUrl(false);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
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


                    ])->columns(4)
            ])->columns(3);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            //'create' => Pages\CreateTask::route('/create'),
            //'edit' => Pages\EditTask::route('/{record}/edit'),
            //'view' => Pages\ViewTask::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
