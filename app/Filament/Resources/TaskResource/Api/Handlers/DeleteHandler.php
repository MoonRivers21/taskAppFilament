<?php

namespace App\Filament\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Handlers;

class DeleteHandler extends Handlers
{
    public static string|null $uri = '/{id}';
    public static string|null $resource = TaskResource::class;

    public static function getMethod()
    {
        return Handlers::DELETE;
    }

    public function handler(Request $request)
    {
        $id = $request->route('id');
        $authId = Auth::id();

        $model = static::getModel()::where('user_id', $authId)->find($id);

        if (!$model) {
            return static::sendNotFoundResponse("Failed to delete, task not found");
        }

        $model->delete();

        return static::sendSuccessResponse($model, "Task has been successfully deleted");
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }
}
