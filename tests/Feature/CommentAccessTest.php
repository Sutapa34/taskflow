<?php

use App\Models\Comment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

use function Pest\Laravel\actingAs;

function setUpOrgProjectTask(User $owner): Task
{
    $organization = Organization::create([
        'name' => 'Comment Test Org '.uniqid(),
        'slug' => 'comment-test-org-'.uniqid(),
        'owner_id' => $owner->id,
    ]);
    $organization->members()->attach($owner->id, ['role' => 'owner']);

    $project = Project::create([
        'organization_id' => $organization->id,
        'name' => 'Comment Test Project',
        'created_by' => $owner->id,
    ]);

    return $project->tasks()->create([
        'title' => 'Comment Test Task',
        'status' => 'backlog',
        'created_by' => $owner->id,
    ]);
}

it('allows an organization member to add a comment to a task', function () {
    $user = User::factory()->create();
    $task = setUpOrgProjectTask($user);

    $response = actingAs($user, 'sanctum')
        ->postJson("/api/tasks/{$task->id}/comments", ['body' => 'Looks good to me!']);

    $response->assertCreated()
        ->assertJsonPath('data.body', 'Looks good to me!')
        ->assertJsonPath('data.author.id', $user->id);

    actingAs($user, 'sanctum')
        ->getJson("/api/tasks/{$task->id}/comments")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('denies a non-member from commenting on or viewing a task comments', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $task = setUpOrgProjectTask($owner);

    actingAs($outsider, 'sanctum')
        ->postJson("/api/tasks/{$task->id}/comments", ['body' => 'Sneaky comment'])
        ->assertForbidden();

    actingAs($outsider, 'sanctum')
        ->getJson("/api/tasks/{$task->id}/comments")
        ->assertForbidden();
});

it('allows the author to delete their own comment, but denies other plain members', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $task = setUpOrgProjectTask($owner);
    $task->project->organization->members()->attach($author->id, ['role' => 'member']);

    $comment = Comment::create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'body' => 'My own comment',
    ]);

    $otherMember = User::factory()->create();
    $task->project->organization->members()->attach($otherMember->id, ['role' => 'member']);

    actingAs($otherMember, 'sanctum')
        ->deleteJson("/api/comments/{$comment->id}")
        ->assertForbidden();

    actingAs($author, 'sanctum')
        ->deleteJson("/api/comments/{$comment->id}")
        ->assertNoContent();
});

it('allows an owner to delete any comment in their organization', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $task = setUpOrgProjectTask($owner);
    $task->project->organization->members()->attach($member->id, ['role' => 'member']);

    $comment = Comment::create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'body' => 'Member comment',
    ]);

    actingAs($owner, 'sanctum')
        ->deleteJson("/api/comments/{$comment->id}")
        ->assertNoContent();
});
