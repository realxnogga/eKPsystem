const notificationIcon = document.querySelector('.notification-icon');
const notificationDropdown = document.querySelector('.notification-dropdown');
const closeBtn = document.querySelector('.close-button');

notificationIcon.addEventListener('click', () => {
    notificationDropdown.style.display = 'block';
});

closeBtn.addEventListener('click', () => {
    notificationDropdown.style.display = 'none';
});

window.addEventListener('click', (event) => {
    if (!notificationIcon.contains(event.target) && !notificationDropdown.contains(event.target)) {
        notificationDropdown.style.display = 'none';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const notificationButton = document.getElementById("notification-button");
    const notificationDropdown = document.getElementById("notification-dropdown");

    // Toggle visibility of the notification bar
    notificationButton.addEventListener("click", function (event) {
        event.stopPropagation();
        notificationDropdown.classList.toggle("show");
    });

    // Close the notification bar when clicking outside
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".notification-container")) {
            notificationDropdown.classList.remove("show");
        }
    });
});
