<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'organization' => $this->whenLoaded('organization', fn ($organization) => $organization->toArray($request)),
            'user' => $this->whenLoaded('user', fn ($user) => $user->toArray($request)),
        ];
    }
}
