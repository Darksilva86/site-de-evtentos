<?php
require_once 'config.php';
requireLogin();

$page_title = "Minhas Compras";

// Buscar compras do utilizador
$stmt = $conn->prepare("
    SELECT p.*, e.title, e.description, e.event_date, e.event_time, e.location, e.image
    FROM purchases p
    JOIN events e ON p.event_id = e.id
    WHERE p.user_id = ?
    ORDER BY p.purchase_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();

// Calcular estatísticas
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_purchases,
        SUM(total_price) as total_spent,
        SUM(quantity) as total_tickets
    FROM purchases 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4"><i class="bi bi-receipt"></i> Minhas Compras</h1>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-bag-check text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_purchases'] ?? 0; ?></h3>
                    <small class="text-muted">Total de Compras</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-ticket-perforated text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo $stats['total_tickets'] ?? 0; ?></h3>
                    <small class="text-muted">Bilhetes Comprados</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-cash text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo formatPrice($stats['total_spent'] ?? 0); ?></h3>
                    <small class="text-muted">Total Gasto</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Compras -->
    <?php if (empty($purchases)): ?>
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h3>Nenhuma compra realizada</h3>
            <p>Você ainda não comprou bilhetes. Explore nossos eventos!</p>
            <a href="events.php" class="btn btn-primary">
                <i class="bi bi-calendar-event"></i> Ver Eventos
            </a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Evento</th>
                                <th>Data do Evento</th>
                                <th>Quantidade</th>
                                <th>Total</th>
                                <th>Pagamento</th>
                                <th>Status</th>
                                <th>Data da Compra</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td><strong>#<?php echo $purchase['id']; ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($purchase['image']): ?>
                                                <img src="uploads/events/<?php echo $purchase['image']; ?>"
                                                    class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;"
                                                    alt="<?php echo htmlspecialchars($purchase['title']); ?>">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($purchase['title']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($purchase['location']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo formatDateTime($purchase['event_date'], $purchase['event_time']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $purchase['quantity']; ?> bilhete(s)</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?php echo formatPrice($purchase['total_price']); ?></strong>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($purchase['payment_method'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        switch ($purchase['status']) {
                                            case 'confirmed':
                                                $badge_class = 'bg-success';
                                                $status_text = 'Confirmada';
                                                break;
                                            case 'pending':
                                                $badge_class = 'bg-warning';
                                                $status_text = 'Pendente';
                                                break;
                                            case 'cancelled':
                                                $badge_class = 'bg-danger';
                                                $status_text = 'Cancelada';
                                                break;
                                            default:
                                                $badge_class = 'bg-secondary';
                                                $status_text = $purchase['status'];
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($purchase['purchase_date'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="purchase_detail.php?id=<?php echo $purchase['id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>