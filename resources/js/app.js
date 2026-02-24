import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('notifications', {
        items: [],

        async loadInitial() {
            try {
                const res = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!res.ok) {
                    console.error('Failed to load notifications', res.status);
                    return;
                }

                const data = await res.json();
                if (Array.isArray(data)) {
                    this.items = data.map((n) => ({
                        id: n.id,
                        read: !!n.read,
                        type: n.type,
                        message: n.message,
                        created_at: n.created_at,
                        data: n.data ?? {},
                    }));
                }
            } catch (error) {
                console.error('Failed to load notifications', error);
            }
        },

        add(notification) {
            this.items.unshift({
                id: Date.now() + Math.random(),
                read: false,
                ...notification,
            });
        },

        unreadCount() {
            return this.items.filter((n) => !n.read).length;
        },

        async markAllAsRead() {
            this.items.forEach((n) => {
                n.read = true;
            });

            try {
                await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
            } catch (error) {
                console.error('Failed to mark notifications as read', error);
            }
        },
    });

    // Load existing notifications from the backend on init
    Alpine.store('notifications').loadInitial();

    // Friend request actions on user profile (no page reload)
    Alpine.data('friendRequestProfile', (config) => ({
        state: config.state,
        loading: false,
        receiverId: config.receiverId,
        storeUrl: config.storeUrl,
        acceptUrl: config.acceptUrl,
        rejectUrl: config.rejectUrl,
        destroyUrl: config.destroyUrl,
        csrf: config.csrf,

        headers() {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrf,
            };
        },

        async sendRequest() {
            if (this.loading || this.state !== 'add_friend') return;
            this.loading = true;
            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: this.headers(),
                    credentials: 'same-origin',
                    body: JSON.stringify({ receiver_id: this.receiverId }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok) {
                    this.state = 'pending_from_me';
                } else {
                    alert(data.message || 'Request failed');
                }
            } catch (e) {
                console.error(e);
                alert('Request failed');
            } finally {
                this.loading = false;
            }
        },

        async cancelRequest() {
            if (this.loading || !this.destroyUrl) return;
            this.loading = true;
            try {
                const res = await fetch(this.destroyUrl, {
                    method: 'DELETE',
                    headers: this.headers(),
                    credentials: 'same-origin',
                });
                if (res.ok || res.status === 204) {
                    this.state = 'add_friend';
                    this.destroyUrl = null;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async acceptRequest() {
            if (this.loading || !this.acceptUrl) return;
            this.loading = true;
            try {
                const res = await fetch(this.acceptUrl, {
                    method: 'POST',
                    headers: this.headers(),
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    this.state = 'friends';
                    this.acceptUrl = null;
                    this.rejectUrl = null;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async rejectRequest() {
            if (this.loading || !this.rejectUrl) return;
            this.loading = true;
            try {
                const res = await fetch(this.rejectUrl, {
                    method: 'POST',
                    headers: this.headers(),
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    this.state = 'add_friend';
                    this.acceptUrl = null;
                    this.rejectUrl = null;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
    }));

    // Accept/Reject friend request on friends index page (no page reload)
    Alpine.data('friendRequestAcceptReject', (config) => ({
        loading: false,
        acceptUrl: config.acceptUrl,
        rejectUrl: config.rejectUrl,
        csrf: config.csrf,

        headers() {
            return {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrf,
            };
        },

        async accept() {
            if (this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(this.acceptUrl, {
                    method: 'POST',
                    headers: this.headers(),
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    this.$el.remove();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async reject() {
            if (this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(this.rejectUrl, {
                    method: 'POST',
                    headers: this.headers(),
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    this.$el.remove();
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
    }));
});

Alpine.start();

// اشترك في قناة المستخدم بعد تحميل الصفحة وتعريف window.App من Blade
window.addEventListener('load', () => {
    if (!window.App || !window.App.userId || !window.Echo) {
        console.warn('Echo not initialized or user not authenticated, skipping subscription');
        return;
    }

    const channelName = `users.${window.App.userId}`;
    const store = () => window.Alpine?.store('notifications');

    console.log('Subscribing to channel:', channelName);

    window.Echo.private(channelName)
        .subscribed(() => {
            console.log('Subscribed to', channelName);
        })
        .error((error) => {
            console.error('Echo channel error', error);
        })
        .listen('.FriendRequestSent', (e) => {
            console.log('Received FriendRequestSent', e);
            store()?.add({
                type: 'friend_request',
                message: `${e.sender_name} sent you a friend request.`,
                created_at: e.created_at,
                data: e,
            });
        })
        .listen('.CommentCreated', (e) => {
            console.log('Received CommentCreated', e);
            store()?.add({
                type: 'comment',
                message: `${e.author_name} commented on your post.`,
                created_at: e.created_at,
                data: e,
            });
        })
        .listen('.PostLiked', (e) => {
            console.log('Received PostLiked', e);
            store()?.add({
                type: 'like',
                message: `${e.liker_name} liked your post.`,
                created_at: e.created_at,
                data: e,
            });
        })
        .listen('.TestBroadcast', (e) => {
            console.log('Received TestBroadcast', e);
            store()?.add({
                type: 'test',
                message: e.message ?? 'Test notification',
                created_at: e.created_at,
                data: e,
            });
        });
});

// Like/Unlike without full page reload
window.addEventListener('DOMContentLoaded', () => {
    const wrappers = document.querySelectorAll('[data-post-actions]');
    if (wrappers.length) {
        wrappers.forEach((root) => {
            const likeForm = root.querySelector('[data-like-form]');
            const unlikeForm = root.querySelector('[data-unlike-form]');
            const likeBtn = root.querySelector('.js-like-btn');
            const unlikeBtn = root.querySelector('.js-unlike-btn');
            const countEl = root.querySelector('.js-likes-count');

            if (!likeForm || !unlikeForm || !likeBtn || !unlikeBtn || !countEl) return;

            let liked = root.dataset.liked === '1';
            let count = parseInt(root.dataset.likesCount || '0', 10) || 0;

            const applyState = () => {
                // Update count text
                countEl.textContent = count;

                if (liked) {
                    // Like button disabled
                    likeBtn.disabled = true;
                    likeBtn.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                    likeBtn.classList.remove('bg-white', 'text-slate-700', 'hover:bg-slate-50', 'hover:border-slate-300');

                    // Unlike button enabled
                    unlikeBtn.disabled = false;
                    unlikeBtn.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                    unlikeBtn.classList.add('border-indigo-200', 'bg-indigo-50', 'text-indigo-700');
                } else {
                    // Like button enabled
                    likeBtn.disabled = false;
                    likeBtn.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                    likeBtn.classList.add('border-slate-200', 'bg-white', 'text-slate-700');

                    // Unlike button disabled
                    unlikeBtn.disabled = true;
                    unlikeBtn.classList.remove('border-indigo-200', 'bg-indigo-50', 'text-indigo-700');
                    unlikeBtn.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-400', 'cursor-not-allowed');
                }
            };

            applyState();

            likeForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (liked) return;

                liked = true;
                count = count + 1;
                applyState();

                try {
                    await window.axios.post(likeForm.action, new FormData(likeForm));
                } catch (e) {
                    console.error('Like failed', e);
                    liked = false;
                    count = Math.max(0, count - 1);
                    applyState();
                }
            });

            unlikeForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!liked) return;

                liked = false;
                count = Math.max(0, count - 1);
                applyState();

                const formData = new FormData(unlikeForm);
                formData.append('_method', 'DELETE');

                try {
                    await window.axios.post(unlikeForm.action, formData);
                } catch (e) {
                    console.error('Unlike failed', e);
                    liked = true;
                    count = count + 1;
                    applyState();
                }
            });
        });
    }

    // Comment Like/Unlike without full page reload
    const commentWrappers = document.querySelectorAll('[data-comment-actions]');
    if (commentWrappers.length) {
        commentWrappers.forEach((root) => {
            const likeForm = root.querySelector('[data-comment-like-form]');
            const unlikeForm = root.querySelector('[data-comment-unlike-form]');
            const likeBtn = root.querySelector('.js-comment-like-btn');
            const unlikeBtn = root.querySelector('.js-comment-unlike-btn');
            const likeText = root.querySelector('.js-comment-like-text');
            const countEl = root.querySelector('.js-comment-likes-count');

            if (!likeForm || !unlikeForm || !likeBtn || !unlikeBtn || !likeText || !countEl) return;

            let liked = root.dataset.liked === '1';
            let count = parseInt(root.dataset.likesCount || '0', 10) || 0;
            const likeTextLabel = root.dataset.likeText || 'Like';
            const likedTextLabel = root.dataset.likedText || 'Liked';

            const applyState = () => {
                // Update count text
                countEl.textContent = count;

                if (liked) {
                    // Like button - show "Liked" and change color
                    likeBtn.classList.remove('text-gray-500', 'hover:text-indigo-600');
                    likeBtn.classList.add('text-indigo-600');
                    likeText.textContent = likedTextLabel;

                    // Unlike button - show
                    unlikeBtn.classList.remove('hidden');
                } else {
                    // Like button - show "Like" and default color
                    likeBtn.classList.remove('text-indigo-600');
                    likeBtn.classList.add('text-gray-500', 'hover:text-indigo-600');
                    likeText.textContent = likeTextLabel;

                    // Unlike button - hide
                    unlikeBtn.classList.add('hidden');
                }
            };

            applyState();

            likeForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (liked) return;

                liked = true;
                count = count + 1;
                applyState();

                try {
                    await window.axios.post(likeForm.action, new FormData(likeForm));
                } catch (e) {
                    console.error('Comment like failed', e);
                    liked = false;
                    count = Math.max(0, count - 1);
                    applyState();
                }
            });

            unlikeForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!liked) return;

                liked = false;
                count = Math.max(0, count - 1);
                applyState();

                const formData = new FormData(unlikeForm);
                formData.append('_method', 'DELETE');

                try {
                    await window.axios.post(unlikeForm.action, formData);
                } catch (e) {
                    console.error('Comment unlike failed', e);
                    liked = true;
                    count = count + 1;
                    applyState();
                }
            });
        });
    }
});
