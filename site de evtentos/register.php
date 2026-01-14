<?php
require_once 'config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$page_title = "Criar Conta";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validações
    if (empty($name)) $errors[] = 'O nome é obrigatório';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';
    if (empty($password) || strlen($password) < 6) $errors[] = 'A password deve ter no mínimo 6 caracteres';
    if ($password !== $confirm_password) $errors[] = 'As passwords não coincidem';

    // Verificar se email já existe
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email já está registado';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");

        if ($stmt->execute([$name, $email, $hashed_password, $phone, $address])) {
            showAlert('Conta criada com sucesso! Faça login para continuar.', 'success');
            header('Location: login.php');
            exit();
        } else {
            $errors[] = 'Erro ao criar conta. Tente novamente.';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="auth-form" style="max-width: 600px;">
        <h2><i class="bi bi-person-plus"></i> Criar Nova Conta</h2>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="name" class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" id="name" name="name" required
                        placeholder="Seu nome completo" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <div class="invalid-feedback">Por favor, insira seu nome.</div>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        placeholder="seu@email.pt" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="invalid-feedback">Por favor, insira um email válido.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        placeholder="912345678" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label">Localidade</label>
                    <input type="text" class="form-control" id="address" name="address"
                        placeholder="Lisboa, Portugal" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required
                            placeholder="Mínimo 6 caracteres" minlength="6">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">A password deve ter no mínimo 6 caracteres.</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                        placeholder="Repita a password" minlength="6">
                    <div class="invalid-feedback">As passwords devem coincidir.</div>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">
                    Aceito os <a href="#">termos e condições</a>
                </label>
                <div class="invalid-feedback">Você deve aceitar os termos.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-person-plus"></i> Criar Conta
            </button>
        </form>

        <hr class="my-4">

        <div class="text-center">
            <p class="mb-2">Já tem uma conta?</p>
            <a href="login.php" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-in-right"></i> Fazer Login
            </a>
        </div>
    </div>
</div>

<script>
    // Validação customizada de password
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;

        if (password !== confirmPassword) {
            this.setCustomValidity('As passwords não coincidem');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>