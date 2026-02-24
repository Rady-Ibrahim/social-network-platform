# إجابات أسئلة المقابلة - Social Platform Project

هذا الملف يحتوي على إجابات نموذجية للأسئلة المحتملة في مقابلة تقنية حول المشروع.

---

## 1. أسئلة عن البنية والتصميم (Architecture & Design)

### Q1: لماذا استخدمت Laravel Sanctum بدلاً من Passport للـ API authentication؟

**الإجابة:**
- **Sanctum** مناسب للمشاريع الصغيرة والمتوسطة لأنه أخف وأبسط
- **Passport** يستخدم OAuth2 وهو معقد أكثر ويحتاج setup أكبر
- في هذا المشروع، نحتاج فقط API Token authentication، وليس OAuth2 كامل
- Sanctum يعطي tokens بسيطة ويدعم SPA authentication أيضاً
- الكود في `app/Http/Controllers/Api/AuthController.php` يستخدم `createToken('api')` من Sanctum

**الكود المرجعي:**
```php
// app/Http/Controllers/Api/AuthController.php
$token = $user->createToken('api')->plainTextToken;
```

---

### Q2: كيف تعمل علاقة الصداقة (Friends) في الكود؟ لماذا استخدمت `user_one_id` و `user_two_id` بدلاً من `sender_id` و `receiver_id` فقط؟

**الإجابة:**
- استخدمت **كلا الحقلين** معاً:
  - `sender_id` و `receiver_id`: لتحديد من أرسل الطلب ومن استلمه (directional)
  - `user_one_id` و `user_two_id`: لتسهيل البحث عن أي علاقة بين مستخدمين (bidirectional)
- في `FriendRequest` model، يوجد `booted()` method يملأ `user_one_id` و `user_two_id` تلقائياً:
  ```php
  static::saving(function (FriendRequest $request) {
      $request->user_one_id = min($request->sender_id, $request->receiver_id);
      $request->user_two_id = max($request->sender_id, $request->receiver_id);
  });
  ```
- هذا يجعل البحث أسهل: بدلاً من البحث في كلا الاتجاهين (`sender_id=1 AND receiver_id=2` OR `sender_id=2 AND receiver_id=1`)، نبحث مرة واحدة فقط
- في `FriendRequestController@store`، نستخدم `user_one_id` و `user_two_id` للتحقق من وجود طلب سابق

**الكود المرجعي:**
```php
// app/Http/Controllers/FriendRequestController.php:44-50
$userOne = min($senderId, $receiverId);
$userTwo = max($senderId, $receiverId);

$exists = FriendRequest::where('user_one_id', $userOne)
    ->where('user_two_id', $userTwo)
    ->whereIn('status', [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])
    ->exists();
```

---

### Q3: ما الفرق بين الـ Controllers في `app/Http/Controllers` والـ Controllers في `app/Http/Controllers/Api`؟

**الإجابة:**
- **Web Controllers** (`app/Http/Controllers`):
  - ترجع Views (Blade templates) أو Redirects
  - تستخدم `View` و `RedirectResponse`
  - مثال: `PostController@index` يرجع `view('posts.index')`
  
- **API Controllers** (`app/Http/Controllers/Api`):
  - ترجع JSON responses
  - تستخدم `JsonResponse` و API Resources
  - مثال: `Api\PostController@index` يرجع `PostResource::collection($posts)`

**الكود المرجعي:**
```php
// Web Controller
public function index(Request $request): View
{
    return view('posts.index', ['posts' => $posts]);
}

// API Controller
public function index(Request $request)
{
    $posts = Post::whereIn('user_id', $feedUserIds)->paginate(15);
    return PostResource::collection($posts);
}
```

---

### Q4: كيف تعمل الـ Policies في Laravel؟ وما الفرق بين `authorize()` و `@can` directive؟

**الإجابة:**
- **Policies** هي classes تحدد من يمكنه تنفيذ actions معينة على resources
- **`authorize()`** في Controller:
  - يرمي `AuthorizationException` إذا فشل التحقق
  - يستخدم في Controllers: `$this->authorize('update', $post)`
  
- **`@can` directive** في Blade:
  - يخفي/يظهر عناصر UI فقط
  - لا يمنع الوصول للـ route نفسه
  - مثال: `@can('update', $post) ... @endcan`

**الكود المرجعي:**
```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post): bool
{
    return (int) $post->user_id === (int) $user->id;
}

// app/Http/Controllers/PostController.php
public function update(Request $request, Post $post): RedirectResponse
{
    $this->authorize('update', $post); // يرمي exception إذا فشل
    // ...
}

// resources/views/posts/index.blade.php
@can('update', $post) // يخفي الزر فقط
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan
```

