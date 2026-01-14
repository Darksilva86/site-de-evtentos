<?php
require_once '../config.php';
requireAdmin();

$page_title = "Gestão de Utilizadores";

// Excluir utilizador
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        if ($stmt->execute([$user_id])) {
            showAlert('Utilizador excluído com sucesso!', 'success');
        }
    } else {
        showAlert('Não é possível excluir seu próprio utilizador', 'warning');
    }
    header('Location: users.php');
    exit();
}

// Buscar utilizadores
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-people"></i> Gestão de Utilizadores</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Pesquisar por nome ou email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Pesquisar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Total: <?php echo count($users); ?> utilizador(es)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Localidade</th>
                            <th>Compras</th>
                            <th>Gasto Total</th>
                            <th>Registo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user):
                            $stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(total_price) as total FROM purchases WHERE user_id = ?");
                            $stmt->execute([$user['id']]);
                            $user_stats = $stmt->fetch();
                        ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['address']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $user_stats['count']; ?></span></td>
                                <td><strong><?php echo formatPrice($user_stats['total'] ?? 0); ?></strong></td>
                                <td><small><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small></td>
                                <td>
                                    <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>