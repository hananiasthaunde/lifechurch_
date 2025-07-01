<?php
session_start();

// Destrói todos os dados da sessão
session_destroy();

// Redireciona para a página de login
header('Location: ../public/login.php');
exit;
?>