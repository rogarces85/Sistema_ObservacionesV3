</div>
            </div>

            <footer class="footer">
                <?php echo APP_NAME; ?> &copy; <?php echo date('Y'); ?>
            </footer>
        </div>
    </div>

    <div id="toast-container" class="toast-container"></div>

    <script src="assets/js/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2"></script>
    <script src="assets/js/toasts.js"></script>
    <script src="assets/js/charts-apex.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        document.querySelector('.nav-user').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('user-dropdown').classList.toggle('show');
        });
        
        document.addEventListener('click', function() {
            document.getElementById('user-dropdown').classList.remove('show');
        });
        
        function toggleSidebar() {
            document.querySelector('.navbar-vertical').classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.gestorSesion = new GestorSesion();
            window.gestorSesion.iniciar();

            document.getElementById('btn-logout')?.addEventListener('click', function(e) {
                e.preventDefault();
                logout();
            });
        });
    </script>
</body>
</html>