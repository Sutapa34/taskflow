<?php

use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('allows a member to view their organization', function () {
    $user = User::factory()->create();
    $organization = Organization::create([
        'name' => 'Member Org',
        'slug' => 'member-org',
        'owner_id' => $user->id,
    ]);
    $organization->members()->attach($user->id, ['role' => 'owner']);

    actingAs($user, 'sanctum')
        ->getJson("/api/organizations/{$organization->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $organization->id);
});

it('denies a non-member from viewing an organization', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $organization = Organization::create([
        'name' => 'Private Org',
        'slug' => 'private-org',
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);

    actingAs($outsider, 'sanctum')
        ->getJson("/api/organizations/{$organization->id}")
        ->assertForbidden();
});

it('denies a member from inviting others, but allows an owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    User::factory()->create(['email' => 'invitee@example.com']);

    $organization = Organization::create([
        'name' => 'Invite Org',
        'slug' => 'invite-org',
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);
    $organization->members()->attach($member->id, ['role' => 'member']);

    actingAs($member, 'sanctum')
        ->postJson("/api/organizations/{$organization->id}/invite", [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])
        ->assertForbidden();

    actingAs($owner, 'sanctum')
        ->postJson("/api/organizations/{$organization->id}/invite", [
            'email' => 'invitee@example.com',
            'role' => 'member',
        ])
        ->assertCreated();
});
