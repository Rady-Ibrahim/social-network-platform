<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Search users.
     *
     * Returns up to 20 users matching the given search term.
     *
     * @group Users
     *
     * @queryParam q string required The search term to look for in name or email. Example: john
     * @queryParam friends_only boolean When true, limit results to the authenticated user's friends. Example: true
     *
     * @response 200 scenario="success" {"data": [{"id": 1, "name": "John Doe", "email": "john@example.com"}]}
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:255'],
            'friends_only' => ['sometimes', 'boolean'],
        ]);

        $query = User::query();

        // If friends_only is true, restrict search to current user's friends
        if (!empty($validated['friends_only']) && $request->user()) {
            $friendIds = $request->user()->friendIds();
            if (!empty($friendIds)) {
                $query->whereIn('id', $friendIds);
            } else {
                // No friends, no results
                $query->whereRaw('0 = 1');
            }
        }

        $search = $validated['q'];

        $query->where(function ($q) use ($search) {
            $like = '%' . $search . '%';
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like);
        });

        $users = $query
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(UserResource::collection($users));
    }

    /**
     * Get a single user.
     *
     * Returns the public profile information for the given user.
     *
     * @group Users
     *
     * @urlParam user int required The ID of the user. Example: 1
     *
     * @response 200 scenario="success" {"data": {"id": 1, "name": "John Doe", "email": "john@example.com"}}
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user));
    }
}