---

### Q5: لماذا استخدمت `Form Requests` في بعض الأماكن و `$request->validate()` في أماكن أخرى؟

**الإجابة:**
- **Form Requests** (`app/Http/Requests/Api/StorePostRequest.php`):
  - تستخدم للـ API endpoints
  - تعيد استخدام الكود بسهولة
  - يمكن إضافة logic معقدة للـ validation
  - مثال: `StorePostRequest`, `UpdatePostRequest`
  
- **`$request->validate()`** مباشرة:
  - تستخدم في Web Controllers للـ validation البسيط
  - أسرع للـ validation البسيطة
  - مثال: `CommentController@store` يستخدم `$request->validate()` مباشرة

**الكود المرجعي:**
```php
// Form Request (API)
// app/Http/Requests/Api/StorePostRequest.php
public function rules(): array
{
    return [
        'content' => ['required', 'string', 'max:5000'],
        'image' => ['nullable', 'image', 'max:2048'],
    ];
}

// Direct validation (Web)
// app/Http/Controllers/CommentController.php:15-17
$request->validate([
    'body' => ['required', 'string', 'max:2000'],
]);
```

---

## 2. أسئلة عن الأمان (Security)

### Q6: كيف تمنع المستخدم من إرسال طلب صداقة لنفسه؟ أين يتم التحقق من ذلك؟

**الإجابة:**
- التحقق يتم في `FriendRequestController@store` قبل إنشاء الطلب
- نتحقق من أن `receiver_id !== sender_id`
- إذا كانا متساويين، نرجع error message

**الكود المرجعي:**
```php
// app/Http/Controllers/FriendRequestController.php:40-42
if ($receiverId === $senderId) {
    return back()->withErrors(['receiver_id' => __('You cannot send a friend request to yourself.')]);
}
```

---

### Q7: في `FriendRequestController@store`، كيف تمنع إرسال طلب صداقة مكرر؟

**الإجابة:**
- نستخدم `user_one_id` و `user_two_id` للبحث عن أي طلب موجود بين نفس المستخدمين
- نتحقق من أن الـ status ليس `PENDING` أو `ACCEPTED`
- إذا وُجد طلب، نرجع error

**الكود المرجعي:**
```php
// app/Http/Controllers/FriendRequestController.php:44-54
$userOne = min($senderId, $receiverId);
$userTwo = max($senderId, $receiverId);

$exists = FriendRequest::where('user_one_id', $userOne)
    ->where('user_two_id', $userTwo)
    ->whereIn('status', [FriendRequest::STATUS_PENDING, FriendRequest::STATUS_ACCEPTED])
    ->exists();

if ($exists) {
    return back()->withErrors(['receiver_id' => __('A friend request already exists or you are already friends.')]);
}
```

---

### Q8: كيف تحمي الـ Private Channels في Broadcasting؟ أين يتم التحقق من الصلاحيات؟

**الإجابة:**
- التحقق يتم في `routes/channels.php`
- كل `PrivateChannel` يحتاج authorization callback
- الـ callback يتحقق من أن المستخدم المصادق هو نفسه صاحب القناة

**الكود المرجعي:**
```php
// routes/channels.php:17-19
Broadcast::channel('users.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

// app/Events/FriendRequestSent.php:24-26
public function broadcastOn(): array
{
    return [
        new PrivateChannel('users.' . $this->friendRequest->receiver_id),
    ];
}
```

---

### Q9: ما هي الثغرات الأمنية المحتملة في كود رفع الصور؟ كيف يمكن تحسينها؟

**الإجابة:**
**المشاكل المحتملة:**
1. لا يوجد تحقق من نوع الملف (MIME type) - فقط extension
2. لا يوجد resize/compression للصور الكبيرة
3. لا يوجد virus scanning
4. الملفات تُحفظ بنفس الاسم الأصلي (قد يسبب conflicts)

**التحسينات المقترحة:**
```php
// تحسين validation
'image' => [
    'nullable',
    'image',
    'mimes:jpeg,jpg,png,gif,webp',
    'max:2048',
    'dimensions:max_width=2000,max_height=2000'
],

// استخدام Intervention Image للـ resize
use Intervention\Image\Facades\Image;

if ($request->hasFile('image')) {
    $image = Image::make($request->file('image'))
        ->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })
        ->encode('jpg', 85);
    
    $filename = Str::uuid() . '.jpg';
    Storage::disk('public')->put("posts/{$filename}", $image);
}
```

