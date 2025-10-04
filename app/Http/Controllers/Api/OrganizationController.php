<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Models\Organization;

final class OrganizationController extends Controller
{
    public function store(StoreOrganizationRequest $request)
    {
        $organization = Organization::query()->create([
            'name' => $request->string('name'),
            'slug' => $request->string('slug'),
            'owner_id' => auth()->user()->id,
        ]);

        auth()->user()->organizations()->attach($organization);

        return $organization->toResource();
    }
}
