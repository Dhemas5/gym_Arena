<?php
require "../../../setting/session.php";
checkSession("member"); // hanya member boleh masuk
?>
<?php include '../../../view/master_member/header.php'; ?>
<?php include '../../../view/master_member/navbar.php'; ?>
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

    // Simpan kombinasi waktu unik (supaya jadi baris tabel)
    $key = $start . '|' . $end;
    if (!in_array($key, $timeSlots)) {
        $timeSlots[] = $key;
    }
}

// 🔹 Urutkan waktu mulai agar rapi di tabel
usort($timeSlots, function ($a, $b) {
    [$aStart] = explode('|', $a);
    [$bStart] = explode('|', $b);
    return strcmp($aStart, $bStart);
});

// 🔹 Ambil kategori
$kategori_result = mysqli_query($con, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
?>

<section class="breadcrumb-section set-bg" data-setbg="../../../assets/assets_member/img/breadcrumb-bg.jpg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb-text">
                    <h2>Timetable</h2>
                    <div class="bt-option">
                        <a href="./index.html">Home</a>
                        <a href="#">Pages</a>
                        <span>Services</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="class-timetable-section spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="section-title">
                    <span>Find Your Time</span>
                    <h2>Find Your Time</h2>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="table-controls">
                    <ul>
                        <li class="active" data-tsfilter="all">All Event</li>
                        <?php while ($kategori = mysqli_fetch_assoc($kategori_result)) : ?>
                            <li data-tsfilter="<?= strtolower($kategori['nama_kategori']) ?>">
                                <?= htmlspecialchars($kategori['nama_kategori']) ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="class-timetable">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                                <th>Sunday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                            foreach ($timeSlots as $slot) {
                                [$start, $end] = explode('|', $slot);
                                echo "<tr>";
                                echo "<td class='class-time'>" . date("g.ia", strtotime($start)) . " - " . date("g.ia", strtotime($end)) . "</td>";

                                foreach ($days as $day) {
                                    if (isset($schedule[$day][$start])) {
                                        $data = $schedule[$day][$start];
                                        $kategori_lower = strtolower($data['kategori']);
                                        echo "
                                            <td class='dark-bg hover-bg ts-meta' data-tsmeta='{$kategori_lower}'>
                                                <h5>{$data['kategori']}</h5>
                                                <span>{$data['instruktur']}</span>
                                            </td>";
                                    } else {
                                        echo "<td class='blank-td'></td>";
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
    </div>
</section>

<!-- 🔹 Script untuk filter kategori -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterButtons = document.querySelectorAll(".table-controls li");
        const scheduleCells = document.querySelectorAll(".class-timetable td.ts-meta");

        filterButtons.forEach(button => {
            button.addEventListener("click", function() {
                const filter = this.getAttribute("data-tsfilter");

                // hapus class active dari semua
                filterButtons.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");

                // tampilkan / sembunyikan berdasarkan kategori
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