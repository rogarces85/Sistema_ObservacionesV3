</main>
</div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay loading-overlay--hidden" aria-hidden="true" role="status">
    <div class="loading-overlay__content">
        <div class="loading-overlay__spinner" aria-hidden="true"></div>
        <span class="loading-overlay__text">Cargando...</span>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
    // Funciones para manejo responsivo del sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuBtn = document.getElementById('mobile-menu-btn');
        const isOpen = sidebar.classList.contains('sidebar--open');

        if (isOpen) {
            sidebar.classList.remove('sidebar--open');
            overlay.classList.remove('sidebar__overlay--visible');
            menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('sidebar--open');
            overlay.classList.add('sidebar__overlay--visible');
            menuBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuBtn = document.getElementById('mobile-menu-btn');

        sidebar.classList.remove('sidebar--open');
        overlay.classList.remove('sidebar__overlay--visible');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    // Cerrar sidebar al hacer clic en un enlace (móvil)
    document.querySelectorAll('.sidebar__nav-link').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                closeSidebar();
            }
        });
    });

    // Cerrar sidebar al redimensionar ventana a desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeSidebar();
        }
    });

    // Cerrar sidebar con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
            // También cerrar modales abiertos
            document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
        }
    });

    // Skip to main content (accesibilidad)
    function skipToMain() {
        document.getElementById('main-content').focus();
    }
</script>
</body>

</html>