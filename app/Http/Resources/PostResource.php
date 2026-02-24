<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count ?? $this->whenCounted('likes')),
            'is_liked_by_me' => (bool) ($this->is_liked_by_me ?? false),
        ];
    }
}
