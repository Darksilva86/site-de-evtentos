<?php
require_once '../config.php';
requireAdmin();

$page_title = "Gestão de Compras";

// Atualizar status da compra
if (isset($_POST['update_status'])) {
    $purchase_id = (int)$_POST['purchase_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE purchases SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $purchase_id])) {
        showAlert('Status atualizado com sucesso!', 'success');
    }
    header('Location: purchases.php');
    exit();
}

// Buscar compras
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "
    SELECT p.*, u.name as user_name, u.email as user_email, e.title as event_title
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN events e ON p.event_id = e.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR e.title LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($status)) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY p.purchase_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Estatísticas
$stmt = $conn->query("SELECT COUNT(*) as count, SUM(total_price) as total, SUM(quantity) as tickets FROM purchases");
$stats = $stmt->fetch();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-receipt"></i> Gestão de Compras</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-bag-check text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['count']; ?></h3>
                    <small class="text-muted">Total de Compras</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-ticket text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo $stats['tickets']; ?></h3>
                    <small class="text-muted">Bilhetes Vendidos</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-cash text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2"><?php echo formatPrice($stats['total']); ?></h3>
                    <small class="text-muted">Receita Total</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-7">
                    <input type="text" name="search" class="form-control" placeholder="Pesquisar por utilizador ou evento..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os Status</option>
                        <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Pesquisar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Total: <?php echo count($purchases); ?> compra(s)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Evento</th>
                            <th>Quantidade</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><strong>#<?php echo $purchase['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($purchase['user_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($purchase['user_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($purchase['event_title']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $purchase['quantity']; ?></span></td>
                                <td><strong><?php echo formatPrice($purchase['total_price']); ?></strong></td>
                                <td><small><?php echo htmlspecialchars($purchase['payment_method']); ?></small></td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($purchase['purchase_date'])); ?></small></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="purchase_id" value="<?php echo $purchase['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="confirmed" <?php echo $purchase['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmada</option>
                                            <option value="pending" <?php echo $purchase['status'] == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="cancelled" <?php echo $purchase['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                        <button type="submit" name="update_status" class="d-none"></button>
                                    </form>
                                </td>
                                <td>
                                    <a href="purchase_detail.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
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