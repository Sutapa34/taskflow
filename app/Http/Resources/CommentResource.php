<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'body' => $this->body,
            'author' => $this->whenLoaded('user', function () {
                /** @var User $user */
                $user = $this->user;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
