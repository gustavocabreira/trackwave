<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectController extends Controller
{
    public function store(Organization $organization, StoreProjectRequest $request): JsonResource
    {
        $project = $organization->projects()->create([
            ...$request->validated(),
            'user_id' => auth()->user()->id,
        ]);

        return new ProjectResource($project);
    }
}
