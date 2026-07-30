<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canManage = $request->user()?->id === $this->user_id;

        $task = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'subject' => $this->subject,
            'category_id' => $this->category_id,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date?->toDateString(),
            'due_time' => $this->due_time?->format('H:i'),
            'access' => $canManage ? 'owner' : 'shared',
            'can_manage' => $canManage,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($this->relationLoaded('user')) {
            $task['owner'] = new UserResource($this->user);
        }

        if ($this->relationLoaded('category') && $this->category !== null) {
            $task['category'] = new CategoryResource($this->category);
        }

        if ($canManage && $this->relationLoaded('sharedWith')) {
            $task['shared_with'] = UserResource::collection($this->sharedWith);
        }

        return $task;
    }
}
