<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = QueryBuilder::for($project->tasks())
            ->allowedFilters('status', AllowedFilter::exact('assignee_id'))
            ->allowedSorts('created_at', 'status')
            ->defaultSort('-created_at')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $project->tasks()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
