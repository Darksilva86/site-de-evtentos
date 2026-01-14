<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: purchases.php');
    exit();
}

$purchase_id = (int)$_GET['id'];

// Buscar detalhes da compra (apenas do utilizador logado)
$stmt = $conn->prepare("
    SELECT p.*, 
           e.title as event_title, e.description as event_description, e.event_date, e.event_time, 
           e.location, e.ticket_price, e.image
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
        <h1 class="fw-bold"><i class="bi bi-receipt"></i> Comprovativo de Compra</h1>
        <div>
            <button onclick="window.print()" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <a href="purchases.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Comprovativo -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <h2 class="fw-bold text-primary"><?php echo SITE_NAME; ?></h2>
                        <p class="text-muted mb-0">Comprovativo de Compra de Bilhetes</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="text-center mb-4">
                        <?php
                        $badge_class = '';
                        $status_text = '';
                        $icon = '';
                        switch ($purchase['status']) {
                            case 'confirmed':
                                $badge_class = 'bg-success';
                                $status_text = 'Compra Confirmada';
                                $icon = 'check-circle';
                                break;
                            case 'pending':
                                $badge_class = 'bg-warning';
                                $status_text = 'Pagamento Pendente';
                                $icon = 'clock';
                                break;
                            case 'cancelled':
                                $badge_class = 'bg-danger';
                                $status_text = 'Compra Cancelada';
                                $icon = 'x-circle';
                                break;
                        }
                        ?>
                        <h3><span class="badge <?php echo $badge_class; ?>"><i class="bi bi-<?php echo $icon; ?>"></i> <?php echo $status_text; ?></span></h3>
                    </div>

                    <!-- Informações da Compra -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Número da Compra</h6>
                            <p class="fw-bold">#<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted">Data da Compra</h6>
                            <p class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($purchase['purchase_date'])); ?></p>
                        </div>
                    </div>

                    <!-- Dados do Evento -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3"><i class="bi bi-calendar-event"></i> Detalhes do Evento</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <?php if ($purchase['image']): ?>
                                        <img src="uploads/events/<?php echo $purchase['image']; ?>"
                                            class="img-fluid rounded" alt="<?php echo htmlspecialchars($purchase['event_title']); ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                            <i class="bi bi-image text-white fs-1"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <h4 class="mb-3"><?php echo htmlspecialchars($purchase['event_title']); ?></h4>

                                    <div class="mb-2">
                                        <i class="bi bi-calendar text-primary"></i>
                                        <strong>Data e Hora:</strong> <?php echo formatDateTime($purchase['event_date'], $purchase['event_time']); ?>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-geo-alt text-primary"></i>
                                        <strong>Local:</strong> <?php echo htmlspecialchars($purchase['location']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes da Compra -->
                    <div class="table-responsive mb-4">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-end">Preço Unitário</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Bilhete de Entrada</td>
                                    <td class="text-center"><?php echo $purchase['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatPrice($purchase['ticket_price']); ?></td>
                                    <td class="text-end"><?php echo formatPrice($purchase['quantity'] * $purchase['ticket_price']); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold text-primary fs-5"><?php echo formatPrice($purchase['total_price']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Informações de Pagamento -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Método de Pagamento</h6>
                            <p class="fw-bold"><?php echo htmlspecialchars($purchase['payment_method']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Total de Bilhetes</h6>
                            <p class="fw-bold"><?php echo $purchase['quantity']; ?> bilhete(s)</p>
                        </div>
                    </div>

                    <!-- Instruções -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li>Guarde este comprovativo para apresentar no dia do evento</li>
                            <li>Os bilhetes também foram enviados para o seu email</li>
                            <li>Chegue com 30 minutos de antecedência</li>
                            <li>Em caso de dúvidas, contacte-nos através de info@eventos.pt</li>
                        </ul>
                    </div>

                    <!-- Footer -->
                    <div class="text-center pt-3 border-top">
                        <p class="text-muted small mb-0">
                            Este é um documento digital válido como comprovativo de compra.<br>
                            Para mais informações, visite <?php echo SITE_URL; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="text-center mt-4">
                <a href="event_detail.php?id=<?php echo $purchase['event_id']; ?>" class="btn btn-primary me-2">
                    <i class="bi bi-calendar-event"></i> Ver Evento
                </a>
                <a href="purchases.php" class="btn btn-outline-secondary">
                    <i class="bi bi-receipt"></i> Ver Todas as Compras
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>