<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Models\Organization;

final class UpdateOrganizationAction
{
    public function execute(Organization $organization, array $payload): Organization
    {
        $array = [];

        if (isset($payload['name'])) {
            $array['name'] = $payload['name'];
        }

        if (isset($payload['slug'])) {
            $slug = $payload['slug'];

            if ($slug === null) {
                $slug = now()->timestamp.'-'.str()->slug($payload['name']);
            }

            $slug = str()->substr($slug, 0, 50);

            $array['slug'] = $slug;
        }

        $organization->update($array);

        return $organization;
    }
}
