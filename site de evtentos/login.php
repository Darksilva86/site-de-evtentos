<?php
require_once 'config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$page_title = "Login";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        showAlert('Por favor, preencha todos os campos', 'danger');
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            showAlert('Login realizado com sucesso!', 'success');

            // Redirecionar baseado no papel do utilizador
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            showAlert('Email ou password incorretos', 'danger');
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="auth-form">
        <h2><i class="bi bi-box-arrow-in-right"></i> Entrar na Conta</h2>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required
                        placeholder="seu@email.pt" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="invalid-feedback">Por favor, insira um email válido.</div>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required
                        placeholder="Sua password">
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="bi bi-eye"></i>
                    </button>
                    <div class="invalid-feedback">Por favor, insira sua password.</div>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Lembrar-me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="mb-2">Ainda não tem conta?</p>
            <a href="register.php" class="btn btn-outline-primary">
                <i class="bi bi-person-plus"></i> Criar Nova Conta
            </a>
        </div>

        <div class="mt-3 text-center">
            <small class="text-muted">
                <strong>Contas de teste:</strong><br>
                Admin: admin@eventos.pt | admin123<br>
                Utilizador: joao@email.pt | admin123
            </small>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>