**الكود الحالي:**
```php
// app/Http/Controllers/PostController.php:66-68
if ($request->hasFile('image')) {
    $imagePath = $request->file('image')->store('posts', 'public');
}
```

---

### Q10: لماذا تحذف التوكنات القديمة عند تسجيل الدخول في `AuthController@login`؟

**الإجابة:**
- للأمان: منع استخدام tokens قديمة إذا تم تسريبها
- لتقليل عدد tokens في قاعدة البيانات
- لضمان أن المستخدم لديه token واحد فقط نشط في كل مرة

**الكود المرجعي:**
```php
// app/Http/Controllers/Api/AuthController.php:52-54
$user = $request->user();
$user->tokens()->where('name', 'api')->delete(); // حذف القديمة
$token = $user->createToken('api')->plainTextToken; // إنشاء جديدة
```

---

### Q11: كيف تمنع المستخدم من تعديل/حذف منشورات أو تعليقات الآخرين؟

**الإجابة:**
- نستخدم **Policies** للتحقق من الملكية
- في Controller، نستخدم `$this->authorize()` قبل أي update/delete
- الـ Policy يتحقق من أن `user_id` للمنشور/التعليق = `id` المستخدم الحالي

**الكود المرجعي:**
```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post): bool
{
    return (int) $post->user_id === (int) $user->id;
}

// app/Http/Controllers/PostController.php:90
public function update(Request $request, Post $post): RedirectResponse
{
    $this->authorize('update', $post); // يرمي 403 إذا فشل
    // ...
}

// app/Policies/CommentPolicy.php:15-22
public function delete(User $user, Comment $comment): bool
{
    // يمكن للمالك أو صاحب المنشور الحذف
    if ((int) $comment->user_id === (int) $user->id) {
        return true;
    }
    return (int) $comment->post->user_id === (int) $user->id;
}
```

---

## 3. أسئلة عن الأداء (Performance)

### Q12: في `PostController@index`، كيف تحسن الأداء عند جلب المنشورات؟ ما هي الـ Eager Loading التي استخدمتها؟

**الإجابة:**
- استخدمت **Eager Loading** لتقليل عدد queries:
  - `with(['user', 'comments' => fn($q) => $q->latest()->limit(5)->with('user')])`
  - `withCount(['comments', 'likes'])`
  - `withExists(['likes as is_liked_by_me' => fn($q) => $q->where('user_id', $user->id)])`
- بدون Eager Loading: N+1 problem (query لكل post، query لكل user، query لكل comment)
- مع Eager Loading: 3-4 queries فقط لكل الصفحة

**الكود المرجعي:**
```php
// app/Http/Controllers/PostController.php:22-30
$posts = Post::whereIn('user_id', $feedUserIds)
    ->with([
        'user',
        'comments' => fn ($q) => $q->latest()->limit(5)->with('user'),
    ])
    ->withCount(['comments', 'likes'])
    ->withExists(['likes as is_liked_by_me' => fn ($q) => $q->where('user_id', $user->id)])
    ->latest()
    ->paginate(15);
```

---

### Q13: ما هي المشكلة المحتملة في `User@friends()` إذا كان للمستخدم آلاف الأصدقاء؟ كيف يمكن تحسينها؟

**الإجابة:**
**المشكلة:**
- `friendIds()` يجلب كل الـ friend requests في memory ثم يفلترها
- إذا كان للمستخدم 10,000 صديق، هذا يعني:
  - جلب 10,000 records في memory
  - عمل `map()` و `unique()` على كل البيانات
  - ثم `whereIn()` مع 10,000 IDs

**التحسينات:**
```php
// الحل الأفضل: استخدام Database Query مباشرة
public function friends()
{
    return static::whereHas('friendRequestsSent', function ($q) {
        $q->where('receiver_id', $this->id)
          ->where('status', FriendRequest::STATUS_ACCEPTED);
    })
    ->orWhereHas('friendRequestsReceived', function ($q) {
        $q->where('sender_id', $this->id)
          ->where('status', FriendRequest::STATUS_ACCEPTED);
    });
}

// أو استخدام Caching
public function friendIds(): array
{
    return Cache::remember("user.{$this->id}.friend_ids", 3600, function () {
        return $this->acceptedFriendRequests()
            ->get()
            ->map(fn (FriendRequest $r) => $r->sender_id === $this->id ? $r->receiver_id : $r->sender_id)
            ->unique()
            ->values()
            ->all();
    });
}
```

