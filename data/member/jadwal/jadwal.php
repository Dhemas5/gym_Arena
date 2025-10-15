<?php
require "../../../setting/session.php";
checkSession("member");
?>
<?php include '../../../view/master_member/header.php'; ?>
<?php
require "../../../setting/koneksi.php";

// 🔹 Ambil semua jadwal dari tabel
$query = "SELECT j.*, k.nama_kategori, i.nama_instruktur, DAYNAME(j.tanggal) AS hari
          FROM tbl_jadwal_kelas j
          JOIN tbl_kategori k ON j.id_kategori = k.id_kategori
          JOIN tbl_instruktur i ON j.id_instruktur = i.id_instruktur
          ORDER BY j.tanggal, j.jam_mulai";
$result = mysqli_query($con, $query);

// 🔹 Simpan hasil jadwal ke array [hari][] untuk ditampilkan per waktu
$schedule = [];
$timeSlots = []; // untuk menyimpan semua waktu unik dari DB

while ($row = mysqli_fetch_assoc($result)) {
    $day = $row['hari'];
    $start = $row['jam_mulai'];
    $end = $row['jam_selesai'];

    $schedule[$day][$start] = [
        'kategori' => $row['nama_kategori'],
        'instruktur' => $row['nama_instruktur'],
        'start' => $start,
        'end' => $end
    ];

    $key = $start . '|' . $end;
    if (!in_array($key, $timeSlots)) {
        $timeSlots[] = $key;
    }
}

usort($timeSlots, function ($a, $b) {
    [$aStart] = explode('|', $a);
    [$bStart] = explode('|', $b);
    return strcmp($aStart, $bStart);
});

$kategori_result = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
?>

<!-- ====== AdminLTE Content Wrapper ====== -->
<div class="content-wrapper">
    <!-- Header Page -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Daftar Pengguna Gym</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Filter kategori -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filter Kategori</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#" data-tsfilter="all">Semua</a></li>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)) : ?>
                            <li class="nav-item">
                                <a class="nav-link text-capitalize" href="#" data-tsfilter="<?= strtolower($kategori['nama_kategori']) ?>">
                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Jadwal -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock"></i> Jadwal Mingguan</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead class="thead-dark">
                            <tr>
                                <th>Waktu</th>
                                <th>Senin</th>
                                <th>Selasa</th>
                                <th>Rabu</th>
                                <th>Kamis</th>
                                <th>Jumat</th>
                                <th>Sabtu</th>
                                <th>Minggu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                            foreach ($timeSlots as $slot) {
                                [$start, $end] = explode('|', $slot);
                                echo "<tr>";
                                echo "<td><b>" . date("H:i", strtotime($start)) . " - " . date("H:i", strtotime($end)) . "</b></td>";

                                foreach ($days as $day) {
                                    if (isset($schedule[$day][$start])) {
                                        $data = $schedule[$day][$start];
                                        $kategori_lower = strtolower($data['kategori']);
                                        echo "
                            <td class='ts-meta' data-tsmeta='{$kategori_lower}'>
                              <div class='p-1'>
                                <h6 class='mb-0 text-primary'>{$data['kategori']}</h6>
                                <small class='text-muted'>{$data['instruktur']}</small>
                              </div>
                            </td>";
                                    } else {
                                        echo "<td class='bg-light'></td>";
                                    }
                                }

                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Script filter kategori -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterButtons = document.querySelectorAll("[data-tsfilter]");
        const scheduleCells = document.querySelectorAll(".ts-meta");

        filterButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const filter = this.getAttribute("data-tsfilter");

                filterButtons.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");

                scheduleCells.forEach(cell => {
                    if (filter === "all" || cell.getAttribute("data-tsmeta") === filter) {
                        cell.style.display = "";
                    } else {
                        cell.style.display = "none";
                    }
                });
            });
        });
    });
</script>

<?php include '../../../view/master_member/footer.php'; ?>