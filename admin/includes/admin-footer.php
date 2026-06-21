<?php
/**
 * Law & Reason - Admin Footer
 * Closes the layout, includes admin JS
 */
?>
    </main>

    <script>
    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Image preview on file input change
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
        input.addEventListener('change', function() {
            var previewId = this.dataset.preview;
            var preview = document.getElementById(previewId);
            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.flash-message').forEach(function(el) {
        setTimeout(function() {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.5s';
            setTimeout(function() { el.remove(); }, 500);
        }, 5000);
    });

    // Admin mobile sidebar toggle
    (function() {
        var toggle = document.getElementById('adminMenuToggle');
        var sidebar = document.querySelector('.admin-sidebar');
        var overlay = document.getElementById('adminOverlay');
        
        if (toggle && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('active');
            });
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                });
            }
        }
    })();
    </script>
</body>
</html>
