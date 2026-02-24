<?php

namespace App\Policies;

use App\Models\FriendRequest;
use App\Models\User;

class FriendRequestPolicy
{
    public function accept(User $user, FriendRequest $friendRequest): bool
    {
        return (int) $friendRequest->receiver_id === (int) $user->id;
    }

    public function reject(User $user, FriendRequest $friendRequest): bool
    {
        return (int) $friendRequest->receiver_id === (int) $user->id;
    }

    public function cancel(User $user, FriendRequest $friendRequest): bool
    {
        return (int) $friendRequest->sender_id === (int) $user->id;
    }

    public function delete(User $user, FriendRequest $friendRequest): bool
    {
        return $this->cancel($user, $friendRequest);
    }
}
