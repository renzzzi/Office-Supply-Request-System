const bell = document.getElementById('notification-bell');
const dropdown = document.getElementById('notification-dropdown');
const badge = document.getElementById('notification-badge');
const notificationList = document.getElementById('notification-list');

const fetchNotifications = async () => {
    try {
        const response = await fetch('../api/get-notifications.php');
        const data = await response.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
        }

        notificationList.innerHTML = '';
        if (data.notifications.length > 0) {
            data.notifications.forEach(notif => {
                const item = document.createElement('a');
                item.href = notif.link ? `../${notif.link}` : '#';
                item.classList.add('notification-item');
                if (notif.is_read == 0) {
                    item.classList.add('unread');
                }
                item.innerHTML = `
                    <div>
                        <p>${notif.message}</p>
                        <small>${new Date(notif.created_at).toLocaleString()}</small>
                    </div>
                `;
                notificationList.appendChild(item);
            });
        } else {
            notificationList.innerHTML = '<div class="notification-item-placeholder">No notifications yet.</div>';
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
        notificationList.innerHTML = '<div class="notification-item-placeholder">Could not load notifications.</div>';
    }
};

const markAsRead = async () => {
    try {
        await fetch('../api/mark-notifications-as-read.php', { method: 'POST' });
        badge.classList.remove('show');
    } catch (error) {
        console.error('Failed to mark notifications as read:', error);
    }
};

document.body.addEventListener('click', (event) => {
    const notificationContainer = event.target.closest('.notification-container');
    
    if (notificationContainer) {
        const isBellClick = event.target.closest('#notification-bell');
        if (isBellClick) {
            const isVisible = dropdown.classList.toggle('show');
            if (isVisible && badge.classList.contains('show')) {
                setTimeout(markAsRead, 1500);
            }
        }
    } else {
        dropdown.classList.remove('show');
    }
});

fetchNotifications();
setInterval(fetchNotifications, 60000);