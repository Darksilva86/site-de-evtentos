<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Buscar utilizador
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    showAlert('Utilizador não encontrado', 'danger');
    header('Location: users.php');
    exit();
}

$page_title = "Editar Utilizador";

// Atualizar utilizador
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $role = $_POST['role'];

    $errors = [];

    if (empty($name)) $errors[] = 'O nome é obrigatório';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';

    // Verificar se email já existe (exceto o próprio)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email já está em uso';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $address, $role, $user_id])) {
            showAlert('Utilizador atualizado com sucesso!', 'success');
            header('Location: users.php');
            exit();
        } else {
            $errors[] = 'Erro ao atualizar utilizador';
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
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (strlen($new_password) < 6) {
        $errors[] = 'A password deve ter no mínimo 6 caracteres';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'As passwords não coincidem';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            showAlert('Password alterada com sucesso!', 'success');
            header('Location: user_edit.php?id=' . $user_id);
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

// Buscar estatísticas do utilizador
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_purchases,
        SUM(total_price) as total_spent,
        SUM(quantity) as total_tickets
    FROM purchases 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user_stats = $stmt->fetch();

include '../includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="users.php">Utilizadores</a></li>
            <li class="breadcrumb-item active">Editar Utilizador</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-person-gear"></i> Editar Utilizador</h1>
        <a href="users.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Dados Pessoais -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Dados Pessoais</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
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

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Papel *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Utilizador</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="update_user" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>

            <!-- Alterar Password -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Alterar Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nova Password *</label>
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    minlength="6" required>
                                <small class="text-muted">Mínimo de 6 caracteres</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    minlength="6" required>
                            </div>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="bi bi-shield-lock"></i> Alterar Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Estatísticas -->
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Estatísticas do Utilizador</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total de Compras</small>
                        <h4 class="mb-0"><?php echo $user_stats['total_purchases'] ?? 0; ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Bilhetes Comprados</small>
                        <h4 class="mb-0"><?php echo $user_stats['total_tickets'] ?? 0; ?></h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Gasto</small>
                        <h4 class="mb-0 text-primary"><?php echo formatPrice($user_stats['total_spent'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>

            <!-- Informações -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Informações</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Conta criada em:</small>
                    <p class="mb-3"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>

                    <small class="text-muted d-block mb-1">Última atualização:</small>
                    <p class="mb-3"><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></p>

                    <a href="../profile.php" class="btn btn-sm btn-outline-primary w-100 mb-2" target="_blank">
                        <i class="bi bi-eye"></i> Ver como Utilizador
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>