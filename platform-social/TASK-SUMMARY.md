# ملخص مشروع منصة التواصل الاجتماعي

## نظرة عامة على المشروع

منصة تواصل اجتماعي مبنية على Laravel توفر:
- **التسجيل والمصادقة**: تسجيل المستخدمين وتسجيل الدخول
- **المنشورات**: إنشاء، تعديل، حذف، عرض المنشورات
- **التعليقات**: إضافة، تعديل، حذف التعليقات على المنشورات
- **الإعجابات**: إعجاب وإلغاء إعجاب بالمنشورات
- **صداقة المستخدمين**: إرسال طلبات صداقة، قبولها، رفضها
- **الملف الشخصي**: عرض وتعديل الملف الشخصي (صورة، السيرة الذاتية)

---

## هيكل المشروع والربط بين الملفات

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              مسارات (Routes)                                     │
│                              routes/web.php                                      │
│                              routes/api.php                                      │
└───────────────────────────────┬─────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            المتحكمات (Controllers)                               │
│  PostController │ CommentController │ PostLikeController │ FriendRequestController │
│  UserController │ ProfileController │ Auth Controllers                           │
└───────────────────────────────┬─────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              النماذج (Models)                                    │
│  User │ Post │ Comment │ PostLike │ FriendRequest                               │
└───────────────────────────────┬─────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            قاعدة البيانات (Migrations)                            │
│  users │ posts │ comments │ post_likes │ friend_requests                         │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## الفلو الرئيسي (Flow)

### 1. المستخدم (User)
- **Model**: `app/Models/User.php`
- **Migration**: `0001_01_01_000000_create_users_table.php` + `2026_02_08_200000_add_avatar_and_bio_to_users_table.php`
- **العلاقات**:
  - له العديد من المنشورات `posts()`
  - له علاقات صداقة `friendRequestsSent()`, `friendRequestsReceived()`, `friends()`
  - عرض الصورة: `avatarUrl()`

### 2. المنشورات (Posts)
- **Model**: `app/Models/Post.php`
- **Controller**: `app/Http/Controllers/PostController.php`
- **Policy**: `app/Policies/PostPolicy.php`
- **Migration**: `2026_02_08_220000_create_posts_table.php`
- **Views**: `resources/views/posts/index.blade.php`, `show.blade.php`, `edit.blade.php`
- **الفلو**:
  - `index()` → عرض الفيد (منشورات المستخدم وأصدقائه فقط)
  - `store()` → إنشاء منشور جديد
  - `show()` → عرض منشور واحد مع التعليقات
  - `edit()` / `update()` → تعديل المنشور (بعد التحقق من الصلاحية)
  - `destroy()` → حذف المنشور (بعد التحقق من الصلاحية)

### 3. التعليقات (Comments)
- **Model**: `app/Models/Comment.php`
- **Controller**: `app/Http/Controllers/CommentController.php`
- **Policy**: `app/Policies/CommentPolicy.php`
- **Migration**: `2026_02_08_230000_create_comments_table.php`
- **الفلو**:
  - `store()` → إضافة تعليق على منشور
  - `update()` → تعديل تعليق (للمالك فقط)
  - `destroy()` → حذف تعليق (للمالك فقط)

### 4. الإعجابات (Post Likes)
- **Model**: `app/Models/PostLike.php`
- **Controller**: `app/Http/Controllers/PostLikeController.php`
- **Migration**: `2026_02_08_230001_create_post_likes_table.php`
- **الفلو**:
  - `store()` → إضافة إعجاب
  - `destroy()` → إلغاء الإعجاب

### 5. طلبات الصداقة (Friend Requests)
- **Model**: `app/Models/FriendRequest.php`
- **Controller**: `app/Http/Controllers/FriendRequestController.php`
- **Policy**: `app/Policies/FriendRequestPolicy.php`
- **Migration**: `2026_02_08_210000_create_friend_requests_table.php`
- **View**: `resources/views/friends/index.blade.php`
- **الفلو**:
  - `store()` → إرسال طلب صداقة (مع التحقق من عدم التكرار)
  - `accept()` → قبول الطلب (للـ receiver فقط)
  - `reject()` → رفض الطلب
  - `destroy()` → إلغاء الطلب (للـ sender فقط)

### 6. صفحة المستخدم (User Profile)
- **Controller**: `app/Http/Controllers/UserController.php`
- **View**: `resources/views/users/show.blade.php`
- **الفلو**: `show()` → عرض ملف المستخدم + حالة الصداقة مع المستخدم الحالي

---

## جدول العلاقات بين النماذج

