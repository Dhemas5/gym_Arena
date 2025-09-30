</div>
<!-- /.content-wrapper -->
<footer class="main-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <strong>&copy; 2024 GymApp.</strong> All rights reserved.
            </div>
            <div class="col-sm-6 text-right d-none d-sm-block">
                <b>Version</b> 1.0
            </div>
        </div>
    </div>
</footer>


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
<!-- 
<script src="../../../assets/assets_admin/dist/js/demo.js"></script>
<script src="../../../assets/assets_admin/dist/js/pages/dashboard2.js"></script> -->

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
<script>
    $(function() {
        var table = $("#tabelPelatih").DataTable({
            paging: true,
            lengthChange: false,
            searching: false,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
        });

        table.buttons().container().appendTo('#tabelPelatih_wrapper .col-md-6:eq(0)');

    });
</script>

<script>
    $(function() {
        var $sidebar = $(".control-sidebar");
        $sidebar.empty(); // kosongkan isi default

        var $container = $("<div />", {
            class: "p-3 control-sidebar-content",
        });
        $sidebar.append($container);

        // Judul
        $container.append('<h5>Pengaturan Tampilan</h5><hr class="mb-2"/>');

        // Dark Mode dengan Label
        var darkModeHtml =
            '<div class="form-group">' +
            '<div class="custom-control custom-switch">' +
            '<input type="checkbox" class="custom-control-input" id="dark-mode-switch">' +
            '<label class="custom-control-label" for="dark-mode-switch">Aktifkan Dark Mode</label>' +
            '</div>' +
            '</div>';

        $container.append(darkModeHtml);

        // Script toggle Dark Mode
        $('#dark-mode-switch').on('change', function() {
            if ($(this).is(':checked')) {
                $('body').addClass('dark-mode');
            } else {
                $('body').removeClass('dark-mode');
            }
        });
    });
</script>
</body>

</html>