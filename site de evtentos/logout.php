<?php
require_once 'config.php';

// Destruir sessão
session_destroy();

// Redirecionar para página inicial
header('Location: index.php');
exit();
