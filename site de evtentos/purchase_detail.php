<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: purchases.php');
    exit();
}

$purchase_id = (int)$_GET['id'];

// Buscar compra (apenas do utilizador logado)
$stmt = $conn->prepare("
    SELECT p.*, e.title, e.description, e.event_date, e.event_time, e.location, e.image
    FROM purchases p
    JOIN events e ON p.event_id = e.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$purchase_id, $_SESSION['user_id']]);
$purchase = $stmt->fetch();

if (!$purchase) {
    showAlert('Compra não encontrada', 'danger');
    header('Location: purchases.php');
    exit();
}

$page_title = "Detalhes da Compra";

include 'includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Início</a></li>
            <li class="breadcrumb-item"><a href="purchases.php">Minhas Compras</a></li>
            <li class="breadcrumb-item active">Compra #<?php echo $purchase_id; ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold"><i class="bi bi-receipt"></i> Detalhes da Compra</h1>
        <a href="purchases.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($purchase['status'] == 'confirmed'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> <strong>Compra Confirmada!</strong> Seus bilhetes foram enviados para o email cadastrado.
        </div>
    <?php elseif ($purchase['status'] == 'pending'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-clock"></i> <strong>Pagamento Pendente.</strong> Aguardando confirmação do pagamento.
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle"></i> <strong>Compra Cancelada.</strong> Esta compra foi cancelada.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Detalhes do Evento -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Evento</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <?php if ($purchase['image']): ?>
                                <img src="uploads/events/<?php echo $purchase['image']; ?>"
                                    class="img-fluid rounded" alt="<?php echo htmlspecialchars($purchase['title']); ?>">
                            <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="bi bi-image text-white fs-1"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($purchase['title']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($purchase['description']); ?></p>
                            <div class="event-info-box">
                                <div class="mb-2">
                                    <i class="bi bi-calendar text-primary"></i>
                                    <strong>Data e Hora:</strong> <?php echo formatDateTime($purchase['event_date'], $purchase['event_time']); ?>
                                </div>
                                <div>
                                    <i class="bi bi-geo-alt text-primary"></i>
                                    <strong>Local:</strong> <?php echo htmlspecialchars($purchase['location']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bilhetes -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Seus Bilhetes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Você possui <strong><?php echo $purchase['quantity']; ?> bilhete(s)</strong> para este evento.
                    </div>

                    <?php for ($i = 1; $i <= $purchase['quantity']; $i++): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1"><strong>Bilhete #<?php echo str_pad($purchase_id . $i, 8, '0', STR_PAD_LEFT); ?></strong></h6>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($purchase['title']); ?><br>
                                        <?php echo formatDateTime($purchase['event_date'], $purchase['event_time']); ?>
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="bg-light p-2 rounded">
                                        <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <small>Apresente este código QR na entrada do evento ou mostre o email de confirmação.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Resumo da Compra -->
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Resumo da Compra</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">ID da Compra</small>
                        <strong>#<?php echo $purchase['id']; ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Data da Compra</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($purchase['purchase_date'])); ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Quantidade</small>
                        <strong><?php echo $purchase['quantity']; ?> bilhete(s)</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Método de Pagamento</small>
                        <strong><?php echo htmlspecialchars($purchase['payment_method']); ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Pago:</strong>
                        <strong class="text-primary fs-5"><?php echo formatPrice($purchase['total_price']); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Ações</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir Bilhetes
                    </button>
                    <a href="mailto:?subject=Bilhetes - <?php echo htmlspecialchars($purchase['title']); ?>&body=Confira meus bilhetes para o evento!"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-envelope"></i> Enviar por Email
                    </a>
                    <?php if ($purchase['status'] != 'cancelled'): ?>
                        <a href="event_detail.php?id=<?php echo $purchase['event_id']; ?>" class="btn btn-outline-info">
                            <i class="bi bi-info-circle"></i> Ver Evento
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>