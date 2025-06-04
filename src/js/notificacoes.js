document.querySelector('.toggle-track').addEventListener('click', function() {
  const thumb = document.querySelector('.toggle-thumb');
  const body = document.body;
  if (thumb.style.right === '3px') {
    thumb.style.right = '33px';
    body.classList.add('light-theme');
  } else {
    thumb.style.right = '3px';
    body.classList.remove('light-theme');
  }
});

// Notificações
const notificationIcon = document.getElementById('notificationIcon');
const notificationPanel = document.getElementById('notificationPanel');
    
// Abrir/fechar painel de notificações
notificationIcon.addEventListener('click', function(e) {
  e.stopPropagation();
  notificationPanel.style.display = notificationPanel.style.display === 'block' ? 'none' : 'block';
});
    
// Fechar painel ao clicar fora
document.addEventListener('click', function(e) {
  if (!notificationPanel.contains(e.target) && e.target !== notificationIcon) {
    notificationPanel.style.display = 'none';
  }
});
    
// Fechar notificação individual
const closeButtons = document.querySelectorAll('.notification-close');
closeButtons.forEach(button => {
  button.addEventListener('click', function() {
    const notificationId = this.getAttribute('data-id');
    document.getElementById('deleteNotificationId').value = notificationId;
    document.getElementById('deleteNotificationForm').submit();
  });
});
    
// Inicialização dos valores padrões
window.addEventListener('DOMContentLoaded', () => {
  if (installmentValue.value === 'no') {
    installmentCountWrapper.style.display = 'none';
  }
});