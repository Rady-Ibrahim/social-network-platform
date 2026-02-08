<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return (int) $comment->user_id === (int) $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ((int) $comment->user_id === (int) $user->id) {
            return true;
        }

        return (int) $comment->post->user_id === (int) $user->id;
    }
}
