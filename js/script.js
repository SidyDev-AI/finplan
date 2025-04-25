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
