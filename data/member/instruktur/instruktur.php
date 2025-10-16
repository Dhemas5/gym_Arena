<?php
require "../../../setting/session.php";
checkSession("member"); // hanya member boleh masuk
?>
<?php include '../../../view/master_member/header.php'; ?>
<?php
require "../../../setting/koneksi.php"; ?>

<!-- konten.php -->
<div class="content-wrapper">
    <!-- Header Halaman -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Daftar Pengguna Gym</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="../../../data/member/beranda/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten utama -->
    <section class="content pt-3">
        <div class="container-fluid">
            <h4 class="text-center mb-4"><b>Daftar Instruktur Arena Gym Fit Club</b></h4>
            <div class="row justify-content-center">
                <?php
                $query = mysqli_query($con, "SELECT * FROM tbl_instruktur ORDER BY nama_instruktur ASC");

                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        $id_instruktur = htmlspecialchars($row['id_instruktur']);
                        $nama_instruktur = htmlspecialchars($row['nama_instruktur']);
                        $spesialisasi = htmlspecialchars($row['spesialisasi']);
                        $no_hp = htmlspecialchars($row['no_hp']);
                        $email = htmlspecialchars($row['email']);
                        $catatan = htmlspecialchars($row['catatan']);

                        $foto = !empty($row['foto'])
                            ? "../../../data/admin/img/" . $row['foto']
                            : "../../../data/admin/img/default.png";

                        // Format nomor ke internasional untuk WhatsApp
                        $no_wa = preg_replace('/[^0-9]/', '', $no_hp);
                        if (substr($no_wa, 0, 1) === '0') {
                            $no_wa = '62' . substr($no_wa, 1);
                        }
                ?>
                        <!-- Kartu Instruktur -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card position-relative shadow-sm border-0" style="border-radius: 15px; overflow:hidden;">
                                <!-- Ribbon Spesialisasi -->
                                <div class="ribbon-wrapper ribbon-lg">
                                    <div class="ribbon bg-primary text-sm">
                                        <?= $spesialisasi; ?>
                                    </div>
                                </div>

                                <div class="card-body text-center p-4">
                                    <!-- Foto Kotak -->
                                    <img src="<?= $foto; ?>" alt="<?= $nama_instruktur; ?>"
                                        class="img-fluid mb-3"
                                        style="width: 140px; height: 140px; object-fit: cover; border-radius: 10px; box-shadow: 0 3px 6px rgba(0,0,0,0.2);">

                                    <!-- Nama -->
                                    <h5 class="mb-2 text-dark font-weight-bold"><?= $nama_instruktur; ?></h5>

                                    <!-- Kontak -->
                                    <div class="mb-2">
                                        <!-- WhatsApp -->
                                        <a href="https://wa.me/<?= $no_wa; ?>?text=Halo%20<?= urlencode($nama_instruktur); ?>,%20saya%20ingin%20bertanya."
                                            target="_blank"
                                            class="text-success mx-2"
                                            title="Hubungi via WhatsApp">
                                            <i class="fab fa-whatsapp fa-lg"></i>
                                        </a>

                                        <!-- Email -->
                                        <a href="mailto:<?= $email; ?>" class="text-primary mx-2" title="Kirim Email">
                                            <i class="fas fa-envelope fa-lg"></i>
                                        </a>
                                    </div>

                                    <!-- Catatan -->
                                    <p class="text-muted small mb-0"><i class="fas fa-sticky-note"></i> <?= $catatan; ?></p>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="col-12 text-center"><p class="text-muted">Belum ada data instruktur.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>
</div>

<?php include '../../../view/master_member/footer.php'; ?>

<!-- Efek tambahan -->
<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
    }

    .ribbon {
        letter-spacing: 0.4px;
        font-weight: 600;
    }
</style>