<?php

namespace App\Filament\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\TaskResource;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Handlers;

class UpdateHandler extends Handlers
{
    public static string|null $uri = '/{id}';
    public static string|null $resource = TaskResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public function handler(UpdateTaskRequest $request)
    {
        $id = $request->route('id');
        $authId = Auth::id();
        $model = static::getModel()::where('user_id', $authId)->find($id);

        if (!$model) {
            return static::sendNotFoundResponse("Task not found");
        }

        $validated = $request->validated();
        
        $model->fill($validated);

        $model->save();

        $transformer = static::getApiTransformer();
        $transFormData = new $transformer($model);

        return static::sendSuccessResponse($transFormData, "Successfully Updated the task");
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }
}
