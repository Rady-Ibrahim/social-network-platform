<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(8)->create();

        foreach ([0, 2, 4] as $i) {
            $a = $users[$i];
            $b = $users[$i + 1];
            FriendRequest::create([
                'sender_id' => $a->id,
                'receiver_id' => $b->id,
                'user_one_id' => min($a->id, $b->id),
                'user_two_id' => max($a->id, $b->id),
                'status' => FriendRequest::STATUS_ACCEPTED,
            ]);
        }

        $posts = collect();
        for ($i = 0; $i < 12; $i++) {
            $posts->push(Post::factory()->create(['user_id' => $users->random()->id]));
        }

        foreach ($posts->take(8) as $post) {
            Comment::create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
                'body' => fake()->sentence(),
            ]);
        }

        foreach ($posts->take(6) as $post) {
            $liker = $users->random();
            PostLike::firstOrCreate(
                ['post_id' => $post->id, 'user_id' => $liker->id]
            );
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
