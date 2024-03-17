<?php

namespace App\Filament\Resources\TaskResource\Api;

use App\Filament\Resources\TaskResource;
use Rupadana\ApiService\ApiService;


class TaskApiService extends ApiService
{
    protected static string|null $resource = TaskResource::class;

    public static function handlers(): array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
