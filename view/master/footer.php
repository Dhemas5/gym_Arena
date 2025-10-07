</div>
<!-- /.content-wrapper -->

<!-- Enhanced footer with more information and links -->
<footer class="main-footer">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <strong>&copy; 2025 <a href="#" class="text-primary">Arena Gym Fit Club</a>.</strong> All rights reserved.
            </div>
            <div class="col-sm-6 text-right d-none d-sm-block">
                <b>Version</b> 1.0.0 | 
                <a href="#" class="text-muted">Dokumentasi</a> | 
                <a href="#" class="text-muted">Support</a>
            </div>
        </div>
    </div>
</footer>

<!-- Added scroll to top button -->
<button id="scrollToTop" class="btn btn-primary" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 45px; height: 45px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <i class="fas fa-arrow-up"></i>
</button>

<aside class="control-sidebar control-sidebar-dark"></aside>

</div>
<!-- ./wrapper -->

<script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
<script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/assets_admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../../../assets/assets_admin/dist/js/adminlte.js"></script>

<script src="../../../assets/assets_admin/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="../../../assets/assets_admin/plugins/raphael/raphael.min.js"></script>
<script src="../../../assets/assets_admin/plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="../../../assets/assets_admin/plugins/jquery-mapael/maps/usa_states.min.js"></script>
<script src="../../../assets/assets_admin/plugins/chart.js/Chart.min.js"></script>

<!-- DataTables  & Plugins -->
<script src="../../../assets/assets_admin/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../../assets/assets_admin/plugins/jszip/jszip.min.js"></script>
<script src="../../../assets/assets_admin/plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../../assets/assets_admin/plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../../assets/assets_admin/plugins/datatables-buttons/js/buttons.print.min.js"></script>

<!-- Added preloader hide script to fix loading stuck issue -->
<script>
    // Debug log to check if script is running
    console.log("[v0] Footer script loaded");
    
    // Hide preloader immediately when DOM is ready
    $(document).ready(function() {
        console.log("[v0] Document ready, hiding preloader");
        $('.preloader').fadeOut('slow', function() {
            console.log("[v0] Preloader hidden");
            $(this).remove();
        });
    });
    
    // Backup: also try on window load
    $(window).on('load', function() {
        console.log("[v0] Window loaded");
        if ($('.preloader').length) {
            console.log("[v0] Preloader still exists, hiding now");
            $('.preloader').fadeOut('fast', function() {
                $(this).remove();
            });
        }
    });
</script>

<!-- Enhanced DataTables initialization with better options -->
<script>
    $(function() {
        // Check if table exists before initializing
        if ($("#tabelPelatih").length) {
            var table = $("#tabelPelatih").DataTable({
                paging: true,
                lengthChange: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                buttons: ["copy", "csv", "excel", "pdf", "print"],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Data tidak ditemukan"
                }
            });

            table.buttons().container().appendTo('#tabelPelatih_wrapper .col-md-6:eq(0)');
        }
    });
</script>

<!-- Enhanced control sidebar with better dark mode toggle -->
<script>
    $(function() {
        var $sidebar = $(".control-sidebar");
        $sidebar.empty();

        var $container = $("<div />", {
            class: "p-3 control-sidebar-content",
        });
        $sidebar.append($container);

        // Judul
        $container.append('<h5><i class="fas fa-cog mr-2"></i>Pengaturan Tampilan</h5><hr class="mb-3"/>');

        // Dark Mode dengan Label dan Icon
        var darkModeHtml =
            '<div class="form-group">' +
            '<div class="custom-control custom-switch custom-switch-off-light custom-switch-on-dark">' +
            '<input type="checkbox" class="custom-control-input" id="dark-mode-switch">' +
            '<label class="custom-control-label" for="dark-mode-switch">' +
            '<i class="fas fa-moon mr-2"></i>Mode Gelap' +
            '</label>' +
            '</div>' +
            '</div>';

        $container.append(darkModeHtml);

        // Info tambahan
        $container.append('<hr class="my-3"/><small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Pengaturan akan tersimpan otomatis</small>');

        // Check localStorage untuk dark mode
        if (localStorage.getItem('darkMode') === 'enabled') {
            $('body').addClass('dark-mode');
            $('#dark-mode-switch').prop('checked', true);
        }

        // Script toggle Dark Mode dengan localStorage
        $('#dark-mode-switch').on('change', function() {
            if ($(this).is(':checked')) {
                $('body').addClass('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                $('body').removeClass('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    });
</script>

<!-- Added scroll to top functionality -->
<script>
    $(function() {
        var $scrollBtn = $('#scrollToTop');
        
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $scrollBtn.fadeIn();
            } else {
                $scrollBtn.fadeOut();
            }
        });
        
        $scrollBtn.click(function() {
            $('html, body').animate({scrollTop: 0}, 600);
            return false;
        });
    });
</script>

<!-- Added form validation and loading states -->
<!-- <script>
    $(function() {
        // Form validation
        $('form').on('submit', function() {
            var $btn = $(this).find('button[type="submit"]');
            if ($btn.length) {
                $btn.prop('disabled', true);
                var originalText = $btn.html();
                $btn.data('original-text', originalText);
                $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script> -->

</body>
</html>
