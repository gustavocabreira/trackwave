<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Models\Organization;

final class CreateOrganizationAction
{
    public function execute(array $organization): Organization
    {
        $slug = $organization['slug'] ?? null;

        if ($slug === null) {
            $slug = now()->timestamp.'-'.str()->slug($organization['name']);
        }

        $slug = str()->substr($slug, 0, 50);

        $organization = Organization::query()->create([
            'name' => $organization['name'],
            'slug' => $slug,
            'owner_id' => auth()->user()->id,
        ]);

        auth()->user()->organizations()->attach($organization);
        auth()->user()->update([
            'organization_id' => $organization->id,
        ]);

        return $organization;
    }
}
