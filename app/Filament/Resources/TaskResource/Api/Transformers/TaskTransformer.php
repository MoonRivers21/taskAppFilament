<?php

namespace App\Filament\Resources\TaskResource\Api\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskTransformer extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'taskId' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->image,
            'userId' => $this->user_id,
            'status' => $this->status,
            'published' => $this->published,
            'publishedAt' => $this->published_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->deleted_at
        ];
    }
}
