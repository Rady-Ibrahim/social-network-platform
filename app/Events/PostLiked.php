<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Post $post,
        public int $likerId,
        public string $likerName,
    ) {
        $this->post->loadMissing('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->post->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PostLiked';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->post->id,
            'owner_id' => $this->post->user_id,
            'liker_id' => $this->likerId,
            'liker_name' => $this->likerName,
            'excerpt' => str($this->post->content ?? '')->limit(80),
            'created_at' => now()->toIso8601String(),
            'type' => 'like',
        ];
    }
}

