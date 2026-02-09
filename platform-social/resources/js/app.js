import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('notifications', {
        items: [],

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

        markAllAsRead() {
            this.items.forEach((n) => {
                n.read = true;
            });
        },
    });
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
