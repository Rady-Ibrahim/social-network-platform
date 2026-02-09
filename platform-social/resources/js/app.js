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

if (window.App && window.App.userId && window.Echo) {
    const channelName = `users.${window.App.userId}`;
    const store = () => window.Alpine?.store('notifications');

    window.Echo.private(channelName)
        .listen('.FriendRequestSent', (e) => {
            store()?.add({
                type: 'friend_request',
                message: `${e.sender_name} sent you a friend request.`,
                created_at: e.created_at,
                data: e,
            });
        })
        .listen('.CommentCreated', (e) => {
            store()?.add({
                type: 'comment',
                message: `${e.author_name} commented on your post.`,
                created_at: e.created_at,
                data: e,
            });
        })
        .listen('.PostLiked', (e) => {
            store()?.add({
                type: 'like',
                message: `${e.liker_name} liked your post.`,
                created_at: e.created_at,
                data: e,
            });
        });
}
