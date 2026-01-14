<?php
require_once 'config.php';

$page_title = "Eventos";

// Parâmetros de pesquisa
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'date_asc';

// Construir query
$query = "SELECT * FROM events WHERE status = 'active' AND event_date >= CURDATE()";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($date)) {
    $query .= " AND event_date = ?";
    $params[] = $date;
}

// Ordenação
switch ($order) {
    case 'date_desc':
        $query .= " ORDER BY event_date DESC, event_time DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY ticket_price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY ticket_price DESC";
        break;
    default:
        $query .= " ORDER BY event_date ASC, event_time ASC";
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="fw-bold"><i class="bi bi-calendar-event"></i> Todos os Eventos</h1>
            <p class="text-muted">Encontre e reserve bilhetes para os melhores eventos</p>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="events.php" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Pesquisar</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="Nome, descrição ou local..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Data</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="col-md-2">
                    <label for="order" class="form-label">Ordenar por</label>
                    <select name="order" id="order" class="form-select">
                        <option value="date_asc" <?php echo $order == 'date_asc' ? 'selected' : ''; ?>>Data (crescente)</option>
                        <option value="date_desc" <?php echo $order == 'date_desc' ? 'selected' : ''; ?>>Data (decrescente)</option>
                        <option value="price_asc" <?php echo $order == 'price_asc' ? 'selected' : ''; ?>>Preço (menor)</option>
                        <option value="price_desc" <?php echo $order == 'price_desc' ? 'selected' : ''; ?>>Preço (maior)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Pesquisar
                    </button>
                </div>
            </form>

            <?php if (!empty($search) || !empty($date)): ?>
                <div class="mt-3">
                    <a href="events.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpar Filtros
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Results Count -->
    <div class="mb-3">
        <p class="text-muted">
            <strong><?php echo count($events); ?></strong> evento(s) encontrado(s)
            <?php if (!empty($search)): ?>
                para "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Events List -->
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <h3>Nenhum evento encontrado</h3>
            <p>Tente ajustar seus filtros de pesquisa</p>
            <a href="events.php" class="btn btn-primary">Ver Todos os Eventos</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card event-card h-100">
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

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo substr(htmlspecialchars($event['description']), 0, 120); ?>...
                            </p>

                            <div class="mb-3">
                                <div class="mb-2">
                                    <i class="bi bi-calendar text-primary"></i>
                                    <small><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></small>
                                </div>
                                <div>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <small><?php echo htmlspecialchars($event['location']); ?></small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="card-price"><?php echo formatPrice($event['ticket_price']); ?></span>
                                <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>