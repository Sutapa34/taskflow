<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        /** @var Project $project */
        $project = $task->project;

        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        /** @var Task $task */
        $task = $comment->task;

        /** @var Project $project */
        $project = $task->project;

        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();
    }
}
