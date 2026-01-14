<?php
require_once 'config.php';

$page_title = "Sobre Nós";

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Sobre Nós</h1>
            <p class="lead text-muted">Conectando pessoas aos melhores eventos em Portugal</p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-primary mb-3">
                        <i class="bi bi-bullseye" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Nossa Missão</h3>
                    <p class="text-muted">
                        Facilitar o acesso aos melhores eventos culturais, desportivos e de entretenimento em Portugal,
                        proporcionando uma plataforma segura, intuitiva e eficiente para a compra de bilhetes online.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-success mb-3">
                        <i class="bi bi-eye" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Nossa Visão</h3>
                    <p class="text-muted">
                        Ser a plataforma de referência em Portugal para descoberta e reserva de eventos,
                        conectando organizadores e público de forma simples e transparente.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="fw-bold mb-4 text-center">Nossos Valores</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-check" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Confiança</h5>
                        <p class="text-muted small">Transações seguras e dados protegidos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-lightning" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Rapidez</h5>
                        <p class="text-muted small">Compra de bilhetes em poucos cliques</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-star" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Qualidade</h5>
                        <p class="text-muted small">Os melhores eventos selecionados</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Comunidade</h5>
                        <p class="text-muted small">Conectando pessoas através de eventos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card bg-light border-0">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4">Quem Somos</h2>
                    <p class="mb-3">
                        Somos uma plataforma portuguesa dedicada a tornar a experiência de descobrir e
                        participar em eventos mais simples e acessível para todos. Desde 2025, conectamos
                        milhares de pessoas aos eventos que amam.
                    </p>
                    <p class="mb-3">
                        Nossa equipa trabalha constantemente para oferecer a melhor seleção de eventos
                        em Portugal, desde concertos e festivais até conferências e espetáculos.
                        Acreditamos que cada evento é uma oportunidade única de criar memórias inesquecíveis.
                    </p>
                    <p class="mb-0">
                        Com uma plataforma intuitiva, segura e eficiente, facilitamos todo o processo
                        de compra de bilhetes, permitindo que você se concentre no que realmente importa:
                        aproveitar o evento!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 text-center">
            <h2 class="fw-bold mb-4">Estatísticas</h2>
            <div class="row g-4">
                <?php
                $stmt = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active'");
                $total_events = $stmt->fetch()['count'];

                $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
                $total_users = $stmt->fetch()['count'];

                $stmt = $conn->query("SELECT SUM(quantity) as total FROM purchases");
                $total_tickets = $stmt->fetch()['total'] ?? 0;

                $stmt = $conn->query("SELECT COUNT(DISTINCT event_id) as count FROM purchases");
                $events_sold = $stmt->fetch()['count'];
                ?>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-4">
                            <h2 class="display-4 fw-bold text-primary mb-2"><?php echo $total_events; ?>+</h2>
                            <p class="text-muted mb-0">Eventos Ativos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-4">
                            <h2 class="display-4 fw-bold text-success mb-2"><?php echo $total_users; ?>+</h2>
                            <p class="text-muted mb-0">Utilizadores Registados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-4">
                            <h2 class="display-4 fw-bold text-warning mb-2"><?php echo $total_tickets; ?>+</h2>
                            <p class="text-muted mb-0">Bilhetes Vendidos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-4">
                            <h2 class="display-4 fw-bold text-info mb-2"><?php echo $events_sold; ?>+</h2>
                            <p class="text-muted mb-0">Eventos Realizados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-lg-8 mx-auto text-center">
            <h3 class="fw-bold mb-3">Pronto para começar?</h3>
            <p class="text-muted mb-4">Junte-se a milhares de utilizadores e descubra os melhores eventos!</p>
            <div>
                <a href="events.php" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-calendar-event"></i> Ver Eventos
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Criar Conta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>