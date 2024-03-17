<?php

namespace App\Filament\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class DetailHandler extends Handlers
{
    public static string|null $uri = '/{id}';
    public static string|null $resource = TaskResource::class;


    public function handler(Request $request)
    {
        $id = $request->route('id');
        $authId = Auth::id();

        $model = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $model->where(static::getKeyName(), $id)->where('user_id', $authId)
        )
            ->first();

        if (!$query) {
            return static::sendNotFoundResponse('Task not found.');
        }

        $transformer = static::getApiTransformer();

        return new $transformer($query);
    }
}
