<?php
require_once '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$event_id = (int)$_GET['id'];

// Buscar evento
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    showAlert('Evento não encontrado', 'danger');
    header('Location: events.php');
    exit();
}

$page_title = "Editar Evento";

// Atualizar evento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = sanitize($_POST['location']);
    $ticket_price = floatval($_POST['ticket_price']);
    $available_tickets = intval($_POST['available_tickets']);
    $status = $_POST['status'];

    $errors = [];

    if (empty($title)) $errors[] = 'O título é obrigatório';
    if (empty($description)) $errors[] = 'A descrição é obrigatória';
    if (empty($event_date)) $errors[] = 'A data é obrigatória';
    if (empty($event_time)) $errors[] = 'A hora é obrigatória';
    if (empty($location)) $errors[] = 'O local é obrigatório';
    if ($ticket_price < 0) $errors[] = 'O preço não pode ser negativo';
    if ($available_tickets < 0) $errors[] = 'A quantidade de bilhetes não pode ser negativa';

    // Upload de nova imagem (opcional)
    $image = $event['image']; // Manter imagem atual por padrão
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $new_image = uploadImage($_FILES['image']);
        if ($new_image !== false) {
            $image = $new_image;
        } else {
            $errors[] = 'Formato de imagem inválido. Use JPG, PNG ou GIF';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE events 
            SET title = ?, description = ?, event_date = ?, event_time = ?, 
                location = ?, ticket_price = ?, available_tickets = ?, image = ?, status = ?
            WHERE id = ?
        ");

        if ($stmt->execute([
            $title, $description, $event_date, $event_time, 
            $location, $ticket_price, $available_tickets, $image, $status, $event_id
        ])) {
            showAlert('Evento atualizado com sucesso!', 'success');
            // Recarregar dados do evento
            $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $event = $stmt->fetch();
        } else {
            $errors[] = 'Erro ao atualizar evento';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="events.php">Eventos</a></li>
            <li class="breadcrumb-item active">Editar Evento</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-pencil-square"></i> Editar Evento</h1>
        <a href="events.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalhes do Evento</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Título do Evento *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                    value="<?php echo htmlspecialchars($event['title']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="event_date" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" 
                                    value="<?php echo $event['event_date']; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="event_time" class="form-label">Hora *</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" 
                                    value="<?php echo $event['event_time']; ?>" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Descrição *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="location" class="form-label">Local *</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                    value="<?php echo htmlspecialchars($event['location']); ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ticket_price" class="form-label">Preço (€) *</label>
                                <input type="number" class="form-control price-input" id="ticket_price" name="ticket_price" 
                                    min="0" step="0.01" value="<?php echo $event['ticket_price']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="available_tickets" class="form-label">Bilhetes Disponíveis *</label>
                                <input type="number" class="form-control" id="available_tickets" name="available_tickets" 
                                    min="0" value="<?php echo $event['available_tickets']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo $event['status'] == 'active' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="cancelled" <?php echo $event['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                    <option value="completed" <?php echo $event['status'] == 'completed' ? 'selected' : ''; ?>>Concluído</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="image" class="form-label">Imagem de Capa</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Deixe vazio para manter a imagem atual.</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update_event" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Atualizar Evento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Imagem Atual -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Imagem Atual</h6>
                </div>
                <div class="card-body text-center">
                    <?php if ($event['image']): ?>
                        <img src="../uploads/events/<?php echo $event['image']; ?>" class="img-fluid rounded mb-2" alt="Imagem do Evento">
                    <?php else: ?>
                        <div class="bg-secondary rounded p-5 mb-2 text-white">
                            <i class="bi bi-image display-1"></i>
                            <p class="mt-2">Sem imagem</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estatísticas do Evento -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Estatísticas (View)</h6>
                </div>
                <div class="card-body">
                    <?php
                        // Buscar estatísticas da View event_details se disponível
                        try {
                            $stmt = $conn->prepare("SELECT * FROM event_details WHERE id = ?");
                            $stmt->execute([$event_id]);
                            $event_stats = $stmt->fetch();
                        } catch (PDOException $e) {
                            $event_stats = false;
                        }
                    ?>
                    
                    <?php if ($event_stats): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Bilhetes Vendidos</small>
                            <h4 class="mb-0"><?php echo $event_stats['tickets_sold']; ?></h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Receita Total</small>
                            <h4 class="mb-0 text-success"><?php echo formatPrice($event_stats['total_revenue']); ?></h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Ocupação</small>
                            <div class="progress mt-1" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" 
                                    style="width: <?php echo $event_stats['occupancy_percentage']; ?>%">
                                </div>
                            </div>
                            <small class="text-end d-block mt-1"><?php echo $event_stats['occupancy_percentage']; ?>%</small>
                        </div>
                        <div>
                            <small class="text-muted d-block">Status de Stock</small>
                            <span class="badge bg-info text-dark"><?php echo $event_stats['availability_status']; ?></span>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i> 
                            Execute o script de views para ver estatísticas avançadas aqui.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <a href="../event_detail.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-info w-100" target="_blank">
                    <i class="bi bi-eye"></i> Ver na Loja
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>