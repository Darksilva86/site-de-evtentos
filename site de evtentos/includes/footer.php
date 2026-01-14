</main>

<!-- Footer -->
<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="bi bi-calendar-event"></i> <?php echo SITE_NAME; ?></h5>
                <p>Sistema completo de gestão de eventos com compra de bilhetes online.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white-50 text-decoration-none">Início</a></li>
                    <li><a href="events.php" class="text-white-50 text-decoration-none">Eventos</a></li>
                    <li><a href="about.php" class="text-white-50 text-decoration-none">Sobre Nós</a></li>
                    <li><a href="contact.php" class="text-white-50 text-decoration-none">Contacto</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contacto</h5>
                <p class="mb-1"><i class="bi bi-envelope"></i> info@eventos.pt</p>
                <p class="mb-1"><i class="bi bi-telephone"></i> +351 210 000 000</p>
                <div class="mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-twitter fs-5"></i></a>
                </div>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>