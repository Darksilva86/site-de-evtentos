<?php
require_once 'config.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$event_id = (int)$_GET['id'];

// Buscar evento
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    showAlert('Evento não encontrado', 'danger');
    header('Location: events.php');
    exit();
}

$page_title = $event['title'];

// Adicionar ao carrinho
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    requireLogin();

    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) {
        showAlert('Quantidade inválida', 'danger');
    } elseif ($quantity > $event['available_tickets']) {
        showAlert('Não há bilhetes suficientes disponíveis', 'danger');
    } else {
        // Verificar se já existe no carrinho
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$_SESSION['user_id'], $event_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $new_quantity = $existing['quantity'] + $quantity;
            if ($new_quantity > $event['available_tickets']) {
                showAlert('Não há bilhetes suficientes disponíveis', 'danger');
            } else {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $existing['id']]);
                showAlert('Carrinho atualizado com sucesso!', 'success');
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, event_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $event_id, $quantity]);
            showAlert('Bilhete(s) adicionado(s) ao carrinho!', 'success');
        }

        header('Location: cart.php');
        exit();
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Início</a></li>
            <li class="breadcrumb-item"><a href="events.php">Eventos</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($event['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Event Details -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <?php if ($event['image']): ?>
                    <img src="uploads/events/<?php echo $event['image']; ?>" class="event-detail-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
                <?php else: ?>
                    <div class="event-detail-image bg-secondary d-flex align-items-center justify-content-center">
                        <i class="bi bi-image text-white" style="font-size: 6rem;"></i>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($event['title']); ?></h1>

                    <div class="event-info-box">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="bi bi-calendar-event"></i> Data e Hora</h6>
                                <p class="mb-0"><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary"><i class="bi bi-geo-alt"></i> Local</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="bi bi-ticket-perforated"></i> Bilhetes Disponíveis</h6>
                                <p class="mb-0">
                                    <strong><?php echo $event['available_tickets']; ?></strong> bilhetes
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="bi bi-cash"></i> Preço</h6>
                                <p class="mb-0 fs-4 fw-bold text-primary">
                                    <?php echo formatPrice($event['ticket_price']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="fw-bold mb-3">Sobre o Evento</h4>
                        <p class="text-muted" style="white-space: pre-line;"><?php echo htmlspecialchars($event['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Box -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Comprar Bilhetes</h5>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Preço por bilhete:</span>
                            <strong class="text-primary fs-5"><?php echo formatPrice($event['ticket_price']); ?></strong>
                        </div>
                        <small class="text-muted">
                            <?php echo $event['available_tickets']; ?> bilhetes disponíveis
                        </small>
                    </div>

                    <?php if ($event['available_tickets'] > 0): ?>
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantidade:</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity"
                                        min="1" max="<?php echo min(10, $event['available_tickets']); ?>" value="1" required>
                                    <small class="text-muted">Máximo: <?php echo min(10, $event['available_tickets']); ?> bilhetes por compra</small>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <span>Total:</span>
                                        <strong class="fs-4 text-primary" id="totalPrice"><?php echo formatPrice($event['ticket_price']); ?></strong>
                                    </div>
                                </div>

                                <button type="submit" name="add_to_cart" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-cart-plus"></i> Adicionar ao Carrinho
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Faça login para comprar bilhetes
                            </div>
                            <a href="login.php?redirect=event_detail.php?id=<?php echo $event_id; ?>" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Fazer Login
                            </a>
                            <a href="register.php" class="btn btn-outline-primary w-100 py-2 mt-2">
                                <i class="bi bi-person-plus"></i> Criar Conta
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Bilhetes esgotados
                        </div>
                    <?php endif; ?>

                    <hr class="my-3">

                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Compra segura<br>
                            <i class="bi bi-arrow-repeat"></i> Política de reembolso
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Calcular total automaticamente
    document.getElementById('quantity')?.addEventListener('input', function() {
        const price = <?php echo $event['ticket_price']; ?>;
        const quantity = parseInt(this.value) || 1;
        const total = price * quantity;

        document.getElementById('totalPrice').textContent = total.toLocaleString('pt-PT', {
            style: 'currency',
            currency: 'EUR'
        });
    });
</script>

<?php include 'includes/footer.php'; ?>