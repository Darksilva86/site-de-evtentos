<?php
require_once 'config.php';
requireLogin();

$page_title = "Finalizar Compra";

// Buscar itens do carrinho
$stmt = $conn->prepare("
    SELECT c.*, e.title, e.ticket_price, e.available_tickets
    FROM cart c
    JOIN events e ON c.event_id = e.id
    WHERE c.user_id = ? AND e.status = 'active'
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    showAlert('Carrinho vazio', 'warning');
    header('Location: cart.php');
    exit();
}

// Calcular total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['ticket_price'] * $item['quantity'];
}

// Processar compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    $payment_method = sanitize($_POST['payment_method']);

    try {
        $conn->beginTransaction();

        $success = true;

        // Processar cada item do carrinho
        foreach ($cart_items as $item) {
            // Verificar disponibilidade
            $stmt = $conn->prepare("SELECT available_tickets FROM events WHERE id = ? FOR UPDATE");
            $stmt->execute([$item['event_id']]);
            $event = $stmt->fetch();

            if ($event['available_tickets'] < $item['quantity']) {
                $success = false;
                showAlert('Bilhetes insuficientes para: ' . $item['title'], 'danger');
                break;
            }

            // Criar registro de compra
            $stmt = $conn->prepare("
                INSERT INTO purchases (user_id, event_id, quantity, total_price, payment_method)
                VALUES (?, ?, ?, ?, ?)
            ");
            $item_total = $item['ticket_price'] * $item['quantity'];
            $stmt->execute([
                $_SESSION['user_id'],
                $item['event_id'],
                $item['quantity'],
                $item_total,
                $payment_method
            ]);

            // Atualizar bilhetes disponíveis
            $stmt = $conn->prepare("
                UPDATE events 
                SET available_tickets = available_tickets - ? 
                WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['event_id']]);
        }

        if ($success) {
            // Limpar carrinho
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $conn->commit();
            showAlert('Compra realizada com sucesso!', 'success');
            header('Location: purchases.php');
            exit();
        } else {
            $conn->rollBack();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        showAlert('Erro ao processar compra. Tente novamente.', 'danger');
    }
}

// Buscar dados do utilizador
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Início</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Carrinho</a></li>
            <li class="breadcrumb-item active">Finalizar Compra</li>
        </ol>
    </nav>

    <h1 class="fw-bold mb-4"><i class="bi bi-credit-card"></i> Finalizar Compra</h1>

    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8 mb-4">
            <form method="POST" action="" id="checkoutForm" class="needs-validation" novalidate>
                <!-- Dados Pessoais -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Dados Pessoais</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Localidade</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['address']); ?>" readonly>
                            </div>
                        </div>
                        <small class="text-muted">
                            <a href="profile.php">Editar dados pessoais</a>
                        </small>
                    </div>
                </div>

                <!-- Método de Pagamento -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Método de Pagamento</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="Cartão de Crédito" checked required>
                            <label class="form-check-label" for="credit_card">
                                <i class="bi bi-credit-card"></i> Cartão de Crédito / Débito
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="mbway" value="MB Way" required>
                            <label class="form-check-label" for="mbway">
                                <i class="bi bi-phone"></i> MB Way
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal" required>
                            <label class="form-check-label" for="paypal">
                                <i class="bi bi-paypal"></i> PayPal
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="Transferência Bancária" required>
                            <label class="form-check-label" for="transfer">
                                <i class="bi bi-bank"></i> Transferência Bancária
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Termos -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Concordo com os <a href="#">termos e condições</a> e a <a href="#">política de privacidade</a>
                            </label>
                            <div class="invalid-feedback">Você deve aceitar os termos.</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <a href="cart.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar ao Carrinho
                    </a>
                    <button type="submit" name="confirm_purchase" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Confirmar Compra
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 class="fw-bold mb-3">Resumo do Pedido</h5>

                <?php foreach ($cart_items as $item): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <small class="text-muted">Quantidade: <?php echo $item['quantity']; ?></small>
                            </div>
                            <div class="text-end">
                                <strong><?php echo formatPrice($item['ticket_price'] * $item['quantity']); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong><?php echo formatPrice($total); ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Taxa de serviço:</span>
                    <strong><?php echo formatPrice(0); ?></strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-3">
                    <span class="fs-5 fw-bold">Total a Pagar:</span>
                    <strong class="fs-4 text-primary"><?php echo formatPrice($total); ?></strong>
                </div>

                <div class="alert alert-info">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Seus bilhetes serão enviados por email após a confirmação do pagamento.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>