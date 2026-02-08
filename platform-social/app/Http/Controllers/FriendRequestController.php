<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FriendRequestController extends Controller
{
    public function index(Request $request): View
    {
        $pending = $request->user()
            ->pendingFriendRequestsReceived()
            ->with('sender')
            ->latest()
            ->get();

        $friends = $request->user()->friends()->get();

        return view('friends.index', [
            'pendingRequests' => $pending,
            'friends' => $friends,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $receiverId = (int) $request->receiver_id;
        $senderId = $request->user()->id;

        if ($receiverId === $senderId) {
            return back()->withErrors(['receiver_id' => __('You cannot send a friend request to yourself.')]);
        }

        $userOne = min($senderId, $receiverId);
        $userTwo = max($senderId, $receiverId);

        $exists = FriendRequest::where('user_one_id', $userOne)
            ->where('user_two_id', $userTwo)
            ->whereIn('status', [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])
            ->exists();

        if ($exists) {
            return back()->withErrors(['receiver_id' => __('A friend request already exists or you are already friends.')]);
        }

        FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => FriendRequest::STATUS_PENDING,
        ]);

        return back()->with('status', 'friend-request-sent');
    }

    public function accept(Request $request, FriendRequest $friendRequest): RedirectResponse
    {
        $this->authorize('accept', $friendRequest);

        $friendRequest->update(['status' => FriendRequest::STATUS_ACCEPTED]);

        return back()->with('status', 'friend-request-accepted');
    }

    public function reject(Request $request, FriendRequest $friendRequest): RedirectResponse
    {
        $this->authorize('reject', $friendRequest);

        $friendRequest->update(['status' => FriendRequest::STATUS_REJECTED]);

        return back()->with('status', 'friend-request-rejected');
    }

    public function destroy(Request $request, FriendRequest $friendRequest): RedirectResponse
    {
        $this->authorize('cancel', $friendRequest);

        $friendRequest->delete();

        return back()->with('status', 'friend-request-cancelled');
    }
}
