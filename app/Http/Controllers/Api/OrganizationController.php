<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\IndexOrganizationRequest;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;

final class OrganizationController extends Controller
{
    /**
     * Get a list of the user organizations
     */
    public function index(IndexOrganizationRequest $request)
    {
        $organizations = auth()
            ->user()
            ->organizations()
            ->when($request->has('name'), function ($query) {
                $name = request()->string('name');
                $query->where('name', 'like', "%{$name}%");
            })
            ->with('owner')
            ->paginate($request->integer('per_page', 10));

        return OrganizationResource::collection($organizations);
    }

    /**
     * Store a new organization
     */
    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action)
    {
        $organization = $action->execute($request->validated());

        return new OrganizationResource($organization);
    }

    /**
     * Show a organization
     */
    public function show(Organization $organization)
    {
        $organization->load('owner');
        return new OrganizationResource($organization);
    }
}
