<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user, Organization $organization): bool
    {
        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user, Organization $organization): bool
    {
        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        /** @var Organization $organization */
        $organization = $project->organization;

        return $organization->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();
    }
}
