<?php

namespace App\Filament\Resources\TaskResource\Api\Handlers;

use App\Filament\Resources\TaskResource;
use App\Http\Requests\CreateTaskRequest;
use Illuminate\Support\Facades\Auth;
use Rupadana\ApiService\Http\Handlers;

class CreateHandler extends Handlers
{
    public static string|null $uri = '/';
    public static string|null $resource = TaskResource::class;


    public static function getMethod()
    {
        return Handlers::POST;
    }


    public function handler(CreateTaskRequest $request)
    {
        //Get the method "POST"
        $model = new (static::getModel());

        //Valid User
        $authId = Auth::id();

        //Checking the authentication if exists
        if ($authId && $authId > 0) {

            $validated = $request->validated();
            $model->fill($validated);
            $model->save();
            return static::sendSuccessResponse($model, "Successfully created task");

        }
        return static::sendNotFoundResponse('Unauthorized action.');

    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }
}
