<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('viewAny', [Project::class, $organization]);

        return ProjectResource::collection($organization->projects()->latest()->get());
    }

    public function store(StoreProjectRequest $request, Organization $organization)
    {
        $this->authorize('create', [Project::class, $organization]);

        $project = $organization->projects()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return new ProjectResource($project);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
