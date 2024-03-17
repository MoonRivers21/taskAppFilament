<?php

namespace App\Filament\Resources\TaskResource\Widgets;

use App\Enums\PublishStatus;
use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsTodoOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user()->getAuthIdentifier() ?? null;

        $countTodo = Task::query()
            ->where('status', TaskStatus::TODO)
            ->where('user_id', $user)
            ->where('published', PublishStatus::PUBLISHED)
            ->count();

        $countInprogress = Task::query()
            ->where('status', TaskStatus::IN_PROGRESS)
            ->where('user_id', $user)
            ->where('published', PublishStatus::PUBLISHED)
            ->count();

        $countDone = Task::query()
            ->where('status', TaskStatus::DONE)
            ->where('user_id', $user)
            ->where('published', PublishStatus::PUBLISHED)
            ->count();

        $countDraft = Task::query()
            ->where('user_id', $user)
            ->where('published', PublishStatus::DRAFT)
            ->count();

        $countPublished = Task::query()
            ->where('user_id', $user)
            ->where('published', PublishStatus::PUBLISHED)
            ->count();

        $countTrash = Task::onlyTrashed()
            ->where('user_id', $user)
            ->count();

        return [
            Stat::make('TO-DO', $countTodo)
                ->icon(TaskStatus::fromValue(TaskStatus::TODO)->icon(TaskStatus::TODO)),

            Stat::make('IN-PROGRESS', $countInprogress)
                ->icon(TaskStatus::fromValue(TaskStatus::IN_PROGRESS)->icon(TaskStatus::IN_PROGRESS)),

            Stat::make('DONE', $countDone)
                ->icon(TaskStatus::fromValue(TaskStatus::DONE)->icon(TaskStatus::DONE)),

            Stat::make('DRAFT', $countDraft)
                ->icon(PublishStatus::fromValue(PublishStatus::DRAFT)->icon(PublishStatus::DRAFT)),

            Stat::make('PUBLISHED', $countPublished)
                ->icon(PublishStatus::fromValue(PublishStatus::PUBLISHED)->icon(PublishStatus::PUBLISHED)),

            Stat::make('TRASH', $countTrash)
                ->icon('heroicon-o-trash'),


        ];
    }
}
