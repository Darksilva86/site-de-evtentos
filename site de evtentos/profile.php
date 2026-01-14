<?php
require_once 'config.php';
requireLogin();

$page_title = "Meu Perfil";

// Buscar dados do utilizador
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Atualizar perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

    $errors = [];

    if (empty($name)) $errors[] = 'O nome é obrigatório';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';

    // Verificar se email já existe (exceto o próprio)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email já está em uso';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $address, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            showAlert('Perfil atualizado com sucesso!', 'success');
            header('Location: profile.php');
            exit();
        } else {
            $errors[] = 'Erro ao atualizar perfil';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

// Alterar password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (!password_verify($current_password, $user['password'])) {
        $errors[] = 'Password atual incorreta';
    }

    if (strlen($new_password) < 6) {
        $errors[] = 'A nova password deve ter no mínimo 6 caracteres';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'As passwords não coincidem';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            showAlert('Password alterada com sucesso!', 'success');
            header('Location: profile.php');
            exit();
        } else {
            $errors[] = 'Erro ao alterar password';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

// Buscar estatísticas recentes
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(total_price) as total
    FROM purchases 
    WHERE user_id = ? AND purchase_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stmt->execute([$_SESSION['user_id']]);
$recent_stats = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4"><i class="bi bi-person-circle"></i> Meu Perfil</h1>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h5 class="text-center fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                <p class="text-center text-muted small mb-3"><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="list-group">
                    <a href="#dados" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="bi bi-person"></i> Dados Pessoais
                    </a>
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="bi bi-lock"></i> Alterar Password
                    </a>
                    <a href="purchases.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-receipt"></i> Minhas Compras
                    </a>
                </div>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2">Últimos 30 dias</h6>
                    <p class="mb-1"><strong><?php echo $recent_stats['count'] ?? 0; ?></strong> compras</p>
                    <p class="mb-0"><strong><?php echo formatPrice($recent_stats['total'] ?? 0); ?></strong> gastos</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Dados Pessoais -->
                <div class="tab-pane fade show active" id="dados">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person"></i> Dados Pessoais</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        <div class="invalid-feedback">Por favor, insira seu nome.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <div class="invalid-feedback">Por favor, insira um email válido.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            value="<?php echo htmlspecialchars($user['phone']); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Localidade</label>
                                        <input type="text" class="form-control" id="address" name="address"
                                            value="<?php echo htmlspecialchars($user['address']); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <p class="text-muted small mb-0">
                                        Conta criada em: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                    </p>
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Salvar Alterações
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Alterar Password -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-lock"></i> Alterar Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Atual *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <div class="invalid-feedback">Por favor, insira sua password atual.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nova Password *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        minlength="6" required>
                                    <small class="text-muted">Mínimo de 6 caracteres</small>
                                    <div class="invalid-feedback">A password deve ter no mínimo 6 caracteres.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nova Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                        minlength="6" required>
                                    <div class="invalid-feedback">As passwords devem coincidir.</div>
                                </div>

                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="bi bi-shield-lock"></i> Alterar Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Validação de password
    document.getElementById('confirm_password')?.addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;

        if (newPassword !== confirmPassword) {
            this.setCustomValidity('As passwords não coincidem');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>