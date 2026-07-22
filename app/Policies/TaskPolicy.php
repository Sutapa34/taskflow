<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        /** @var Project $project */
        $project = $task->project;

        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user, Project $project): bool
    {
        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        /** @var Project $project */
        $project = $task->project;

        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        /** @var Project $project */
        $project = $task->project;

        /** @var Organization $organization */
        $organization = $project->organization;

        $isOwnerOrAdmin = $organization->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();

        return $isOwnerOrAdmin || $task->created_by === $user->id;
    }
}
