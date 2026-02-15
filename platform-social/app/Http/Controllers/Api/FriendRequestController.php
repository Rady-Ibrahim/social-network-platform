<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFriendRequestRequest;
use App\Http\Resources\FriendRequestResource;
use App\Http\Resources\UserResource;
use App\Events\FriendRequestSent;
use App\Models\FriendRequest;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendRequestController extends Controller
{
    public function friends(Request $request): JsonResponse
    {
        $friends = $request->user()->friends()->get();

        return response()->json(UserResource::collection($friends));
    }

    public function index(Request $request): JsonResponse
    {
        $pending = $request->user()
            ->pendingFriendRequestsReceived()
            ->with('sender')
            ->latest()
            ->get();

        return response()->json(FriendRequestResource::collection($pending));
    }

    public function store(StoreFriendRequestRequest $request): JsonResponse
    {
        $receiverId = (int) $request->receiver_id;
        $senderId = $request->user()->id;

        if ($receiverId === $senderId) {
            return response()->json(['message' => 'You cannot send a friend request to yourself.'], 422);
        }

        $userOne = min($senderId, $receiverId);
        $userTwo = max($senderId, $receiverId);

        $existing = FriendRequest::where('user_one_id', $userOne)
            ->where('user_two_id', $userTwo)
            ->first();

        if ($existing) {
            if (in_array($existing->status, [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])) {
                return response()->json(['message' => 'A friend request already exists or you are already friends.'], 422);
            }

            $existing->update([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => FriendRequest::STATUS_PENDING,
            ]);
            $friendRequest = $existing->load(['sender', 'receiver']);

            // Store notification for receiver
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

            return response()->json(new FriendRequestResource($friendRequest), 201);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => FriendRequest::STATUS_PENDING,
        ]);
        $friendRequest->load(['sender', 'receiver']);

        // Store notification for receiver
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

        return response()->json(new FriendRequestResource($friendRequest), 201);
    }

    public function accept(Request $request, FriendRequest $friend_request): JsonResponse
    {
        $this->authorize('accept', $friend_request);
        $friend_request->update(['status' => FriendRequest::STATUS_ACCEPTED]);

        return response()->json(new FriendRequestResource($friend_request->load(['sender', 'receiver'])));
    }

    public function reject(Request $request, FriendRequest $friend_request): JsonResponse
    {
        $this->authorize('reject', $friend_request);
        $friend_request->update(['status' => FriendRequest::STATUS_REJECTED]);

        return response()->json(new FriendRequestResource($friend_request->load(['sender', 'receiver'])));
    }

    public function destroy(Request $request, FriendRequest $friend_request): JsonResponse
    {
        $this->authorize('cancel', $friend_request);
        $friend_request->delete();

        return response()->json(null, 204);
    }
}
