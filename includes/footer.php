                </div>
            </div>

            <footer class="footer">
                <div class="container-xl">
                    <div class="row text-muted">
                        <div class="col-12 text-center">
                            <small><?php echo APP_NAME; ?> &copy; <?php echo date('Y'); ?></small>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay loading-overlay--hidden" aria-hidden="true" role="status" style="display:none">
        <div class="loading-overlay__content">
            <div class="loading-overlay__spinner" aria-hidden="true"></div>
            <span class="loading-overlay__text">Cargando...</span>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="assets/js/charts.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
