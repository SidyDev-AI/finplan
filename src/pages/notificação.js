// Toggle dark/light mode
const toggleBtn = document.getElementById('toggle-theme');

toggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('light-mode');
  document.body.classList.toggle('dark-mode');
});

// Show message on user click
function showMessage(index) {
  const messages = document.querySelectorAll('.message-box');
  messages.forEach((msg, i) => {
    msg.classList.toggle('active', i === index);
  });
}

// Toggle notification popup
function toggleNotifications() {
  const popup = document.getElementById("notification-popup");
  popup.classList.toggle("hidden");
}

// Remove individual notification
function removeNotification(el) {
  el.parentElement.remove();
}

// Fecha o popup ao clicar fora
document.addEventListener("click", function (event) {
  const popup = document.getElementById("notification-popup");
  const bell = document.querySelector(".notification-bell");

  if (!popup.contains(event.target) && !bell.contains(event.target)) {
    popup.classList.add("hidden");
  }
});
