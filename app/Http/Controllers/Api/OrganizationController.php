<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;

final class OrganizationController extends Controller
{
    /**
     * Store a new organization
     */
    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action)
    {
        $organization = $action->execute($request->validated());

        return $organization->toResource();
    }
}
