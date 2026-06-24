                </div>
            </div>

            <footer class="footer">
                <div class="container-xl">
                    <div class="row text-muted align-items-center">
                        <div class="col-md-6 text-center text-md-start">
                            <small>
                                &copy; <?php echo date('Y'); ?> <?php echo defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'Sistema REM'; ?> &middot; Servicio de Salud Osorno
                            </small>
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <small class="d-inline-flex align-items-center gap-2">
                                <span class="status status-green" aria-hidden="true"></span>
                                <span>Entorno operativo estable</span>
                            </small>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay loading-overlay--hidden" aria-hidden="true" role="status">
        <div class="loading-overlay__content">
            <div class="loading-overlay__spinner" aria-hidden="true"></div>
            <span class="loading-overlay__text">Cargando...</span>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;" id="toastContainer"></div>

    <button id="themeToggleFab" class="btn-fab d-none d-md-flex" type="button" aria-label="Cambiar tema">
        <i class="ti ti-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="assets/js/charts.js" defer></script>
    <script src="assets/js/theme.js" defer></script>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
