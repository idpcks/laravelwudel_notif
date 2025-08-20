// Service Worker for Push Notifications
// This file will be published to public/sw.js

self.addEventListener('install', function(event) {
    console.log('Service Worker installing...');
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    console.log('Service Worker activating...');
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function(event) {
    console.log('Push event received:', event);
    
    if (event.data) {
        try {
            const data = event.data.json();
            console.log('Push data:', data);
            
            const options = {
                body: data.message,
                icon: data.icon || '/favicon.ico',
                badge: data.badge || '/favicon.ico',
                data: data.data || {},
                requireInteraction: data.requireInteraction || false,
                silent: data.silent || false,
                tag: data.tag || null,
                renotify: data.renotify || false,
                actions: data.actions || [],
                vibrate: [200, 100, 200],
                timestamp: data.timestamp || Date.now(),
            };

            // Add image if provided
            if (data.image) {
                options.image = data.image;
            }

            event.waitUntil(
                self.registration.showNotification(data.title, options)
            );
        } catch (error) {
            console.error('Error parsing push data:', error);
            
            // Fallback to text notification
            const options = {
                body: event.data.text() || 'New notification',
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                vibrate: [200, 100, 200],
            };

            event.waitUntil(
                self.registration.showNotification('Notification', options)
            );
        }
    } else {
        // No data, show default notification
        const options = {
            body: 'You have a new notification',
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            vibrate: [200, 100, 200],
        };

        event.waitUntil(
            self.registration.showNotification('New Notification', options)
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    if (event.action) {
        console.log('Action clicked:', event.action);
        // Handle custom actions
        handleNotificationAction(event.action, event.notification.data);
    } else {
        // Default click behavior
        const urlToOpen = event.notification.data?.url || '/';
        
        event.waitUntil(
            self.clients.matchAll({
                type: 'window',
                includeUncontrolled: true
            }).then(function(clientList) {
                // Check if there's already a window/tab open with the target URL
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // If no window/tab is open, open a new one
                if (self.clients.openWindow) {
                    return self.clients.openWindow(urlToOpen);
                }
            })
        );
    }
});

self.addEventListener('notificationclose', function(event) {
    console.log('Notification closed:', event);
    
    // You can send analytics data here
    if (event.notification.data?.analytics) {
        // Send analytics data to your server
        sendAnalytics('notification_closed', event.notification.data);
    }
});

// Handle custom notification actions
function handleNotificationAction(action, data) {
    switch (action) {
        case 'view':
            if (data?.url) {
                self.clients.openWindow(data.url);
            }
            break;
        case 'dismiss':
            // Just close the notification (already done)
            break;
        default:
            console.log('Unknown action:', action);
            break;
    }
}

// Send analytics data
function sendAnalytics(event, data) {
    // Implement your analytics tracking here
    console.log('Analytics:', event, data);
}

// Handle background sync
self.addEventListener('sync', function(event) {
    console.log('Background sync:', event);
    
    if (event.tag === 'laravelwudel-notif-sync') {
        event.waitUntil(syncPushNotifications());
    }
});

async function syncPushNotifications() {
    try {
        // Implement background sync logic here
        console.log('Syncing push notifications...');
        
        // You can fetch pending notifications or sync data here
        const response = await fetch('/api/push/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        
        if (response.ok) {
            console.log('Push notifications synced successfully');
        }
    } catch (error) {
        console.error('Error syncing push notifications:', error);
    }
}
