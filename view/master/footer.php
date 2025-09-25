</div>
<!-- /.content-wrapper -->

<footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.1.0
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#tabelPelatih').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false
        });
    });
</script>

<script>
    // Tambahkan event click agar baris berubah putih saat diklik
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#tabelPelatih tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                rows.forEach(r => r.classList.remove('active'));
                row.classList.add('active');
            });
        });
    });
</script>


</body>

</html>