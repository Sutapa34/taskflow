<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('allows an organization member to create and view a project', function () {
    $user = User::factory()->create();
    $organization = Organization::create([
        'name' => 'Acme Inc',
        'slug' => 'acme-inc',
        'owner_id' => $user->id,
    ]);
    $organization->members()->attach($user->id, ['role' => 'owner']);

    $response = actingAs($user, 'sanctum')
        ->postJson("/api/organizations/{$organization->id}/projects", [
            'name' => 'Website Redesign',
            'description' => 'Q3 refresh',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Website Redesign');

    $project = Project::first();

    actingAs($user, 'sanctum')
        ->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $project->id);
});

it('denies a non-member from creating or viewing a project', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $organization = Organization::create([
        'name' => 'Private Co',
        'slug' => 'private-co',
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);

    actingAs($outsider, 'sanctum')
        ->postJson("/api/organizations/{$organization->id}/projects", ['name' => 'Sneaky Project'])
        ->assertForbidden();

    $project = Project::create([
        'organization_id' => $organization->id,
        'name' => 'Internal Roadmap',
        'created_by' => $owner->id,
    ]);

    actingAs($outsider, 'sanctum')
        ->getJson("/api/projects/{$project->id}")
        ->assertForbidden();
});

it('only allows an owner or admin to delete a project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $organization = Organization::create([
        'name' => 'Delete Test Org',
        'slug' => 'delete-test-org',
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);
    $organization->members()->attach($member->id, ['role' => 'member']);

    $project = Project::create([
        'organization_id' => $organization->id,
        'name' => 'Doomed Project',
        'created_by' => $owner->id,
    ]);

    actingAs($member, 'sanctum')
        ->deleteJson("/api/projects/{$project->id}")
        ->assertForbidden();

    actingAs($owner, 'sanctum')
        ->deleteJson("/api/projects/{$project->id}")
        ->assertNoContent();
});
