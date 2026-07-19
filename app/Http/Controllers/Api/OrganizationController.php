<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteMemberRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        return OrganizationResource::collection($request->user()->organizations);
    }

    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);

        return new OrganizationResource($organization->load('members'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $organization->update($validated);

        return new OrganizationResource($organization);
    }

    public function invite(InviteMemberRequest $request, Organization $organization)
    {
        $this->authorize('invite', $organization);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No user found with that email. They need to register first.',
            ], 404);
        }

        if ($organization->members()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is already a member of this organization.',
            ], 422);
        }

        $organization->members()->attach($user->id, ['role' => $request->role]);

        return response()->json(['message' => 'Member invited successfully.'], 201);
    }
}