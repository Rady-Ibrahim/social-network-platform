<?php

namespace App\Http\Controllers;

use App\Events\FriendRequestSent;
use App\Models\FriendRequest;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
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

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $receiverId = (int) $request->receiver_id;
        $senderId = $request->user()->id;

        if ($receiverId === $senderId) {
            if ($request->wantsJson()) {
                return response()->json(['message' => __('You cannot send a friend request to yourself.')], 422);
            }
            return back()->withErrors(['receiver_id' => __('You cannot send a friend request to yourself.')]);
        }

        $userOne = min($senderId, $receiverId);
        $userTwo = max($senderId, $receiverId);

        $exists = FriendRequest::where('user_one_id', $userOne)
            ->where('user_two_id', $userTwo)
            ->whereIn('status', [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) {
                return response()->json(['message' => __('A friend request already exists or you are already friends.')], 422);
            }
            return back()->withErrors(['receiver_id' => __('A friend request already exists or you are already friends.')]);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => FriendRequest::STATUS_PENDING,
        ]);

        // Store notification for receiver
        $friendRequest->loadMissing('sender', 'receiver');
        if ($friendRequest->receiver && $friendRequest->sender) {
            $friendRequest->receiver->notifications()->create([
                'type' => 'friend_request',
                'message' => $friendRequest->sender->name . ' sent you a friend request.',
                'data' => [
                    'friend_request_id' => $friendRequest->id,
                    'sender_id' => $friendRequest->sender_id,
                    'receiver_id' => $friendRequest->receiver_id,
                ],
            ]);
        }

        event(new FriendRequestSent($friendRequest));

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'sent',
                'friend_request_id' => $friendRequest->id,
            ], 201);
        }
        return back()->with('status', 'friend-request-sent');
    }

    public function accept(Request $request, FriendRequest $friendRequest): RedirectResponse|JsonResponse
    {
        $this->authorize('accept', $friendRequest);

        $friendRequest->update(['status' => FriendRequest::STATUS_ACCEPTED]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'accepted']);
        }
        return back()->with('status', 'friend-request-accepted');
    }

    public function reject(Request $request, FriendRequest $friendRequest): RedirectResponse|JsonResponse
    {
        $this->authorize('reject', $friendRequest);

        $friendRequest->update(['status' => FriendRequest::STATUS_REJECTED]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'rejected']);
        }
        return back()->with('status', 'friend-request-rejected');
    }

    public function destroy(Request $request, FriendRequest $friendRequest): RedirectResponse|JsonResponse
    {
        $this->authorize('cancel', $friendRequest);

        $friendRequest->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }
        return back()->with('status', 'friend-request-cancelled');
    }
}
