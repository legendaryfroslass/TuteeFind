document.addEventListener('DOMContentLoaded', function() {
    // Select all elements with the class 'notification-item'
    const notificationItems = document.querySelectorAll('#notification-list .notification-item');
    
    // Get the total count of notification items
    const notificationCount = notificationItems.length;

    console.log("Total notifications:", notificationCount);
});

document.addEventListener('DOMContentLoaded', function() {
    const notificationCountElement = document.getElementById('notif-count');
    
    // Example function to fetch and update notification count
    function updateNotificationCount() {
        fetch('/path/to/notification/count/api') // Replace with your API endpoint
            .then(response => response.json())
            .then(data => {
                notificationCountElement.textContent = data.unreadCount; // Update badge text
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }

    updateNotificationCount();
});