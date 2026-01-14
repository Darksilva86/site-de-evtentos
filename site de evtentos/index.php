<?php
require_once 'config.php';

$page_title = "Início";

// Buscar eventos recentes (próximos eventos)
$stmt = $conn->prepare("
    SELECT * FROM events 
    WHERE status = 'active' 
    AND event_date >= CURDATE() 
    ORDER BY event_date ASC 
    LIMIT 6
");
$stmt->execute();
$recent_events = $stmt->fetchAll();

// Estatísticas
$stmt = $conn->query("SELECT COUNT(*) as total FROM events WHERE status = 'active'");
$total_events = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM purchases");
$total_purchases = $stmt->fetch()['total'];

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-3 fw-bold">Bem-vindo ao Sistema de Gestão de Eventos</h1>
        <p class="lead">Descubra e reserve bilhetes para os melhores eventos em Portugal</p>
        <div class="mt-4">
            <a href="events.php" class="btn btn-light btn-lg me-3">
                <i class="bi bi-calendar-event"></i> Ver Eventos
            </a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-person-plus"></i> Criar Conta
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Search Box -->
<div class="container">
    <div class="search-box">
        <form action="events.php" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-lg" placeholder="Pesquisar eventos...">
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-lg" placeholder="Data">
            </div>
            <div class="col-md-2">
                <select name="order" class="form-select form-select-lg">
                    <option value="">Ordenar por</option>
                    <option value="date_asc">Data (crescente)</option>
                    <option value="date_desc">Data (decrescente)</option>
                    <option value="price_asc">Preço (menor)</option>
                    <option value="price_desc">Preço (maior)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-search"></i> Pesquisar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
<section class="py-5 bg-light mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 fw-bold"><?php echo $total_events; ?></h2>
                        <p class="text-muted mb-0">Eventos Ativos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 fw-bold"><?php echo $total_users; ?></h2>
                        <p class="text-muted mb-0">Utilizadores Registados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-ticket-perforated text-warning" style="font-size: 3rem;"></i>
                        <h2 class="mt-3 fw-bold"><?php echo $total_purchases; ?></h2>
                        <p class="text-muted mb-0">Bilhetes Vendidos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Próximos Eventos -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Próximos Eventos</h2>
            <a href="events.php" class="btn btn-outline-primary">Ver Todos <i class="bi bi-arrow-right"></i></a>
        </div>

        <?php if (empty($recent_events)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h3>Nenhum evento disponível</h3>
                <p>Não há eventos programados no momento. Volte em breve!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recent_events as $event): ?>
                    <div class="col-md-6 col-lg-4 fade-in">
                        <div class="card event-card">
                            <?php if ($event['image']): ?>
                                <img src="uploads/events/<?php echo $event['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-white" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>

                            <span class="badge bg-primary event-badge">
                                <?php echo $event['available_tickets']; ?> bilhetes
                            </span>

                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...
                                </p>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?php echo formatDateTime($event['event_date'], $event['event_time']); ?>
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="card-price"><?php echo formatPrice($event['ticket_price']); ?></span>
                                    <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                        Ver Detalhes <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<?php if (!isLoggedIn()): ?>
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Pronto para começar?</h2>
            <p class="lead mb-4">Crie sua conta gratuitamente e comece a comprar bilhetes para os melhores eventos!</p>
            <a href="register.php" class="btn btn-light btn-lg">
                <i class="bi bi-person-plus"></i> Criar Conta Agora
            </a>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>