<?php
require_once '../config.php';
requireAdmin();

$page_title = "Gestão de Eventos";

// Adicionar novo evento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = sanitize($_POST['location']);
    $ticket_price = floatval($_POST['ticket_price']);
    $available_tickets = intval($_POST['available_tickets']);

    $errors = [];

    if (empty($title)) $errors[] = 'O título é obrigatório';
    if (empty($description)) $errors[] = 'A descrição é obrigatória';
    if (empty($event_date)) $errors[] = 'A data é obrigatória';
    if (empty($event_time)) $errors[] = 'A hora é obrigatória';
    if (empty($location)) $errors[] = 'O local é obrigatório';
    if ($ticket_price <= 0) $errors[] = 'O preço deve ser maior que zero';
    if ($available_tickets <= 0) $errors[] = 'A quantidade de bilhetes deve ser maior que zero';

    // Upload de imagem
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = uploadImage($_FILES['image']);
        if ($image === false) {
            $errors[] = 'Formato de imagem inválido. Use JPG, PNG ou GIF';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO events (title, description, event_date, event_time, location, ticket_price, available_tickets, image, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$title, $description, $event_date, $event_time, $location, $ticket_price, $available_tickets, $image, $_SESSION['user_id']])) {
            showAlert('Evento criado com sucesso!', 'success');
            header('Location: events.php');
            exit();
        } else {
            $errors[] = 'Erro ao criar evento';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

// Excluir evento
if (isset($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];

    // Verificar se há compras associadas
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM purchases WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $has_purchases = $stmt->fetch()['count'] > 0;

    if ($has_purchases) {
        showAlert('Não é possível excluir evento com compras associadas. Altere o status para cancelado.', 'warning');
    } else {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt->execute([$event_id])) {
            showAlert('Evento excluído com sucesso!', 'success');
        }
    }
    header('Location: events.php');
    exit();
}

// Buscar todos os eventos
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM events WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR location LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY event_date DESC, created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-calendar-event"></i> Gestão de Eventos</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>

    <!-- Add Event Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Adicionar Novo Evento</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Título do Evento *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="event_date" class="form-label">Data *</label>
                        <input type="date" class="form-control future-date" id="event_date" name="event_date" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="event_time" class="form-label">Hora *</label>
                        <input type="time" class="form-control" id="event_time" name="event_time" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Descrição *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="location" class="form-label">Local *</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="ticket_price" class="form-label">Preço do Bilhete (€) *</label>
                        <input type="number" class="form-control price-input" id="ticket_price" name="ticket_price"
                            min="0" step="0.01" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="available_tickets" class="form-label">Bilhetes Disponíveis *</label>
                        <input type="number" class="form-control" id="available_tickets" name="available_tickets"
                            min="1" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="image" class="form-label">Imagem</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                </div>

                <button type="submit" name="add_event" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Adicionar Evento
                </button>
            </form>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control"
                        placeholder="Pesquisar por título ou local..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os Status</option>
                        <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Ativo</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Concluído</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Pesquisar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Events List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Total: <?php echo count($events); ?> evento(s)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Evento</th>
                            <th>Data/Hora</th>
                            <th>Local</th>
                            <th>Preço</th>
                            <th>Bilhetes</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><strong>#<?php echo $event['id']; ?></strong></td>
                                <td>
                                    <?php if ($event['image']): ?>
                                        <img src="../uploads/events/<?php echo $event['image']; ?>"
                                            class="rounded" style="width: 50px; height: 50px; object-fit: cover;"
                                            alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                    <small class="text-muted"><?php echo substr(htmlspecialchars($event['description']), 0, 50); ?>...</small>
                                </td>
                                <td>
                                    <small><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></small>
                                </td>
                                <td><small><?php echo htmlspecialchars($event['location']); ?></small></td>
                                <td><strong><?php echo formatPrice($event['ticket_price']); ?></strong></td>
                                <td><span class="badge bg-primary"><?php echo $event['available_tickets']; ?></span></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch ($event['status']) {
                                        case 'active':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $badge_class = 'bg-danger';
                                            break;
                                        case 'completed':
                                            $badge_class = 'bg-secondary';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($event['status']); ?></span>
                                </td>
                                <td>
                                    <a href="../event_detail.php?id=<?php echo $event['id']; ?>"
                                        class="btn btn-sm btn-outline-info" target="_blank" title="Ver no site">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="event_edit.php?id=<?php echo $event['id']; ?>"
                                        class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="events.php?delete=<?php echo $event['id']; ?>"
                                        class="btn btn-sm btn-outline-danger delete-btn" title="Excluir">
                                        <i class="bi bi-trash"></i>
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

<?php include '../includes/footer.php'; ?>