<?php
require_once 'config.php';

$page_title = "Contacto";

// Processar formulário de contacto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    $errors = [];

    if (empty($name)) $errors[] = 'O nome é obrigatório';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';
    if (empty($subject)) $errors[] = 'O assunto é obrigatório';
    if (empty($message)) $errors[] = 'A mensagem é obrigatória';

    if (empty($errors)) {
        // Aqui você pode implementar o envio de email real
        // Por enquanto, apenas mostramos mensagem de sucesso
        showAlert('Mensagem enviada com sucesso! Entraremos em contacto em breve.', 'success');
        header('Location: contact.php');
        exit();
    } else {
        foreach ($errors as $error) {
            showAlert($error, 'danger');
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Entre em Contacto</h1>
            <p class="lead text-muted">Estamos aqui para ajudar! Envie-nos a sua mensagem</p>
        </div>
    </div>

    <div class="row">
        <!-- Formulário de Contacto -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Envie-nos uma Mensagem</h4>
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                <div class="invalid-feedback">Por favor, insira seu nome.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <div class="invalid-feedback">Por favor, insira um email válido.</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="subject" class="form-label">Assunto *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Selecione um assunto...</option>
                                    <option value="Dúvida sobre evento">Dúvida sobre evento</option>
                                    <option value="Problema com compra">Problema com compra</option>
                                    <option value="Sugestão">Sugestão</option>
                                    <option value="Reclamação">Reclamação</option>
                                    <option value="Parcerias">Parcerias</option>
                                    <option value="Outro">Outro</option>
                                </select>
                                <div class="invalid-feedback">Por favor, selecione um assunto.</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="message" class="form-label">Mensagem *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                <div class="invalid-feedback">Por favor, escreva sua mensagem.</div>
                            </div>
                        </div>

                        <button type="submit" name="send_message" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informações de Contacto -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Informações de Contacto</h5>

                    <div class="mb-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Morada</strong>
                                <p class="text-muted small mb-0">
                                    Rua Exemplo, 123<br>
                                    1000-001 Lisboa, Portugal
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Telefone</strong>
                                <p class="text-muted small mb-0">
                                    +351 210 000 000<br>
                                    Seg-Sex: 9h-18h
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px; min-width: 40px;">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <strong class="d-block mb-1">Email</strong>
                                <p class="text-muted small mb-0">
                                    info@eventos.pt<br>
                                    suporte@eventos.pt
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Redes Sociais</h6>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Horário de Atendimento</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>Segunda - Sexta</strong></td>
                            <td class="text-end">9h - 18h</td>
                        </tr>
                        <tr>
                            <td><strong>Sábado</strong></td>
                            <td class="text-end">10h - 14h</td>
                        </tr>
                        <tr>
                            <td><strong>Domingo</strong></td>
                            <td class="text-end">Fechado</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Rápido -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <h3 class="fw-bold mb-4 text-center">Perguntas Frequentes</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2"><i class="bi bi-question-circle text-primary"></i> Como compro bilhetes?</h6>
                            <p class="small text-muted mb-0">
                                Basta criar uma conta, escolher o evento desejado e adicionar bilhetes ao carrinho.
                                Depois, finalize a compra escolhendo o método de pagamento.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2"><i class="bi bi-question-circle text-primary"></i> Posso cancelar minha compra?</h6>
                            <p class="small text-muted mb-0">
                                Sim, de acordo com nossa política de reembolso. Entre em contacto conosco
                                até 48 horas antes do evento.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2"><i class="bi bi-question-circle text-primary"></i> Como recebo meus bilhetes?</h6>
                            <p class="small text-muted mb-0">
                                Após a confirmação do pagamento, os bilhetes são enviados automaticamente
                                para o email cadastrado.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2"><i class="bi bi-question-circle text-primary"></i> Quais métodos de pagamento aceitam?</h6>
                            <p class="small text-muted mb-0">
                                Aceitamos Cartão de Crédito/Débito, MB Way, PayPal e Transferência Bancária.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>