<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $organization->members()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Organization $organization): bool
    {
        return $organization->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->where('users.id', $user->id)
            ->exists();
    }

    public function invite(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $organization->owner_id === $user->id;
    }
}