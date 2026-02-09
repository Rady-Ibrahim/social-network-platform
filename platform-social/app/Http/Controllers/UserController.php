<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(User $user): View
    {
        $friendRequestFromThem = null;
        $friendRequestFromMe = null;
        $areFriends = false;

        if (Auth::check()) {
            $me = Auth::id();
            $other = $user->id;
            $userOne = min($me, $other);
            $userTwo = max($me, $other);

            $requests = FriendRequest::where('user_one_id', $userOne)
                ->where('user_two_id', $userTwo)
                ->get();

            foreach ($requests as $r) {
                if ($r->status === FriendRequest::STATUS_ACCEPTED) {
                    $areFriends = true;
                    break;
                }
                if ($r->status === FriendRequest::STATUS_PENDING) {
                    if ((int) $r->receiver_id === (int) $me) {
                        $friendRequestFromThem = $r;
                    } else {
                        $friendRequestFromMe = $r;
                    }
                }
            }
        }

        return view('users.show', [
            'user' => $user,
            'friendRequestFromThem' => $friendRequestFromThem,
            'friendRequestFromMe' => $friendRequestFromMe,
            'areFriends' => $areFriends,
        ]);
    }

    /**
     * Search for users (for web UI, using session auth).
     */
    public function search(Request $request): JsonResponse
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
}
