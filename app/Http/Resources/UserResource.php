<?php

namespace App\Http\Resources;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'organizations' => $this->whenLoaded('organizations', function () {
                return $this->organizations->map(function (Model $model) {
                    /** @var Organization $org */
                    $org = $model;

                    return [
                        'id' => $org->id,
                        'name' => $org->name,
                        'slug' => $org->slug,
                        // @phpstan-ignore-next-line property.notFound
                        'role' => $org->pivot->role,
                    ];
                });
            }),
        ];
    }
}
