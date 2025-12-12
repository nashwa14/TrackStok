// Universal Notification System for Kingland

// Load notifications
function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            
            // Update badge
            if (data.unread_count > 0) {
                badge.classList.remove('hidden');
                badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
            } else {
                badge.classList.add('hidden');
            }
            
            // Update notification panel if exists
            const panelList = document.getElementById('notificationList');
            if (panelList && data.notifications) {
                let html = '';
                
                if (data.notifications.length === 0) {
                    html = '<div class="text-center text-gray-500 py-4">Tidak ada notifikasi</div>';
                } else {
                    data.notifications.forEach(notif => {
                        const unreadClass = !notif.is_read ? 'notification-item unread' : 'notification-item';
                        html += `
                            <div class="${unreadClass}" onclick="handleNotificationClick('${notif.link}', ${notif.id})">
                                <i class="fas ${notif.icon} text-xl" style="color: ${notif.warna}"></i>
                                <div class="flex-1">
                                    <p class="font-bold text-black text-sm leading-tight">${notif.judul}</p>
                                    <p class="text-gray-700 text-xs leading-tight">${notif.pesan}</p>
                                    <p class="text-gray-500 text-xs mt-1">${notif.waktu}</p>
                                </div>
                            </div>
                        `;
                    });
                }
                
                panelList.innerHTML = html;
            }
            
            // Update dashboard notifications if exists
            const dashboardList = document.getElementById('dashboardNotifications');
            if (dashboardList && data.notifications) {
                let html = '';
                data.notifications.slice(0, 3).forEach((notif, index) => {
                    const bgClass = index === 0 && !notif.is_read ? 'bg-blue-50' : '';
                    html += `
                        <div class="${bgClass} rounded-md p-3 flex space-x-3 items-start cursor-pointer hover:bg-gray-50 transition" onclick="handleNotificationClick('${notif.link}', ${notif.id})">
                            <i class="fas ${notif.icon} text-xl" style="color: ${notif.warna}"></i>
                            <div class="text-xs md:text-sm flex-1">
                                <p class="font-bold text-black leading-tight">${notif.judul}</p>
                                <p class="text-gray-700 leading-tight">${notif.pesan}</p>
                                <p class="text-gray-500 text-xs mt-1">${notif.waktu}</p>
                            </div>
                        </div>
                    `;
                });
                dashboardList.innerHTML = html || '<div class="text-center text-gray-500 py-4">Tidak ada notifikasi</div>';
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Handle notification click
function handleNotificationClick(link, notifId) {
    // Mark as read
    fetch(`mark_notification_read.php?id=${notifId}`, { method: 'POST' })
        .then(() => {
            if (link) {
                window.location.href = link;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Toggle notification panel
function setupNotificationPanel() {
    const bell = document.getElementById('notificationBell');
    const panel = document.getElementById('notificationPanel');
    
    if (bell && panel) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            panel.classList.toggle('hidden');
            loadNotifications();
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(event) {
            if (!panel.contains(event.target) && !bell.contains(event.target)) {
                panel.classList.add('hidden');
            }
        });
    }
}

// Mark all as read
function markAllRead() {
    fetch('mark_notifications_read.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    setupNotificationPanel();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});

// Export functions for global use
window.loadNotifications = loadNotifications;
window.markAllRead = markAllRead;
window.handleNotificationClick = handleNotificationClick;