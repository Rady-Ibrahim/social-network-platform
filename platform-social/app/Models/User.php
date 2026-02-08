<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar)
            : null;
    }

    public function friendRequestsSent(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    public function friendRequestsReceived(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    /**
     * Friend request records where this user is involved and status is accepted.
     */
    public function acceptedFriendRequests()
    {
        return FriendRequest::where('status', FriendRequest::STATUS_ACCEPTED)
            ->where(function ($q) {
                $q->where('sender_id', $this->id)->orWhere('receiver_id', $this->id);
            });
    }

    /**
     * IDs of users who are friends with this user (bidirectional).
     */
    public function friendIds(): array
    {
        return $this->acceptedFriendRequests()
            ->get()
            ->map(fn (FriendRequest $r) => $r->sender_id === $this->id ? $r->receiver_id : $r->sender_id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * User models that are friends with this user (bidirectional).
     */
    public function friends()
    {
        $ids = $this->friendIds();

        return empty($ids) ? static::whereRaw('0 = 1') : static::whereIn('id', $ids);
    }

    public function pendingFriendRequestsReceived()
    {
        return $this->friendRequestsReceived()->where('status', FriendRequest::STATUS_PENDING);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
