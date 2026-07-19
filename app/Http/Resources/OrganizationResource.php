<?php

namespace App\Http\Resources;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Organization
 */
class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'owner_id' => $this->owner_id,
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function (Model $model) {
                    /** @var User $member */
                    $member = $model;

                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        // @phpstan-ignore-next-line property.notFound
                        'role' => $member->pivot->role,
                    ];
                });
            }),
        ];
    }
}
