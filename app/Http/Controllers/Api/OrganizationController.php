<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;

final class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = auth()->user()->organizations()->with('owner')->paginate(10);
        return OrganizationResource::collection($organizations);
    }
    
    /**
     * Store a new organization
     */
    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action)
    {
        $organization = $action->execute($request->validated());

        return $organization->toResource();
    }
}