**الكود الحالي:**
```php
// app/Models/User.php:84-92
public function friendIds(): array
{
    return $this->acceptedFriendRequests()
        ->get()
        ->map(fn (FriendRequest $r) => $r->sender_id === $this->id ? $r->receiver_id : $r->sender_id)
        ->unique()
        ->values()
        ->all();
}
```

---

### Q14: كيف تتعامل مع الصور المحملة؟ هل تستخدم CDN أو Storage optimization؟

**الإجابة:**
**الحالي:**
- الصور تُحفظ في `storage/app/public/posts`
- يتم الوصول لها عبر `asset('storage/' . $image_path)`
- لا يوجد CDN أو optimization

**التحسينات المقترحة:**
1. **استخدام S3 أو Cloud Storage:**
```php
// config/filesystems.php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
];

// في PostController
$imagePath = $request->file('image')->store('posts', 's3');
```

2. **Image Optimization:**
```php
use Intervention\Image\Facades\Image;

$image = Image::make($request->file('image'))
    ->resize(1200, null, function ($constraint) {
        $constraint->aspectRatio();
    })
    ->encode('jpg', 85);
```

3. **CDN:**
```php
// في Post model
public function imageUrl(): ?string
{
    if (!$this->image_path) {
        return null;
    }
    return config('app.cdn_url') . '/storage/' . $this->image_path;
}
```

---

### Q15: ما هي المشكلة في جلب كل التعليقات في `posts/index.blade.php`؟ كيف يمكن تحسينها؟

**الإجابة:**
**المشكلة:**
- في `PostController@index`، نستخدم `with(['comments' => fn($q) => $q->latest()->limit(5)])`
- هذا جيد، لكن في الـ view نعرض كل التعليقات المحملة
- إذا كان هناك 100 post وكل post له 5 comments، هذا يعني 500 comment في الصفحة

**التحسينات:**
1. **Lazy Loading للتعليقات:**
```javascript
// في Blade، نستخدم AJAX لتحميل التعليقات عند الطلب
<button onclick="loadComments({{ $post->id }})">View Comments</button>
<div id="comments-{{ $post->id }}"></div>
```

2. **تقليل عدد التعليقات المعروضة:**
```php
// في Controller
'comments' => fn ($q) => $q->latest()->limit(3)->with('user'), // 3 بدلاً من 5
```

3. **Pagination للتعليقات:**
```php
// في show.blade.php
$comments = $post->comments()->with('user')->latest()->paginate(10);
```

---

### Q16: كيف يمكن إضافة Pagination للتعليقات في صفحة الـ Feed؟

**الإجابة:**
**الحل:**
1. استخدام AJAX لتحميل التعليقات عند الطلب
2. إنشاء endpoint منفصل للتعليقات

**الكود المقترح:**
```php
// routes/api.php
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

// app/Http/Controllers/Api/CommentController.php
public function index(Post $post)
{
    $comments = $post->comments()
        ->with('user')
        ->latest()
        ->paginate(10);
    
    return CommentResource::collection($comments);
}

// في Blade (AJAX)
<script>
function loadComments(postId, page = 1) {
    fetch(`/api/posts/${postId}/comments?page=${page}`)
        .then(response => response.json())
        .then(data => {
            // عرض التعليقات
        });
}
</script>
```

---

## 4. أسئلة عن Real-time Features

### Q17: كيف يعمل Laravel Broadcasting؟ ما الفرق بين `PublicChannel` و `PrivateChannel`؟

**الإجابة:**
- **Broadcasting** يسمح بإرسال events من Server إلى Clients عبر WebSockets
- **PublicChannel**: أي شخص يمكنه الاشتراك (لا يحتاج authentication)
- **PrivateChannel**: يحتاج authentication و authorization (يتم التحقق في `routes/channels.php`)

**الكود المرجعي:**
```php
// Public Channel (مثال)
public function broadcastOn(): array
{
    return [new Channel('public-feed')];
}

// Private Channel (المستخدم في المشروع)
public function broadcastOn(): array
{
    return [new PrivateChannel('users.' . $this->friendRequest->receiver_id)];
}

// routes/channels.php - Authorization
Broadcast::channel('users.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});
```

---

### Q18: كيف يتم إعداد Laravel Echo في الـ Frontend؟ ما هي المتطلبات？

**الإجابة:**
**المتطلبات:**
1. Laravel Echo package
2. Pusher أو Laravel WebSockets
3. Configuration في `.env`
4. Bootstrap في `resources/js/bootstrap.js`
5. Listeners في `resources/js/app.js`

