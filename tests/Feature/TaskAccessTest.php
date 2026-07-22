<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;

function createOrgWithProject(User $owner): Project
{
    $organization = Organization::create([
        'name' => 'Task Test Org '.uniqid(),
        'slug' => 'task-test-org-'.uniqid(),
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);

    return Project::create([
        'organization_id' => $organization->id,
        'name' => 'Task Test Project',
        'created_by' => $owner->id,
    ]);
}

it('allows an organization member to create, list, and update a task', function () {
    $user = User::factory()->create();
    $project = createOrgWithProject($user);

    $create = actingAs($user, 'sanctum')
        ->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Build login form',
            'status' => 'todo',
        ]);

    $create->assertCreated()->assertJsonPath('data.status', 'todo');

    $task = Task::first();

    actingAs($user, 'sanctum')
        ->getJson("/api/projects/{$project->id}/tasks?filter[status]=todo")
        ->assertOk()
        ->assertJsonCount(1, 'data');

    actingAs($user, 'sanctum')
        ->patchJson("/api/tasks/{$task->id}", ['status' => 'in_progress'])
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');
});

it('denies a non-member from creating or viewing tasks', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = createOrgWithProject($owner);

    actingAs($outsider, 'sanctum')
        ->postJson("/api/projects/{$project->id}/tasks", ['title' => 'Sneaky task'])
        ->assertForbidden();

    $task = $project->tasks()->create([
        'title' => 'Owner task',
        'status' => 'backlog',
        'created_by' => $owner->id,
    ]);

    actingAs($outsider, 'sanctum')
        ->getJson("/api/tasks/{$task->id}")
        ->assertForbidden();
});

it('allows the task creator to delete it, but denies other plain members', function () {
    $owner = User::factory()->create();
    $creator = User::factory()->create();
    $project = createOrgWithProject($owner);
    $project->organization->members()->attach($creator->id, ['role' => 'member']);

    $task = $project->tasks()->create([
        'title' => 'Member-created task',
        'status' => 'backlog',
        'created_by' => $creator->id,
    ]);

    $otherMember = User::factory()->create();
    $project->organization->members()->attach($otherMember->id, ['role' => 'member']);

    actingAs($otherMember, 'sanctum')
        ->deleteJson("/api/tasks/{$task->id}")
        ->assertForbidden();

    actingAs($creator, 'sanctum')
        ->deleteJson("/api/tasks/{$task->id}")
        ->assertNoContent();
});
