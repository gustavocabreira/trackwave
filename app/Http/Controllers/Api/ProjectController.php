<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\IndexProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

final class ProjectController extends Controller
{
    public function index(Organization $organization, IndexProjectRequest $request): JsonResource
    {
        $this->authorize('viewAny', [Project::class, $organization]);

        $projects = $organization
            ->projects()
            ->with('user')
            ->with('organization')
            ->when($request->has('name'), function ($query) {
                $name = request()->string('name');
                $query->where('name', 'like', "%{$name}%");
            })
            ->paginate($request->integer('per_page', 10));

        return ProjectResource::collection($projects);
    }

    public function store(Organization $organization, StoreProjectRequest $request): JsonResource
    {
        $this->authorize('create', [Project::class, $organization]);

        $project = $organization->projects()->create([
            ...$request->validated(),
            'user_id' => auth()->user()->id,
            'organization_id' => $organization->id,
        ]);

        return new ProjectResource($project);
    }

    public function show(Project $project)
    {
        $this->authorize('view', [Project::class, $project]);

        $project->load('organization', 'user');

        return new ProjectResource($project);
    }

    public function update(Project $project, UpdateProjectRequest $request): JsonResource
    {
        $this->authorize('update', [Project::class, $project]);

        $project->load('user');
        $project->update($request->validated());

        return new ProjectResource($project);
    }

    public function destroy(Project $project): Response
    {
        $this->authorize('delete', [Project::class, $project]);

        $project->delete();

        return response()->noContent();
    }
}
