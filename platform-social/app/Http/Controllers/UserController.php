<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
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

        if (auth()->check()) {
            $me = auth()->id();
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
}
