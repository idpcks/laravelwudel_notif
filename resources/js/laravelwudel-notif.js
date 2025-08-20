/**
 * LaravelWudel Notifications - JavaScript Library for Web Push Notifications
 * 
 * This library provides an easy way to integrate web push notifications
 * with Laravel applications using the LaravelWudel Notif package.
 * 
 * @version 1.0.0
 * @date 2025-08-20
 * @author LaravelWudel Team
 */

class LaravelWudelNotifications {
    constructor(options = {}) {
        this.options = {
            vapidPublicKey: options.vapidPublicKey || '',
            serviceWorkerPath: options.serviceWorkerPath || '/sw.js',
            apiBaseUrl: options.apiBaseUrl || '/api/push',
            autoSubscribe: options.autoSubscribe !== false,
            ...options
        };

        this.registration = null;
        this.subscription = null;
        this.isSupported = this.checkSupport();
        
        if (this.isSupported && this.options.autoSubscribe) {
            this.init();
        }
    }

    /**
     * Check if push notifications are supported
     */
    checkSupport() {
        return 'serviceWorker' in navigator && 'PushManager' in window;
    }

    /**
     * Initialize push notifications
     */
    async init() {
        try {
            if (!this.isSupported) {
                throw new Error('Push notifications are not supported in this browser');
            }

            // Register service worker
            this.registration = await navigator.serviceWorker.register(this.options.serviceWorkerPath);
            
            // Check permission
            const permission = await this.requestPermission();
            if (permission !== 'granted') {
                throw new Error('Notification permission denied');
            }

            // Subscribe to push notifications
            await this.subscribe();

            return true;
        } catch (error) {
            console.error('Failed to initialize push notifications:', error);
            throw error;
        }
    }

    /**
     * Request notification permission
     */
    async requestPermission() {
        if (Notification.permission === 'granted') {
            return 'granted';
        }

        if (Notification.permission === 'denied') {
            throw new Error('Notification permission denied');
        }

        return await Notification.requestPermission();
    }

    /**
     * Subscribe to push notifications
     */
    async subscribe() {
        try {
            this.subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.options.vapidPublicKey)
            });

            // Send subscription to backend
            await this.saveSubscription(this.subscription);

            return this.subscription;
        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
            throw error;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            if (this.subscription) {
                await this.subscription.unsubscribe();
                
                // Remove subscription from backend
                await this.removeSubscription();
                
                this.subscription = null;
                return true;
            }
            return false;
        } catch (error) {
            console.error('Failed to unsubscribe from push notifications:', error);
            throw error;
        }
    }

    /**
     * Save subscription to backend
     */
    async saveSubscription(subscription) {
        const response = await fetch(`${this.options.apiBaseUrl}/subscriptions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                p256dh: this.arrayBufferToBase64(subscription.getKey('p256dh')),
                auth: this.arrayBufferToBase64(subscription.getKey('auth')),
                topic: 'general',
                device_info: this.getDeviceInfo()
            })
        });

        if (!response.ok) {
            throw new Error('Failed to save subscription');
        }

        return await response.json();
    }

    /**
     * Remove subscription from backend
     */
    async removeSubscription() {
        if (!this.subscription) {
            return false;
        }

        try {
            const response = await fetch(`${this.options.apiBaseUrl}/subscriptions/${this.subscription.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            return response.ok;
        } catch (error) {
            console.error('Failed to remove subscription:', error);
            return false;
        }
    }

    /**
     * Send test notification
     */
    async sendTestNotification(title = 'Test Notification', message = 'This is a test notification') {
        try {
            const response = await fetch(`${this.options.apiBaseUrl}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    type: 'user',
                    target: 'current',
                    title: title,
                    message: message,
                    data: {
                        url: window.location.href,
                        action: 'test'
                    }
                })
            });

            if (!response.ok) {
                throw new Error('Failed to send test notification');
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to send test notification:', error);
            throw error;
        }
    }

    /**
     * Get subscription status
     */
    getStatus() {
        return {
            supported: this.isSupported,
            registered: !!this.registration,
            subscribed: !!this.subscription,
            permission: Notification.permission
        };
    }

    /**
     * Get device information
     */
    getDeviceInfo() {
        return {
            type: this.getDeviceType(),
            browser: this.getBrowserInfo(),
            os: this.getOperatingSystem(),
            screen: {
                width: screen.width,
                height: screen.height
            },
            userAgent: navigator.userAgent
        };
    }

    /**
     * Get device type
     */
    getDeviceType() {
        const ua = navigator.userAgent.toLowerCase();
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
            return 'tablet';
        }
        if (/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i.test(ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    /**
     * Get browser information
     */
    getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = 'unknown';
        let version = '';

        if (ua.includes('Firefox/')) {
            browser = 'Firefox';
            version = ua.match(/Firefox\/(\d+)/)?.[1] || '';
        } else if (ua.includes('Chrome/')) {
            browser = 'Chrome';
            version = ua.match(/Chrome\/(\d+)/)?.[1] || '';
        } else if (ua.includes('Safari/')) {
            browser = 'Safari';
            version = ua.match(/Version\/(\d+)/)?.[1] || '';
        } else if (ua.includes('Edge/')) {
            browser = 'Edge';
            version = ua.match(/Edge\/(\d+)/)?.[1] || '';
        }

        return { name: browser, version: version };
    }

    /**
     * Get operating system
     */
    getOperatingSystem() {
        const ua = navigator.userAgent;
        let os = 'unknown';
        let version = '';

        if (ua.includes('Windows')) {
            os = 'Windows';
            if (ua.includes('Windows NT 10.0')) version = '10';
            else if (ua.includes('Windows NT 6.3')) version = '8.1';
            else if (ua.includes('Windows NT 6.2')) version = '8';
            else if (ua.includes('Windows NT 6.1')) version = '7';
        } else if (ua.includes('Mac OS X')) {
            os = 'macOS';
            version = ua.match(/Mac OS X (\d+_\d+)/)?.[1]?.replace('_', '.') || '';
        } else if (ua.includes('Linux')) {
            os = 'Linux';
        } else if (ua.includes('Android')) {
            os = 'Android';
            version = ua.match(/Android (\d+)/)?.[1] || '';
        } else if (ua.includes('iOS')) {
            os = 'iOS';
            version = ua.match(/OS (\d+)/)?.[1] || '';
        }

        return { name: os, version: version };
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Convert URL-safe base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Convert ArrayBuffer to base64
     */
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }
}

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LaravelWudelNotifications;
} else if (typeof define === 'function' && define.amd) {
    define([], function() { return LaravelWudelNotifications; });
} else {
    window.LaravelWudelNotifications = LaravelWudelNotifications;
}
