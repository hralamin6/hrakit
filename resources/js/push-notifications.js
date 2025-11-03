/**
 * Push Notification Manager - Clean Implementation
 */

class PushNotificationManager {
    constructor() {
        this.swUrl = '/sw.js';
        this.vapidKey = null;
        this.registration = null;
    }

    async init() {
        if (!('serviceWorker' in navigator && 'PushManager' in window)) {
            console.warn('Push notifications not supported');
            return false;
        }

        try {
            await this.registerServiceWorker();
            await this.loadVapidKey();
            console.log('✅ Push notification manager initialized');
            return true;
        } catch (error) {
            console.error('❌ Init failed:', error);
            return false;
        }
    }

    async registerServiceWorker() {
        this.registration = await navigator.serviceWorker.register(this.swUrl);
        await navigator.serviceWorker.ready;
        console.log('✅ Service Worker ready');
    }

    async loadVapidKey() {
        const response = await fetch('/api/push/vapid-key');
        const data = await response.json();
        this.vapidKey = data.publicKey;
        console.log('✅ VAPID key loaded');
    }

    async subscribe() {
        // Request permission
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            throw new Error('Permission denied');
        }

        // Unsubscribe from old subscription first
        const oldSub = await this.registration.pushManager.getSubscription();
        if (oldSub) {
            await oldSub.unsubscribe();
            console.log('🗑️ Removed old subscription');
        }

        // Create new subscription
        const subscription = await this.registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: this.urlBase64ToUint8Array(this.vapidKey)
        });

        console.log('📱 Browser subscribed:', subscription.endpoint.substring(0, 50) + '...');

        // Send to server
        const response = await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(subscription.toJSON())
        });

        if (!response.ok) {
            const error = await response.text();
            throw new Error('Server error: ' + error);
        }

        const result = await response.json();
        console.log('✅ Server saved subscription:', result);
        return subscription;
    }

    async unsubscribe() {
        const subscription = await this.registration.pushManager.getSubscription();
        if (subscription) {
            await subscription.unsubscribe();

            await fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ endpoint: subscription.endpoint })
            });

            console.log('✅ Unsubscribed');
            return true;
        }
        return false;
    }

    async getSubscription() {
        return await this.registration.pushManager.getSubscription();
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

// Initialize
window.pushManager = new PushNotificationManager();

document.addEventListener('DOMContentLoaded', () => {
    window.pushManager.init();
});

// Helper functions
window.subscribeToPush = async function() {
    try {
        await window.pushManager.subscribe();
        alert('✅ Subscribed to push notifications!');
        location.reload();
    } catch (error) {
        alert('❌ Failed: ' + error.message);
        console.error(error);
    }
};

window.unsubscribeFromPush = async function() {
    try {
        await window.pushManager.unsubscribe();
        alert('✅ Unsubscribed!');
        location.reload();
    } catch (error) {
        alert('❌ Failed: ' + error.message);
        console.error(error);
    }
};

window.checkPushStatus = async function() {
    const sub = await window.pushManager.getSubscription();
    console.log('Subscription:', sub);
    return sub;
};

