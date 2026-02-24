<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Comment $comment,
    ) {
        $this->comment->loadMissing('user', 'post.user');
    }

    public function broadcastOn(): array
    {
        $postOwnerId = $this->comment->post?->user_id;

        return $postOwnerId
            ? [new PrivateChannel('users.' . $postOwnerId)]
            : [];
    }

    public function broadcastAs(): string
    {
        return 'CommentCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'author_id' => $this->comment->user_id,
            'author_name' => $this->comment->user?->name,
            'author_avatar' => $this->comment->user?->avatarUrl(),
            'excerpt' => str($this->comment->body)->limit(80),
            'created_at' => $this->comment->created_at?->toIso8601String(),
            'type' => 'comment',
        ];
    }
}

