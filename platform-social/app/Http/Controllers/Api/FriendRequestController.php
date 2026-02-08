<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFriendRequestRequest;
use App\Http\Resources\FriendRequestResource;
use App\Http\Resources\UserResource;
use App\Models\FriendRequest;
use App\Models\User;
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

        $exists = FriendRequest::where('user_one_id', $userOne)
            ->where('user_two_id', $userTwo)
            ->whereIn('status', [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'A friend request already exists or you are already friends.'], 422);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => FriendRequest::STATUS_PENDING,
        ]);
        $friendRequest->load(['sender', 'receiver']);

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
