<?php
// config.php - Configuração da aplicação
session_start();

// Configurações da base de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management');

// Configurações da aplicação
// Detectar automaticamente o caminho base do projeto
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('\\', '/', dirname($script));
// Se estiver numa subpasta (ex: admin), voltar ao diretório raiz
if (basename($base_path) === 'admin' || basename($base_path) === 'includes') {
    $base_path = dirname($base_path);
}
define('SITE_URL', $protocol . '://' . $host . $base_path);
define('SITE_NAME', 'Sistema de Gestão de Eventos');

// Conexão à base de dados
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Funções auxiliares
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price)
{
    return number_format($price, 2, ',', '.') . ' €';
}

function formatDate($date)
{
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

function formatDateTime($date, $time)
{
    $datetime = $date . ' ' . $time;
    $timestamp = strtotime($datetime);
    return date('d/m/Y \à\s H:i', $timestamp);
}

function showAlert($message, $type = 'success')
{
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert()
{
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">';
        echo $alert['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}

// Função para upload de imagens
function uploadImage($file, $folder = 'uploads/events/')
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return false;
    }

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $newname = uniqid() . '.' . $ext;
    $destination = $folder . $newname;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $newname;
    }

    return false;
}
