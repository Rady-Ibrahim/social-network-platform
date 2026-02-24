## Feature Flows – Social Platform (Laravel)

ملف يربط كل Requirement فى التاسك بالـ **Routes / Controllers / Models / Views / DB / Events / API** الموجودة فعلياً فى الكود.

---

## 1. User Authentication (Web + API)

- **وصف الفلو (Web)**:
  - المستخدم يفتح صفحات التسجيل/الدخول.
  - الطلب يروح لمسارات `routes/auth.php`.
  - يتم التنفيذ من خلال متحكمات `App\Http\Controllers\Auth\*`.
  - يتم استخدام نموذج `User` وجداول `users` فى قاعدة البيانات.
  - Laravel Sanctum مفعّل لاستخدام الـ API Tokens.

- **الملفات المهمة (Web Auth)**:
  - **Routes**:
    - `routes/auth.php`  
      - تسجيل: `GET /register`, `POST /register`
      - تسجيل الدخول: `GET /login`, `POST /login`
      - نسيان/تغيير كلمة المرور، تأكيد البريد، تسجيل الخروج، إلخ.
  - **Controllers**:
    - `app/Http/Controllers/Auth/RegisteredUserController.php`
    - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
    - `app/Http/Controllers/Auth/PasswordResetLinkController.php`
    - `app/Http/Controllers/Auth/NewPasswordController.php`
    - `app/Http/Controllers/Auth/PasswordController.php`
    - `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
    - `app/Http/Controllers/Auth/VerifyEmailController.php`
  - **Views**:
    - `resources/views/auth/login.blade.php`
    - `resources/views/auth/register.blade.php`
    - `resources/views/auth/forgot-password.blade.php`
    - `resources/views/auth/reset-password.blade.php`
    - `resources/views/auth/verify-email.blade.php`
  - **Config**:
  - `config/auth.php` (إعدادات الحراسة، البروڤايدر…)

- **وصف الفلو (API Auth – Sanctum)**:
  - العميل (موبايل / SPA / Postman) يرسل طلب تسجيل أو تسجيل دخول.
  - المسارات فى `routes/api.php`:
    - `POST /api/auth/register` → إنشاء مستخدم + توليد توكن Sanctum.
    - `POST /api/auth/login` → التحقق من البيانات + إصدار توكن جديد.
    - `POST /api/auth/logout` → حذف الـ access token الحالى.
  - المنطق فى `App\Http\Controllers\Api\AuthController`.
  - التوكنات تحفظ فى جدول `personal_access_tokens`.

- **الملفات المهمة (API Auth)**:
  - **Routes**:
    - `routes/api.php`
      - L13–L15: مسارات `register`, `login`, `logout`.
  - **Controller**:
    - `app/Http/Controllers/Api/AuthController.php`
      - `register()` → إنشاء مستخدم + `createToken('api')`.
      - `login()` → `Auth::attempt()` + حذف التوكنات القديمة + إصدار جديد.
      - `logout()` → حذف التوكن الجارى.
  - **Model**:
    - `app/Models/User.php` (يستخدم Trait `HasApiTokens` من Sanctum).
  - **Migration**:
    - `database/migrations/2026_02_08_180138_create_personal_access_tokens_table.php`
  - **Config**:
    - `config/sanctum.php`

---

## 2. User Profiles

- **وصف الفلو (Web Profile)**:
  - المستخدم المسجّل يفتح صفحة تعديل البروفايل.
  - مسار `GET /profile` يمر من `routes/web.php` → `ProfileController@edit`.
  - نموذج تعديل البيانات/الصورة فى فيو `resources/views/profile/edit.blade.php`.
  - التحديث يتم عبر `PATCH /profile` → `ProfileController@update` مع `ProfileUpdateRequest`.
  - بيانات البروفايل (الاسم، البريد، الصورة `avatar`, السيرة الذاتية `bio`) محفوظة فى جدول `users`.

- **الملفات المهمة (Web Profile)**:
  - **Routes**:
    - `routes/web.php` (داخل مجموعة `auth`):
      - `GET /profile` → `ProfileController@edit`
      - `PATCH /profile` → `ProfileController@update`
      - `DELETE /profile` → `ProfileController@destroy`
  - **Controller**:
    - `app/Http/Controllers/ProfileController.php`
  - **Form Request**:
    - `app/Http/Requests/ProfileUpdateRequest.php`
  - **Model**:
    - `app/Models/User.php`
      - الحقول القابلة للملء: `name`, `email`, `password`, `avatar`, `bio`.
      - دالة `avatarUrl()` لعرض رابط الصورة من تخزين `public`.
  - **Migration**:
    - `database/migrations/2026_02_08_200000_add_avatar_and_bio_to_users_table.php`
  - **Views**:
    - `resources/views/profile/edit.blade.php`
    - `resources/views/profile/partials/update-profile-information-form.blade.php`
    - `resources/views/profile/partials/update-password-form.blade.php`
    - `resources/views/profile/partials/delete-user-form.blade.php`

- **وصف الفلو (Public User Page)**:
  - عرض بروفايل أى مستخدم آخر:
    - Web: `GET /users/{user}` → `UserController@show` → فيو `resources/views/users/show.blade.php`.
    - تعرض معلومات المستخدم + حالة الصداقة مع المستخدم الحالى (أصدقاء/طلب معلق/لا يوجد).

---

## 3. Connections (Friend Requests & Friends List)

- **وصف الفلو (Web)**:
  - من واجهة الويب يمكن إرسال/قبول/رفض/إلغاء طلبات الصداقة.
  - صفحة `GET /friends` تعرض:
    - طلبات الصداقة المعلقة المستلمة.
    - قائمة الأصدقاء الفعليين.
  - إرسال طلب (`POST /friend-requests`) يتحقق من:
    - عدم إرسال طلب لنفسك.
    - عدم وجود طلب سابق (أو صداقة) بين نفس المستخدمين.
  - عند إرسال طلب جديد:
    - يتم إنشاء سجل فى جدول `friend_requests`.
    - يتم إطلاق حدث `FriendRequestSent` للبث فى قناة المستخدم المستلم.

- **الملفات المهمة (Web Friends)**:
  - **Routes**:
    - `routes/web.php`:
      - `GET /friends` → `FriendRequestController@index`
      - `POST /friend-requests` → `FriendRequestController@store`
      - `POST /friend-requests/{friend_request}/accept` → `FriendRequestController@accept`
      - `POST /friend-requests/{friend_request}/reject` → `FriendRequestController@reject`
      - `DELETE /friend-requests/{friend_request}` → `FriendRequestController@destroy`
  - **Controller**:
    - `app/Http/Controllers/FriendRequestController.php`
      - `index()` → يجلب الطلبات المعلقة + الأصدقاء.
      - `store()` → إرسال طلب + `event(new FriendRequestSent(...))`.
      - `accept()` / `reject()` / `destroy()` مع تفويض Policy.
  - **Model**:
    - `app/Models/FriendRequest.php`
  - **Policies**:
    - `app/Policies/FriendRequestPolicy.php` (التحكم فى من يمكنه القبول/الرفض/الإلغاء).
  - **User Model Helpers**:
    - `app/Models/User.php`:
      - `friendRequestsSent()`, `friendRequestsReceived()`
      - `acceptedFriendRequests()`
      - `friendIds()` → يرجع IDs الأصدقاء (ثنائى الاتجاه).
      - `friends()` → Query للأصدقاء.
      - `pendingFriendRequestsReceived()` → الطلبات المعلقة.
  - **Views**:
    - `resources/views/friends/index.blade.php`

- **وصف الفلو (API Friends)**:
  - كل العمليات نفسها لكن عبر JSON API:
    - مسارات داخل `Route::middleware('auth:sanctum')` فى `routes/api.php`:
      - `GET /api/friends` → قائمة الأصدقاء.
      - `GET /api/friend-requests` → الطلبات المعلقة.
      - `POST /api/friend-requests` → إرسال طلب.
      - `POST /api/friend-requests/{id}/accept` / `reject`
      - `DELETE /api/friend-requests/{id}` → إلغاء الطلب.
  - **API Controller**:
    - `app/Http/Controllers/Api/FriendRequestController.php`

---

## 4. Posts (Text + Image Upload)

- **وصف الفلو (Web Posts + Images)**:
  - المستخدم المسجّل يفتح صفحة الفيد:
    - `GET /` أو `GET /dashboard` أو `GET /posts` → `PostController@index`.
    - يتم تكوين قائمة منشورات من المستخدم + أصدقائه فقط.
  - إنشاء منشور:
    - Form فى `resources/views/posts/index.blade.php`.
    - الطلب `POST /posts` يذهب إلى `PostController@store`.
    - التحقق:
      - `content` مطلوب، نصى، بحد أقصى 5000 حرف.
      - `image` اختيارية، نوع صورة، حجم حتى 2MB.
    - تخزين الصورة:
      - `store('posts', 'public')` وتخزين المسار فى الحقل `image_path` بالجدول `posts`.
  - تعديل/حذف منشور:
    - `GET /posts/{post}/edit` → `PostController@edit` (مع Policy).
    - `PUT /posts/{post}` → `PostController@update` (مع إدارة صورة قديمة/جديدة).
    - `DELETE /posts/{post}` → `PostController@destroy`.

- **الملفات المهمة (Web Posts)**:
  - **Routes**:
    - `routes/web.php`:
      - `GET /posts`, `GET /posts/{post}`, `POST /posts`,
        `GET /posts/{post}/edit`, `PUT /posts/{post}`, `DELETE /posts/{post}`.
  - **Controller**:
    - `app/Http/Controllers/PostController.php`
      - `index()`, `show()`, `store()`, `edit()`, `update()`, `destroy()`.
  - **Model**:
    - `app/Models/Post.php`
  - **Policy**:
    - `app/Policies/PostPolicy.php` (منع تعديل/حذف منشور غير مملوك للمستخدم).
  - **Migrations**:
    - `2026_02_08_220000_create_posts_table.php`
    - `2026_02_09_000000_add_image_to_posts_table.php` (حقل الصورة).
  - **Views**:
    - `resources/views/posts/index.blade.php`
    - `resources/views/posts/show.blade.php`
    - `resources/views/posts/edit.blade.php`

- **وصف الفلو (API Posts)**:
  - كل العمليات الأساسية متاحة عبر API Resource:
    - `Route::apiResource('posts', PostController::class);` فى `routes/api.php`.
  - **Controller**:
    - `app/Http/Controllers/Api/PostController.php`
      - `index()` → فيد JSON بناءً على الأصدقاء.
      - `store()` → يستخدم `StorePostRequest` لإنشاء منشور.
      - `show()`, `update()`, `destroy()` مع Policies و `PostResource`.
  - **Form Requests**:
    - `app/Http/Requests/Api/StorePostRequest.php`
    - `app/Http/Requests/Api/UpdatePostRequest.php`
  - **Resource**:
    - `app/Http/Resources/PostResource.php`

---

## 5. Comments & Likes

- **وصف الفلو (Web Comments)**:
  - إضافة تعليق من واجهة الويب:
    - `POST /posts/{post}/comments` → `CommentController@store`.
  - تعديل تعليق:
    - `PUT /comments/{comment}` → `CommentController@update`.
  - حذف تعليق:
    - `DELETE /comments/{comment}` → `CommentController@destroy`.
  - يتم استخدام Policy للتأكد من أن صاحب التعليق فقط يستطيع التعديل/الحذف.

- **وصف الفلو (Web Likes)**:
  - إعجاب:
    - `POST /posts/{post}/like` → `PostLikeController@store`.
  - إلغاء إعجاب:
    - `DELETE /posts/{post}/like` → `PostLikeController@destroy`.

- **الملفات المهمة (Web)**:
  - **Routes**:
    - `routes/web.php`:
      - L29–L31: مسارات التعليقات.
      - L33–L34: مسارات الإعجابات.
  - **Controllers**:
    - `app/Http/Controllers/CommentController.php`
    - `app/Http/Controllers/PostLikeController.php`
  - **Models**:
    - `app/Models/Comment.php`
    - `app/Models/PostLike.php`
  - **Policies**:
    - `app/Policies/CommentPolicy.php`
  - **Migrations**:
    - `2026_02_08_230000_create_comments_table.php`
    - `2026_02_08_230001_create_post_likes_table.php`

- **وصف الفلو (API Comments & Likes)**:
  - **Comments API**:
    - `GET /api/posts/{post}/comments` → قائمة التعليقات.
    - `POST /api/posts/{post}/comments` → إنشاء تعليق.
    - `PUT /api/comments/{comment}` → تعديل.
    - `DELETE /api/comments/{comment}` → حذف.
    - داخل مجموعة `auth:sanctum` فى `routes/api.php`.
    - Controller: `app/Http/Controllers/Api/CommentController.php`
    - Form Request: `app/Http/Requests/Api/StoreCommentRequest.php`
    - Resource: `app/Http/Resources/CommentResource.php`
  - **Likes API**:
    - `POST /api/posts/{post}/like`
    - `DELETE /api/posts/{post}/like`
    - `GET /api/posts/{post}/likes` → من `Api\PostLikeController@index`.
    - Controller: `app/Http/Controllers/Api/PostLikeController.php`
    - Resource (likes ضمن `PostResource` و/أو response مخصص).

---

## 6. Database Structure (Users, Posts, Comments, Likes, Connections)

- **الملفات الرئيسية**:
  - **Migrations أساسية (Laravel)**:
    - `0001_01_01_000000_create_users_table.php`
    - `0001_01_01_000001_create_cache_table.php`
    - `0001_01_01_000002_create_jobs_table.php`
  - **Migrations خاصة بالمشروع**:
    - `2026_02_08_180138_create_personal_access_tokens_table.php` (Sanctum).
    - `2026_02_08_200000_add_avatar_and_bio_to_users_table.php` (بروفايل).
    - `2026_02_08_210000_create_friend_requests_table.php` (الصداقة).
    - `2026_02_08_220000_create_posts_table.php` (المنشورات).
    - `2026_02_08_230000_create_comments_table.php` (التعليقات).
    - `2026_02_08_230001_create_post_likes_table.php` (الإعجابات).
    - `2026_02_09_000000_add_image_to_posts_table.php` (صورة المنشور).
  - **Factories / Seeders**:
    - `database/factories/*.php`
    - `database/seeders/DatabaseSeeder.php`

- **العلاقات بين النماذج (ملخص)**:
  - `User`:
    - له `posts()`, `friendRequestsSent()`, `friendRequestsReceived()`, `friends()`.
  - `Post`:
    - `belongsTo(User)`، وله `comments`, `likes`.
  - `Comment`:
    - `belongsTo(Post)` + `belongsTo(User)`.
  - `PostLike`:
    - `belongsTo(Post)` + `belongsTo(User)`.
  - `FriendRequest`:
    - `sender`, `receiver` (عبر foreign keys).

---

## 7. Frontend (Blade Views & Layouts)

- **وصف عام**:
  - الواجهة بالكامل مبنية بـ Blade.
  - هناك Layout رئيسى للتطبيق + Layout للضيوف.
  - الصفحات الأساسية:
    - Dashboard/Feed.
    - Posts index/show/edit.
    - Friends index.
    - User profile page.
    - Profile edit page.
    - Auth pages.

- **أهم الملفات**:
  - **Layouts**:
    - `resources/views/layouts/app.blade.php`
    - `resources/views/layouts/guest.blade.php`
    - `resources/views/layouts/navigation.blade.php`
  - **Feed & Posts**:
    - `resources/views/dashboard.blade.php` (غالباً يوجه إلى الفيد).
    - `resources/views/posts/index.blade.php`
    - `resources/views/posts/show.blade.php`
    - `resources/views/posts/edit.blade.php`
  - **Friends & Users**:
    - `resources/views/friends/index.blade.php`
    - `resources/views/users/show.blade.php`
  - **Profile**:
    - `resources/views/profile/edit.blade.php` + partials.
  - **Auth**:
    - `resources/views/auth/*.blade.php`
  - **Components**:
    - `resources/views/components/*.blade.php` (أزرار، فورم، مودال، إلخ).

---

## 8. API Endpoints & Response Format

- **تمركز مسارات الـ API**:
  - `routes/api.php`:
    - Auth: `POST /api/auth/register`, `login`, `logout`.
    - Users: `GET /api/users` (بحث)، `GET /api/users/{user}`.
    - Profile: `GET /api/profile`, `PUT/PATCH /api/profile`.
    - Posts: `apiResource('posts', PostController::class)`.
    - Comments: `GET /api/posts/{post}/comments`, `POST`, `PUT`, `DELETE`.
    - Likes: `POST /api/posts/{post}/like`, `DELETE`, `GET /api/posts/{post}/likes`.
    - Friends: `GET /api/friends`, `GET /api/friend-requests`, `POST`, `accept`, `reject`, `DELETE`.
  - أغلب المسارات محمية بـ `auth:sanctum` (باستثناء التسجيل/الدخول وبعض البحث العام).

- **Controllers و Resources**:
  - **Controllers (API)**: جميعها فى:
    - `app/Http/Controllers/Api/`
      - `AuthController.php`
      - `UserController.php`
      - `ProfileController.php`
      - `PostController.php`
      - `CommentController.php`
      - `PostLikeController.php`
      - `FriendRequestController.php`
  - **Form Requests (API)**:
    - `app/Http/Requests/Api/StorePostRequest.php`
    - `app/Http/Requests/Api/UpdatePostRequest.php`
    - `app/Http/Requests/Api/StoreCommentRequest.php`
    - `app/Http/Requests/Api/StoreFriendRequestRequest.php`
  - **Resources (JSON)**:
    - `app/Http/Resources/PostResource.php`
    - `app/Http/Resources/CommentResource.php`
    - `app/Http/Resources/FriendRequestResource.php`
    - `app/Http/Resources/UserResource.php`

- **شكل الـ JSON والـ Status Codes**:
  - **نجاح**:
    - 200: استرجاع بيانات (مثلاً `show`, `index`).
    - 201: عند الإنشاء (مثلاً `AuthController@register`, `PostController@store`).
    - 204: عند الحذف (`Api\PostController@destroy`).
  - **أخطاء تحقّق**:
    - Laravel يرجع 422 مع تفاصيل الأخطاء (ValidationException).
  - **غير مصرح**:
    - 401/403 عند الفشل فى المصادقة أو التفويض.

---

## 9. Real-Time Notifications (Laravel Echo + WebSockets/Pusher)

- **وصف الفلو**:
  - عند حدوث أحداث معينة (طلب صداقة، تعليق، إعجاب)، يتم إطلاق Events تطبق `ShouldBroadcast`.
  - كل Event يذيع على قناة خاصة `PrivateChannel('users.{id}')`.
  - فى الـ Frontend:
    - يتم تهيئة Laravel Echo فى ملف `resources/js/bootstrap.js` (ضمن المشروع القياسى).
    - ملف `resources/js/app.js`:
      - يعرّف مخزن `notifications` فى AlpineJS.
      - بعد تحميل الصفحة، إذا كان `window.App.userId` و `window.Echo` موجودين:
        - يشترك فى قناة `users.{userId}`.
        - يسمع للأحداث:
          - `.FriendRequestSent`
          - `.CommentCreated`
          - `.PostLiked`
          - `.TestBroadcast`
        - يضيف إشعار جديد لقائمة `notifications.items`.

- **الملفات المهمة**:
  - **Events**:
    - `app/Events/FriendRequestSent.php`
    - `app/Events/CommentCreated.php`
    - `app/Events/PostLiked.php`
    - `app/Events/TestBroadcast.php`
  - **Broadcast Channels**:
    - `routes/channels.php`
      - قناة `users.{id}` مع تحقق من أن الـ id هو نفس المستخدم المصادق.
  - **Providers**:
    - `app/Providers/BroadcastServiceProvider.php` (تسجيل القنوات).
  - **Frontend JS**:
    - `resources/js/app.js`
    - (إعداد Echo غالباً فى `resources/js/bootstrap.js` + إعداد Pusher فى `.env`).
  - **Views**:
    - جزء الإشعارات عادةً داخل `resources/views/layouts/navigation.blade.php` (أزرار/أيقونة إشعارات تستخدم Alpine store).

---

## 10. User Search Functionality

- **وصف الفلو (Web JSON Search)**:
  - من واجهة الويب يمكن البحث عن المستخدمين (مثلاً لاختيار صديق).
  - المسار:
    - `GET /users/search?q=...&friends_only=...` (محمي بـ `auth`).
  - المتحكم:
    - `UserController@search` يرجع JSON بـ `UserResource::collection`.
  - يمكن تقييد البحث على الأصدقاء فقط باستخدام `friends_only=true`.

- **وصف الفلو (API Users)**:
  - `GET /api/users`:
    - غالباً نفس منطق البحث لكن عبر `Api\UserController@index`.

- **الملفات المهمة**:
  - **Routes (Web)**:
    - `routes/web.php`:
      - L21: `Route::get('/users/search', [UserController::class, 'search'])`.
  - **Routes (API)**:
    - `routes/api.php`:
      - L18: `Route::get('/users', [UserController::class, 'index']);`
  - **Controllers**:
    - Web: `app/Http/Controllers/UserController.php` (`search()`, `show()`).
    - API: `app/Http/Controllers/Api/UserController.php`.
  - **Resource**:
    - `app/Http/Resources/UserResource.php`.

---

## 11. API Documentation (Scribe) + Postman

- **وصف الفلو**:
  - المشروع يستخدم Scribe لتوليد توثيق API.
  - الملفات المحورية:
    - إعدادات Scribe فى `config/scribe.php`.
    - ملفات Markdown/YAML فى مجلد `.scribe`.
    - صفحة واجهة التوثيق:
      - `resources/views/scribe/index.blade.php`.
  - يمكن توليد الـ docs ثم الوصول لها عبر رابط (يُحدد فى إعدادات Scribe).

- **الملفات المهمة**:
  - `.scribe/`:
    - `auth.md`
    - `endpoints/00.yaml`, `01.yaml`, `custom.0.yaml`
    - `endpoints.cache/*`
    - `intro.md`
  - **View**:
    - `resources/views/scribe/index.blade.php`
  - **Scribe assets**:
    - `public/vendor/scribe/*`
  - **Postman Collection**:
    - `postman/Social-Platform-API.postman_collection.json`

---

## 12. GitHub Repository (Outside Code Scope)

- **ملاحظة**:
  - متطلب إنشاء Repository على GitHub لا يظهر فى الكود نفسه.
  - التنفيذ العملى:
    - إنشاء repo جديد على GitHub.
    - ربط المشروع الحالى (`platform-social`) به.
    - تنفيذ أوامر Git القياسية (`git init`, `git add .`, `git commit`, `git remote add origin`, `git push`).

---

### ملاحظة ختامية

- لملخص أعلى مستوى (نظرة سريعة على الموديولات الرئيسية) يمكنك الرجوع أيضاً إلى `TASK-SUMMARY.md`.
- هذا الملف `FEATURE-FLOWS.md` يربط كل Requirement بالملفات والمسارات الفعلية فى المشروع لتسهيل التتبع والفهم السريع للكود.