**الكود المرجعي:**
```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// resources/js/app.js
window.Echo.private(`users.${window.App.userId}`)
    .listen('.FriendRequestSent', (e) => {
        // Handle event
    });
```

---

### Q19: ما الذي يحدث عند إرسال طلب صداقة؟ كيف يتم إرسال الإشعار Real-time؟

**الإجابة:**
**الخطوات:**
1. المستخدم يرسل POST request إلى `/friend-requests`
2. `FriendRequestController@store` ينشئ `FriendRequest`
3. يتم إطلاق Event: `event(new FriendRequestSent($friendRequest))`
4. الـ Event يبث على `PrivateChannel('users.{receiver_id}')`
5. Laravel Echo في Frontend يستقبل الـ event
6. يتم إضافة notification إلى Alpine store

**الكود المرجعي:**
```php
// app/Http/Controllers/FriendRequestController.php:56-62
$friendRequest = FriendRequest::create([...]);
event(new FriendRequestSent($friendRequest)); // إطلاق Event

// app/Events/FriendRequestSent.php:22-27
public function broadcastOn(): array
{
    return [
        new PrivateChannel('users.' . $this->friendRequest->receiver_id),
    ];
}

// resources/js/app.js:52-60
.listen('.FriendRequestSent', (e) => {
    store()?.add({
        type: 'friend_request',
        message: `${e.sender_name} sent you a friend request.`,
        created_at: e.created_at,
        data: e,
    });
});
```

---

### Q20: كيف تتعامل مع انقطاع الاتصال في WebSocket؟ هل هناك retry mechanism؟

**الإجابة:**
**الحالي:**
- لا يوجد retry mechanism مخصص
- Laravel Echo/Pusher لديهم automatic reconnection

**التحسينات المقترحة:**
```javascript
// resources/js/app.js
window.Echo.private(`users.${window.App.userId}`)
    .subscribed(() => {
        console.log('Connected');
    })
    .error((error) => {
        console.error('Connection error', error);
        // يمكن إضافة retry logic هنا
    })
    .listen('.FriendRequestSent', (e) => {
        // Handle event
    });

// إضافة reconnection handling
window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('Disconnected, attempting to reconnect...');
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Reconnected successfully');
});
```

---

## 5. أسئلة عن Database & Models

### Q21: كيف تعمل علاقة الصداقة ثنائية الاتجاه (bidirectional) في `User@friends()`؟

**الإجابة:**
- الصداقة ثنائية: إذا كان User A صديق User B، فـ User B صديق User A أيضاً
- المشكلة: `FriendRequest` له `sender_id` و `receiver_id` (directional)
- الحل: نستخدم `acceptedFriendRequests()` الذي يجلب كل الطلبات المقبولة حيث المستخدم هو sender أو receiver
- ثم نستخرج الـ IDs من الطرف الآخر

**الكود المرجعي:**
```php
// app/Models/User.php:73-79
public function acceptedFriendRequests()
{
    return FriendRequest::where('status', FriendRequest::STATUS_ACCEPTED)
        ->where(function ($q) {
            $q->where('sender_id', $this->id)->orWhere('receiver_id', $this->id);
        });
}

// app/Models/User.php:84-92
public function friendIds(): array
{
    return $this->acceptedFriendRequests()
        ->get()
        ->map(fn (FriendRequest $r) => 
            $r->sender_id === $this->id ? $r->receiver_id : $r->sender_id
        )
        ->unique()
        ->values()
        ->all();
}
```

---

### Q22: ما الفرق بين `withCount()` و `withExists()` في Eloquent؟

**الإجابة:**
- **`withCount()`**: يضيف column جديد يحتوي على عدد الـ related records
  - مثال: `withCount('likes')` يضيف `likes_count` = 5
  
- **`withExists()`**: يضيف column boolean يحدد إذا كان يوجد related records
  - مثال: `withExists(['likes as is_liked_by_me' => fn($q) => $q->where('user_id', 1)])` يضيف `is_liked_by_me` = true/false

**الكود المرجعي:**
```php
// app/Http/Controllers/PostController.php:27-28
->withCount(['comments', 'likes']) // يضيف comments_count, likes_count
->withExists(['likes as is_liked_by_me' => fn ($q) => $q->where('user_id', $user->id)]) // يضيف is_liked_by_me
```

---

### Q23: كيف تتعامل مع حذف المستخدم؟ هل تستخدم Soft Deletes؟ ماذا يحدث للمنشورات والتعليقات؟

