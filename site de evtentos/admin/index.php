<?php
require_once '../config.php';
requireAdmin();

$page_title = "Dashboard Admin";

// Estatísticas gerais
$stats = [];

// Total de eventos
$stmt = $conn->query("SELECT COUNT(*) as count FROM events");
$stats['total_events'] = $stmt->fetch()['count'];

// Eventos ativos
$stmt = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active' AND event_date >= CURDATE()");
$stats['active_events'] = $stmt->fetch()['count'];

// Total de utilizadores
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stats['total_users'] = $stmt->fetch()['count'];

// Total de compras
$stmt = $conn->query("SELECT COUNT(*) as count, SUM(total_price) as total FROM purchases");
$purchases_data = $stmt->fetch();
$stats['total_purchases'] = $purchases_data['count'];
$stats['total_revenue'] = $purchases_data['total'] ?? 0;

// Total de bilhetes vendidos
$stmt = $conn->query("SELECT SUM(quantity) as total FROM purchases");
$stats['total_tickets_sold'] = $stmt->fetch()['total'] ?? 0;

// Eventos recentes
$stmt = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
$recent_events = $stmt->fetchAll();

// Compras recentes
$stmt = $conn->query("
    SELECT p.*, u.name as user_name, e.title as event_title
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN events e ON p.event_id = e.id
    ORDER BY p.purchase_date DESC
    LIMIT 5
");
$recent_purchases = $stmt->fetchAll();

// Novos utilizadores
$stmt = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Admin</h1>
        <div>
            <a href="events.php" class="btn btn-primary me-2">
                <i class="bi bi-calendar-plus"></i> Novo Evento
            </a>
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Ver Site
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total de Eventos</h6>
                            <h2 class="fw-bold mb-0"><?php echo $stats['total_events']; ?></h2>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> <?php echo $stats['active_events']; ?> ativos
                            </small>
                        </div>
                        <i class="bi bi-calendar-event text-primary stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card success border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Total de Utilizadores</h6>
                            <h2 class="fw-bold mb-0"><?php echo $stats['total_users']; ?></h2>
                            <small class="text-success">
                                <i class="bi bi-person-plus"></i> Registados
                            </small>
                        </div>
                        <i class="bi bi-people text-success stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card warning border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Bilhetes Vendidos</h6>
                            <h2 class="fw-bold mb-0"><?php echo $stats['total_tickets_sold']; ?></h2>
                            <small class="text-warning">
                                <i class="bi bi-ticket-perforated"></i> Total
                            </small>
                        </div>
                        <i class="bi bi-ticket-perforated text-warning stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card danger border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Receita Total</h6>
                            <h2 class="fw-bold mb-0"><?php echo formatPrice($stats['total_revenue']); ?></h2>
                            <small class="text-danger">
                                <i class="bi bi-graph-up"></i> <?php echo $stats['total_purchases']; ?> vendas
                            </small>
                        </div>
                        <i class="bi bi-cash text-danger stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Eventos Recentes -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event"></i> Eventos Recentes</h5>
                    <a href="events.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Evento</th>
                                    <th>Data</th>
                                    <th>Local</th>
                                    <th>Preço</th>
                                    <th>Bilhetes</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_events as $event): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                        <td><small><?php echo formatDate($event['event_date']); ?></small></td>
                                        <td><small><?php echo htmlspecialchars(substr($event['location'], 0, 20)); ?></small></td>
                                        <td><?php echo formatPrice($event['ticket_price']); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $event['available_tickets']; ?></span></td>
                                        <td>
                                            <?php
                                            $badge = $event['status'] === 'active' ? 'success' : 'secondary';
                                            echo '<span class="badge bg-' . $badge . '">' . ucfirst($event['status']) . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="event_edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compras Recentes -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-receipt"></i> Compras Recentes</h5>
                    <a href="purchases.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body">
                    <?php foreach ($recent_purchases as $purchase): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="small"><?php echo htmlspecialchars($purchase['user_name']); ?></strong>
                                <span class="badge bg-success small"><?php echo formatPrice($purchase['total_price']); ?></span>
                            </div>
                            <small class="text-muted d-block">
                                <?php echo htmlspecialchars(substr($purchase['event_title'], 0, 30)); ?>...
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($purchase['purchase_date'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Novos Utilizadores -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people"></i> Novos Utilizadores</h5>
                    <a href="users.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Localidade</th>
                                    <th>Data de Registo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                                        <td><small><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></small></td>
                                        <td>
                                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>