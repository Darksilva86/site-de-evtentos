<?php
require_once 'config.php';
requireLogin();

$page_title = "Carrinho de Compras";

// Remover item do carrinho
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    showAlert('Item removido do carrinho', 'success');
    header('Location: cart.php');
    exit();
}

// Atualizar quantidade
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
        showAlert('Carrinho atualizado', 'success');
    }
    header('Location: cart.php');
    exit();
}

// Buscar itens do carrinho
$stmt = $conn->prepare("
    SELECT c.*, e.title, e.description, e.event_date, e.event_time, e.location, 
           e.ticket_price, e.available_tickets, e.image
    FROM cart c
    JOIN events e ON c.event_id = e.id
    WHERE c.user_id = ? AND e.status = 'active'
    ORDER BY c.added_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calcular total
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['ticket_price'] * $item['quantity'];
}

include 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4"><i class="bi bi-cart"></i> Carrinho de Compras</h1>

    <?php if (empty($cart_items)): ?>
        <div class="empty-state">
            <i class="bi bi-cart-x"></i>
            <h3>Seu carrinho está vazio</h3>
            <p>Adicione eventos ao carrinho para continuar</p>
            <a href="events.php" class="btn btn-primary">
                <i class="bi bi-calendar-event"></i> Ver Eventos
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="row">
                            <div class="col-md-3">
                                <?php if ($item['image']): ?>
                                    <img src="uploads/events/<?php echo $item['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                        <i class="bi bi-image text-white fs-1"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar"></i> <?php echo formatDateTime($item['event_date'], $item['event_time']); ?><br>
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($item['location']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong class="text-primary"><?php echo formatPrice($item['ticket_price']); ?></strong> por bilhete
                                </p>
                            </div>
                            <div class="col-md-3 text-end">
                                <form method="POST" action="" class="mb-2">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <div class="input-group input-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-secondary qty-minus">-</button>
                                        <input type="number" name="quantity" class="form-control text-center"
                                            value="<?php echo $item['quantity']; ?>"
                                            min="1" max="<?php echo $item['available_tickets']; ?>">
                                        <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                                    </div>
                                    <button type="submit" name="update_cart" class="btn btn-sm btn-primary w-100 mb-1">
                                        <i class="bi bi-arrow-repeat"></i> Atualizar
                                    </button>
                                </form>
                                <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger w-100 delete-btn">
                                    <i class="bi bi-trash"></i> Remover
                                </a>
                                <div class="mt-2">
                                    <strong class="text-primary fs-5">
                                        <?php echo formatPrice($item['ticket_price'] * $item['quantity']); ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5 class="fw-bold mb-3">Resumo da Compra</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong><?php echo formatPrice($subtotal); ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Taxa de serviço:</span>
                        <strong><?php echo formatPrice(0); ?></strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5 fw-bold">Total:</span>
                        <strong class="fs-4 text-primary"><?php echo formatPrice($subtotal); ?></strong>
                    </div>

                    <a href="checkout.php" class="btn btn-primary w-100 py-2 mb-2">
                        <i class="bi bi-credit-card"></i> Finalizar Compra
                    </a>

                    <a href="events.php" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-left"></i> Continuar Comprando
                    </a>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Pagamento seguro<br>
                            <i class="bi bi-lock"></i> Seus dados estão protegidos
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>