**الإجابة:**
**الحالي:**
- لا يوجد Soft Deletes للمستخدم
- عند حذف المستخدم، قد تحدث مشاكل في Foreign Keys

**التحسينات المقترحة:**
1. **استخدام Soft Deletes:**
```php
// app/Models/User.php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    // ...
}
```

2. **Cascade Delete أو Set Null:**
```php
// Migration
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade'); // أو 'set null'
```

3. **Event Listener:**
```php
// app/Models/User.php
protected static function booted()
{
    static::deleting(function ($user) {
        // حذف أو تحديث المنشورات والتعليقات
        $user->posts()->delete();
        $user->comments()->delete();
    });
}
```

---

### Q24: لماذا استخدمت `STATUS_PENDING`, `STATUS_ACCEPTED` كـ constants في `FriendRequest`؟ ما البديل؟

**الإجابة:**
**المزايا:**
- Type safety: لا يمكن استخدام string خاطئ
- سهولة التغيير: تغيير القيمة في مكان واحد
- IDE autocomplete

**البدائل:**
1. **Enum (PHP 8.1+):**
```php
enum FriendRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}

// في Model
protected $casts = [
    'status' => FriendRequestStatus::class,
];
```

2. **Database Enum:**
```php
// Migration
$table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
```

**الكود الحالي:**
```php
// app/Models/FriendRequest.php:18-20
public const STATUS_PENDING = 'pending';
public const STATUS_ACCEPTED = 'accepted';
public const STATUS_REJECTED = 'rejected';
```

---

## 6. أسئلة عن API Design

### Q25: ما الفرق بين `apiResource` و `resource` في Routes؟

**الإجابة:**
- **`Route::resource()`**: للـ Web routes
  - يحتوي على `create` و `edit` routes (للـ forms)
  - مثال: `GET /posts/create`, `GET /posts/{post}/edit`
  
- **`Route::apiResource()`**: للـ API routes
  - لا يحتوي على `create` و `edit` (لأن API لا يحتاج forms)
  - فقط: `index`, `store`, `show`, `update`, `destroy`

**الكود المرجعي:**
```php
// routes/web.php
Route::resource('posts', PostController::class);
// Creates: index, create, store, show, edit, update, destroy

// routes/api.php
Route::apiResource('posts', PostController::class);
// Creates: index, store, show, update, destroy (no create/edit)
```

---

### Q26: كيف تستخدم API Resources؟ ما الفائدة من `PostResource` و `CommentResource`؟

**الإجابة:**
- **API Resources** تحدد شكل الـ JSON response
- الفوائد:
  1. **Consistency**: نفس الشكل دائماً
  2. **Security**: إخفاء بيانات حساسة
  3. **Flexibility**: إضافة/تعديل fields بسهولة
  4. **Relationships**: تضمين related data عند الحاجة

**الكود المرجعي:**
```php
// app/Http/Resources/PostResource.php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'content' => $this->content,
        'user' => new UserResource($this->whenLoaded('user')), // فقط إذا كان loaded
        'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
        'is_liked_by_me' => (bool) ($this->is_liked_by_me ?? false),
    ];
}

// في Controller
return PostResource::collection($posts);
```

---

### Q27: ما هي Status Codes التي ترجعها في الـ API؟ متى تستخدم 200, 201, 204, 422؟

**الإجابة:**
- **200 OK**: نجاح GET/PUT/PATCH requests
- **201 Created**: نجاح POST request (إنشاء resource جديد)
- **204 No Content**: نجاح DELETE request (لا يوجد content للرجوع)
- **422 Unprocessable Entity**: Validation errors
- **401 Unauthorized**: غير مصادق
- **403 Forbidden**: مصادق لكن غير مصرح له

**الكود المرجعي:**
```php
// 201 Created
return response()->json(['user' => $user, 'token' => $token], 201);

// 200 OK
return PostResource::collection($posts);

// 204 No Content
return response()->json([], 204);

// 422 (automatic من Laravel عند validation failure)
$request->validate([...]); // يرمي ValidationException → 422
```

---

### Q28: كيف تتعامل مع Rate Limiting في الـ API؟

**الإجابة:**
**الحالي:**
- لا يوجد rate limiting مخصص

**التحسينات المقترحة:**
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // 60 requests per minute
});

