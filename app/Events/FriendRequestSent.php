<?php

namespace App\Events;

use App\Models\FriendRequest;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public FriendRequest $friendRequest,
    ) {
        $this->friendRequest->loadMissing('sender', 'receiver');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->friendRequest->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'FriendRequestSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->friendRequest->id,
            'sender_id' => $this->friendRequest->sender_id,
            'receiver_id' => $this->friendRequest->receiver_id,
            'sender_name' => $this->friendRequest->sender?->name,
            'sender_avatar' => $this->friendRequest->sender?->avatarUrl(),
            'created_at' => $this->friendRequest->created_at?->toIso8601String(),
            'type' => 'friend_request',
        ];
    }
}

