<?php if (isset($_SESSION['user_id']) && !isset($hideNavigation)): ?>
        </main>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom JavaScript -->
<script src="/assets/js/app.js"></script>

<!-- Page-specific JavaScript -->
<?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $jsFile): ?>
        <script src="<?php echo htmlspecialchars($jsFile); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Inline JavaScript -->
<?php if (isset($inlineJS)): ?>
    <script>
        <?php echo $inlineJS; ?>
    </script>
<?php endif; ?>

<!-- Footer -->
<?php if (isset($_SESSION['user_id']) && !isset($hideFooter)): ?>
<footer class="bg-light text-center text-muted py-3 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-start">
                <small>
                    <i class="fas fa-copyright me-1"></i>
                    <?php echo date('Y'); ?> HCPVision - Système de Visualisation des Données d'Enquêtes
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small>
                    Version 1.0.0 | 
                    <a href="#" class="text-muted text-decoration-none">Documentation</a> | 
                    <a href="#" class="text-muted text-decoration-none">Support</a>
                </small>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

</body>
</html>