// أو في Controller
public function store(Request $request)
{
    $request->validate([...]);
    
    // Rate limiting مخصص
    $key = 'post_creation:' . $request->user()->id;
    if (RateLimiter::tooManyAttempts($key, 10)) {
        return response()->json(['message' => 'Too many posts'], 429);
    }
    RateLimiter::hit($key, 3600); // 10 posts per hour
    
    // Create post
}
```

---

## 7. أسئلة عن Testing & Quality

### Q29: هل كتبت Unit Tests أو Feature Tests للمشروع؟ كيف تختبر Friend Requests؟

**الإجابة:**
**مثال على Feature Test:**
```php
// tests/Feature/FriendRequestTest.php
public function test_user_can_send_friend_request()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $response = $this->actingAs($user1)
        ->post('/friend-requests', ['receiver_id' => $user2->id]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('friend_requests', [
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'status' => FriendRequest::STATUS_PENDING,
    ]);
}

public function test_user_cannot_send_friend_request_to_self()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/friend-requests', ['receiver_id' => $user->id]);
    
    $response->assertSessionHasErrors('receiver_id');
}
```

---

### Q30: كيف تختبر Broadcasting Events؟

**الإجابة:**
```php
// tests/Feature/BroadcastingTest.php
use Illuminate\Support\Facades\Event;
use App\Events\FriendRequestSent;

public function test_friend_request_sent_event_is_broadcasted()
{
    Event::fake();
    
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $this->actingAs($user1)
        ->post('/friend-requests', ['receiver_id' => $user2->id]);
    
    Event::assertDispatched(FriendRequestSent::class, function ($event) use ($user2) {
        return $event->friendRequest->receiver_id === $user2->id;
    });
}
```

---

### Q31: ما هي Code Quality Tools التي استخدمتها؟ (PHPStan, Pint, PHP CS Fixer)

**الإجابة:**
**المقترح:**
1. **Laravel Pint** (built-in code fixer):
```bash
composer require laravel/pint --dev
./vendor/bin/pint
```

2. **PHPStan** (static analysis):
```bash
composer require phpstan/phpstan --dev
./vendor/bin/phpstan analyse
```

3. **PHPUnit** (testing):
```bash
php artisan test
```

---

## 8. أسئلة عن Scalability & Best Practices

### Q32: كيف يمكن تحسين الكود ليدعم ملايين المستخدمين؟

**الإجابة:**
1. **Database Optimization:**
   - Indexes على foreign keys و search fields
   - Database sharding
   - Read replicas

2. **Caching:**
   ```php
   // Cache friend lists
   Cache::remember("user.{$id}.friends", 3600, fn() => $user->friends());
   
   // Cache posts feed
   Cache::remember("user.{$id}.feed", 300, fn() => $posts);
   ```

3. **Queue Jobs:**
   ```php
   // بدلاً من إرسال notification مباشرة
   dispatch(new SendFriendRequestNotification($friendRequest));
   ```

4. **CDN للصور:**
5. **Redis للـ Sessions و Cache:**
6. **Load Balancing:**
7. **Database Connection Pooling:**

---

### Q33: كيف تتعامل مع Caching؟ هل استخدمت Redis أو Cache؟

**الإجابة:**
**الحالي:**
- لا يوجد caching

**المقترح:**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

// في Controller
$posts = Cache::remember("feed.user.{$user->id}", 300, function () use ($feedUserIds) {
    return Post::whereIn('user_id', $feedUserIds)
        ->with(['user', 'comments'])
        ->latest()
        ->paginate(15);
});

// Cache invalidation
Cache::forget("feed.user.{$user->id}"); // عند إنشاء post جديد
```

---

### Q34: كيف يمكن إضافة Queue Jobs للمهام الثقيلة (مثل إرسال الإيميلات)؟

**الإجابة:**
```php
// app/Jobs/SendFriendRequestEmail.php
class SendFriendRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public FriendRequest $friendRequest
    ) {}
    
    public function handle()
    {
        Mail::to($this->friendRequest->receiver)
            ->send(new FriendRequestNotification($this->friendRequest));
    }
}

// في Controller
dispatch(new SendFriendRequestEmail($friendRequest));

// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),
```

---

### Q35: ما هي المشاكل المحتملة في الكود الحالي؟ كيف يمكن تحسينها؟

**الإجابة:**
1. **N+1 Queries**: تم حلها باستخدام Eager Loading
2. **لا يوجد Caching**: إضافة Redis cache
3. **لا يوجد Rate Limiting**: إضافة throttle middleware
4. **Image Upload**: يحتاج optimization و validation أفضل
5. **لا يوجد Soft Deletes**: إضافتها للمستخدمين
6. **لا يوجد Queue Jobs**: نقل المهام الثقيلة إلى queues
7. **لا يوجد Tests**: إضافة Unit و Feature tests
8. **Security**: إضافة CSRF protection (موجود في Laravel)
9. **Error Handling**: تحسين error messages
10. **API Versioning**: إضافة `/api/v1/` للـ API routes