| النموذج | علاقته مع |
|---------|-----------|
| **User** | posts, friendRequestsSent, friendRequestsReceived, friends |
| **Post** | user (BelongsTo), comments, likes |
| **Comment** | post, user |
| **PostLike** | post, user |
| **FriendRequest** | sender, receiver |

---

## المسارات (Routes)

### Web Routes (مصادقة Session)
| Method | URI | Controller | الوظيفة |
|--------|-----|------------|---------|
| GET | `/dashboard` | PostController@index | الصفحة الرئيسية (الفيد) |
| GET | `/users/{user}` | UserController@show | صفحة المستخدم |
| GET | `/posts` | PostController@index | قائمة المنشورات |
| GET | `/posts/{post}` | PostController@show | عرض منشور |
| POST | `/posts` | PostController@store | إنشاء منشور |
| GET | `/posts/{post}/edit` | PostController@edit | صفحة تعديل |
| PUT | `/posts/{post}` | PostController@update | تحديث منشور |
| DELETE | `/posts/{post}` | PostController@destroy | حذف منشور |
| POST | `/posts/{post}/comments` | CommentController@store | إضافة تعليق |
| PUT | `/comments/{comment}` | CommentController@update | تعديل تعليق |
| DELETE | `/comments/{comment}` | CommentController@destroy | حذف تعليق |
| POST | `/posts/{post}/like` | PostLikeController@store | إعجاب |
| DELETE | `/posts/{post}/like` | PostLikeController@destroy | إلغاء إعجاب |
| GET | `/friends` | FriendRequestController@index | صفحة الأصدقاء |
| POST | `/friend-requests` | FriendRequestController@store | إرسال طلب صداقة |
| POST | `/friend-requests/{id}/accept` | FriendRequestController@accept | قبول الطلب |
| POST | `/friend-requests/{id}/reject` | FriendRequestController@reject | رفض الطلب |
| DELETE | `/friend-requests/{id}` | FriendRequestController@destroy | إلغاء الطلب |

### API Routes (مصادقة Sanctum)
- نفس الوظائف متاحة عبر API مع `/api` prefix
- الملف: `routes/api.php`
- Controllers: `app/Http/Controllers/Api/`
- Resources: `app/Http/Resources/` (PostResource, CommentResource, ...)

---

## قائمة الملفات التي تم إنشاؤها/تعديلها

### Models
- `app/Models/User.php` - (معدّل: avatar, bio, علاقات الصداقة)
- `app/Models/Post.php`
- `app/Models/Comment.php`
- `app/Models/PostLike.php`
- `app/Models/FriendRequest.php`

### Controllers
- `app/Http/Controllers/PostController.php`
- `app/Http/Controllers/CommentController.php`
- `app/Http/Controllers/PostLikeController.php`
- `app/Http/Controllers/FriendRequestController.php`
- `app/Http/Controllers/UserController.php`

### Policies
- `app/Policies/PostPolicy.php`
- `app/Policies/CommentPolicy.php`
- `app/Policies/FriendRequestPolicy.php`

### Migrations
- `2026_02_08_200000_add_avatar_and_bio_to_users_table.php`
- `2026_02_08_210000_create_friend_requests_table.php`
- `2026_02_08_220000_create_posts_table.php`
- `2026_02_08_230000_create_comments_table.php`
- `2026_02_08_230001_create_post_likes_table.php`

### Views
- `resources/views/posts/index.blade.php` - الفيد
- `resources/views/posts/show.blade.php` - عرض منشور
- `resources/views/posts/edit.blade.php` - تعديل منشور
- `resources/views/friends/index.blade.php` - الأصدقاء وطلبات الصداقة
- `resources/views/users/show.blade.php` - صفحة المستخدم

### Routes
- `routes/web.php` - مسارات الويب
- `routes/api.php` - مسارات API (إن وُجدت)

---

## مخطط قاعدة البيانات (ER Diagram نصي)

```
users
├── id, name, email, password, avatar, bio, ...
│
posts ────────────────────► users (user_id)
├── id, user_id, content
│
comments ─────────────────► posts (post_id)
├── id, post_id, user_id, body    users (user_id)
│
post_likes ───────────────► posts (post_id)
├── id, post_id, user_id          users (user_id)
│
friend_requests ──────────► users (sender_id)
├── id, sender_id, receiver_id    users (receiver_id)
    user_one_id, user_two_id, status (pending|accepted|rejected)
```

---

## كيفية التشغيل

```bash
# تثبيت الاعتماديات
composer install

# نسخ ملف البيئة
cp .env.example .env

# توليد مفتاح التطبيق
php artisan key:generate

# تشغيل الـ migrations (بدون fresh لتجنب مسح البيانات)
php artisan migrate

# تشغيل الخادم
php artisan serve
```

---

*آخر تحديث: فبراير 2026*
