<?php
session_start();
session_unset(); // Limpa todas as variáveis da sessão
session_destroy(); // Destroi a sessão

// Redireciona para a página de login
header("Location: ../../index.php");
exit();
?>