---

## 9. أسئلة عن Frontend

### Q36: كيف يعمل AlpineJS في المشروع؟ ما هو Alpine Store؟

**الإجابة:**
- **AlpineJS** هو framework خفيف للـ interactivity
- **Alpine Store** هو global state management
- في المشروع، نستخدم store للإشعارات

**الكود المرجعي:**
```javascript
// resources/js/app.js
Alpine.store('notifications', {
    items: [],
    add(item) {
        this.items.unshift(item);
        if (this.items.length > 50) {
            this.items.pop();
        }
    },
    clear() {
        this.items = [];
    }
});

// في Blade
<div x-data x-show="$store.notifications.items.length > 0">
    <span x-text="$store.notifications.items.length"></span>
</div>
```

---

### Q37: كيف تتعامل مع الـ CSRF Token في Forms؟

**الإجابة:**
- Laravel يضيف CSRF token تلقائياً في كل form
- نستخدم `@csrf` directive في Blade
- للـ API، نستخدم Sanctum token بدلاً من CSRF

**الكود المرجعي:**
```blade
{{-- resources/views/posts/index.blade.php:42 --}}
<form action="{{ route('posts.store') }}" method="POST">
    @csrf
    {{-- Form fields --}}
</form>
```

---

### Q38: كيف يمكن إضافة AJAX للتعليقات والإعجابات بدلاً من Page Reload؟

**الإجابة:**
```javascript
// resources/js/app.js
async function addComment(postId, body) {
    const response = await fetch(`/posts/${postId}/comments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ body })
    });
    
    if (response.ok) {
        const data = await response.json();
        // Update UI
        updateCommentsList(postId, data);
    }
}

async function toggleLike(postId) {
    const response = await fetch(`/posts/${postId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    if (response.ok) {
        // Update like button state
        updateLikeButton(postId);
    }
}
```

---

## 10. أسئلة تقنية متقدمة

### Q39: كيف يمكن إضافة Search Functionality للمنشورات (Full-text search)？

**الإجابة:**
```php
// استخدام Laravel Scout مع Algolia أو Meilisearch
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;
    
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user_name' => $this->user->name,
        ];
    }
}

// في Controller
$posts = Post::search($query)->get();
```

---

### Q40: كيف يمكن إضافة File Upload Progress Bar؟

**الإجابة:**
```javascript
// resources/js/app.js
function uploadPost(formData, onProgress) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = (e.loaded / e.total) * 100;
                onProgress(percent);
            }
        });
        
        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                resolve(JSON.parse(xhr.responseText));
            } else {
                reject(new Error('Upload failed'));
            }
        });
        
        xhr.open('POST', '/posts');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        xhr.send(formData);
    });
}
```

---

### Q41: كيف تتعامل مع Image Optimization (Resize, Compress)؟

**الإجابة:**
```php
// استخدام Intervention Image
use Intervention\Image\Facades\Image;

if ($request->hasFile('image')) {
    $image = Image::make($request->file('image'));
    
    // Resize
    $image->resize(1200, null, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    
    // Compress
    $image->encode('jpg', 85); // 85% quality
    
    // Save
    $filename = Str::uuid() . '.jpg';
    Storage::disk('public')->put("posts/{$filename}", $image->stream());
}
```

---

### Q42: كيف يمكن إضافة Activity Feed (نشاطات المستخدم)؟

**الإجابة:**
```php
// Migration
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('type'); // 'post_created', 'comment_added', etc.
    $table->morphs('subject'); // subject_id, subject_type
    $table->timestamps();
});

// Model
class Activity extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function subject()
    {
        return $this->morphTo();
    }
}

// في Controller
Activity::create([
    'user_id' => $user->id,
    'type' => 'post_created',
    'subject_id' => $post->id,
    'subject_type' => Post::class,
]);

// في User Model
public function activities()
{
    return $this->hasMany(Activity::class);
}
```

---

## خاتمة

هذه الإجابات تعتمد على الكود الفعلي في المشروع. تأكد من:
1. فهم كل جزء من الكود
2. معرفة البدائل والحلول الأخرى
3. القدرة على شرح القرارات التي اتخذتها
4. معرفة كيفية تحسين الكود في المستقبل

**نصيحة:** راجع الكود قبل المقابلة وتأكد من فهمك لكل ملف مهم في المشروع.
