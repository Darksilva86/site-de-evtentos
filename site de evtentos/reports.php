<?php

/**
 * Página de Exemplo: Relatórios usando Views
 * Demonstra como usar as views criadas em páginas PHP
 */

require_once 'config.php';

// Verificar se está autenticado e é admin
requireLogin();
requireAdmin();

$page_title = "Relatórios e Estatísticas";

// Buscar dados do dashboard
$dashboard = $conn->query("SELECT * FROM admin_dashboard")->fetch();

// Buscar eventos populares
$stmt = $conn->query("SELECT * FROM popular_events LIMIT 5");
$popular_events = $stmt->fetchAll();

// Buscar top clientes
$stmt = $conn->query("
    SELECT * FROM customer_sales_summary 
    WHERE customer_tier IN ('VIP', 'Premium', 'Regular')
    ORDER BY total_spent DESC 
    LIMIT 10
");
$top_customers = $stmt->fetchAll();

// Buscar receita mensal (últimos 6 meses)
$stmt = $conn->query("
    SELECT * FROM monthly_revenue 
    ORDER BY year DESC, month DESC 
    LIMIT 6
");
$monthly_revenue = $stmt->fetchAll();

// Buscar eventos com stock crítico
$stmt = $conn->query("
    SELECT * FROM tickets_status 
    WHERE stock_status IN ('CRÍTICO', 'ESGOTADO')
    AND event_date >= CURDATE()
    ORDER BY event_date
");
$critical_stock = $stmt->fetchAll();

// Buscar próximos eventos
$stmt = $conn->query("SELECT * FROM upcoming_events LIMIT 5");
$upcoming = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="fw-bold"><i class="bi bi-graph-up"></i> Relatórios e Estatísticas</h1>
            <p class="text-muted">Análise completa do sistema de gestão de eventos</p>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-calendar-event"></i> Eventos Ativos</h6>
                    <h2 class="mb-0"><?php echo $dashboard['total_active_events']; ?></h2>
                    <small><?php echo $dashboard['upcoming_events_count']; ?> próximos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-people"></i> Clientes</h6>
                    <h2 class="mb-0"><?php echo $dashboard['total_customers']; ?></h2>
                    <small>Total registados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-ticket"></i> Bilhetes Vendidos</h6>
                    <h2 class="mb-0"><?php echo number_format($dashboard['total_tickets_sold']); ?></h2>
                    <small><?php echo number_format($dashboard['total_tickets_available']); ?> disponíveis</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-currency-euro"></i> Receita Total</h6>
                    <h2 class="mb-0"><?php echo formatPrice($dashboard['total_revenue']); ?></h2>
                    <small>Mensal: <?php echo formatPrice($dashboard['monthly_revenue']); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Eventos Populares -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-star"></i> Top 5 Eventos Mais Populares</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($popular_events)): ?>
                        <p class="text-muted">Ainda não há vendas registadas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Evento</th>
                                        <th>Bilhetes</th>
                                        <th>Receita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_events as $event): ?>
                                        <tr>
                                            <td><?php echo $event['popularity_rank']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo formatDate($event['event_date']); ?></small>
                                            </td>
                                            <td><span class="badge bg-info"><?php echo $event['tickets_sold']; ?></span></td>
                                            <td><strong><?php echo formatPrice($event['revenue']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Clientes -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Clientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_customers)): ?>
                        <p class="text-muted">Ainda não há clientes com compras.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Tier</th>
                                        <th>Gasto</th>
                                        <th>Eventos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_customers as $customer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'VIP' => 'bg-danger',
                                                    'Premium' => 'bg-warning text-dark',
                                                    'Regular' => 'bg-info'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badge_class[$customer['customer_tier']] ?? 'bg-secondary'; ?>">
                                                    <?php echo $customer['customer_tier']; ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo formatPrice($customer['total_spent']); ?></strong></td>
                                            <td><?php echo $customer['events_attended']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Receita Mensal -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Receita Mensal (Últimos 6 Meses)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($monthly_revenue)): ?>
                        <p class="text-muted">Ainda não há dados de receita.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mês</th>
                                        <th>Compras</th>
                                        <th>Clientes</th>
                                        <th>Bilhetes</th>
                                        <th>Receita</th>
                                        <th>Média/Compra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_revenue as $month): ?>
                                        <tr>
                                            <td><strong><?php echo $month['month_name']; ?></strong></td>
                                            <td><?php echo $month['total_purchases']; ?></td>
                                            <td><?php echo $month['unique_customers']; ?></td>
                                            <td><?php echo $month['total_tickets']; ?></td>
                                            <td><strong class="text-success"><?php echo formatPrice($month['total_revenue']); ?></strong></td>
                                            <td><?php echo formatPrice($month['avg_purchase_value']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Stock -->
    <?php if (!empty($critical_stock)): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Alertas de Stock Crítico</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Evento</th>
                                        <th>Data</th>
                                        <th>Disponíveis</th>
                                        <th>Vendidos</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($critical_stock as $event): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($event['event_name']); ?></strong></td>
                                            <td><?php echo formatDate($event['event_date']); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo $event['tickets_available']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $event['tickets_sold']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $event['stock_status'] == 'ESGOTADO' ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                                    <?php echo $event['stock_status']; ?>
                                                </span>
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
    <?php endif; ?>

    <!-- Próximos Eventos -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Próximos Eventos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcoming)): ?>
                        <p class="text-muted">Não há eventos próximos agendados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Evento</th>
                                        <th>Data/Hora</th>
                                        <th>Local</th>
                                        <th>Urgência</th>
                                        <th>Disponíveis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming as $event): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                            <td>
                                                <?php echo formatDateTime($event['event_date'], $event['event_time']); ?><br>
                                                <small class="text-muted">(em <?php echo $event['days_until']; ?> dias)</small>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                                            <td>
                                                <?php
                                                $urgency_class = [
                                                    'HOJE' => 'bg-danger',
                                                    'AMANHÃ' => 'bg-warning text-dark',
                                                    'ESTA SEMANA' => 'bg-info',
                                                    'ESTE MÊS' => 'bg-primary',
                                                    'PRÓXIMO' => 'bg-secondary'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $urgency_class[$event['urgency_label']] ?? 'bg-secondary'; ?>">
                                                    <?php echo $event['urgency_label']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $event['available_tickets']; ?> bilhetes</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações -->
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="admin/index.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel Admin
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer"></i> Imprimir Relatório
            </button>
        </div>
    </div>
</div>

<style>
    @media print {

        .btn,
        .card-header {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>