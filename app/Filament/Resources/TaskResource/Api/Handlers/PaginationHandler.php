<?php

namespace App\Filament\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static string|null $uri = '/';
    public static string|null $resource = TaskResource::class;


    public function handler()
    {
        $model = static::getEloquentQuery();
        $authId = Auth::id();
        

        $query = QueryBuilder::for($model)
            ->where('user_id', $authId)
            ->allowedFields($model::$allowedFields ?? [])
            ->allowedSorts(['title', 'created_at'])
            ->allowedFilters($model::$allowedFilters ?? [])
            ->allowedIncludes($model::$allowedIncludes ?? null)
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}
