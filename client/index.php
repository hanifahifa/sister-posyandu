<?php 
// Sertakan file logic utama
include "client.php"; 

// Ambil status sesi dari database lokal
$id_posyandu_sesi = $abc->get_id_posyandu_lokal();
$nama_posyandu_sesi = $abc->get_nama_posyandu_lokal();

// Pengecekan sesi: Jika belum login, paksa ke halaman login
if (!$id_posyandu_sesi && (!isset($_GET['page']) || $_GET['page'] != 'login')) {
    header('location:index.php?page=login');
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Si-Posyandu Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* [START] CSS KUSTOM (DARI KODE ASLI ANDA) */
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 50%, #fce4ec 100%); min-height: 100vh; padding-bottom: 40px; }
        .navbar { background: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(20px); box-shadow: 0 2px 20px rgba(233, 30, 99, 0.1); border-bottom: 1px solid rgba(233, 30, 99, 0.1); padding: 1rem 0; }
        .navbar-brand { color: #e91e63 !important; font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .navbar-brand i { font-size: 1.5rem; }
        .nav-link { color: #e91e63 !important; font-weight: 500; padding: 0.5rem 1rem !important; border-radius: 12px; transition: all 0.3s ease; margin: 0 4px; }
        .nav-link:hover { background: rgba(233, 30, 99, 0.1); transform: translateY(-2px); }
        .alert { border: none; border-radius: 16px; padding: 1.2rem 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.08); backdrop-filter: blur(10px); animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: linear-gradient(135deg, #c8e6c9, #a5d6a7); color: #2e7d32; }
        .alert-info { background: linear-gradient(135deg, #f8bbd0, #f48fb1); color: #880e4f; }
        .alert-danger { background: linear-gradient(135deg, #ffcdd2, #ef9a9a); color: #c62828; }
        .card, .welcome-card { border: none; border-radius: 24px; box-shadow: 0 8px 30px rgba(233, 30, 99, 0.15); background: white; overflow: hidden; transition: all 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(233, 30, 99, 0.2); }
        .card .list-group { max-height: 280px; overflow-y: auto; }
        .welcome-card { background: linear-gradient(135deg, #ffffff 0%, #fce4ec 100%); padding: 3rem; text-align: center; margin-bottom: 2rem; }
        .welcome-card h1 { color: #e91e63; font-weight: 700; margin-bottom: 1rem; font-size: 2.5rem; }
        .welcome-card p { color: #ad1457; font-size: 1.1rem; line-height: 1.6; }
        .welcome-icon { font-size: 5rem; color: #f48fb1; margin-bottom: 1.5rem; animation: bounce 2s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .btn { border: none; border-radius: 14px; padding: 0.8rem 1.8rem; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-warning { background: linear-gradient(135deg, #ffd54f, #ffb74d); color: #f57c00; }
        .btn-primary { background: linear-gradient(135deg, #f48fb1, #ec407a); color: white; }
        .btn-success { background: linear-gradient(135deg, #81c784, #66bb6a); color: white; }
        .form-control, .form-select { border: 2px solid #f8bbd0; border-radius: 12px; padding: 0.8rem 1rem; transition: all 0.3s ease; background: rgba(252, 228, 236, 0.3); }
        .form-control:focus, .form-select:focus { border-color: #e91e63; box-shadow: 0 0 0 4px rgba(233, 30, 99, 0.1); background: white; }
        .form-label { color: #ad1457; font-weight: 600; margin-bottom: 0.6rem; }
        .table thead { background: linear-gradient(135deg, #f48fb1, #ec407a); color: white; }
        .section-title { color: #e91e63; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; }
        .section-title i { font-size: 1.8rem; }
        h5 { color: #ad1457; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
        .text-danger { color: #e91e63 !important; }
        .info-box { background: linear-gradient(135deg, #fff9c4, #fff59d); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #fbc02d; color: #f57f17; font-weight: 500; }
        .data-grid { display: grid; gap: 1.5rem; }
        @media (max-width: 768px) { .navbar-nav { margin-top: 1rem; } .welcome-card h1 { font-size: 2rem; } .welcome-icon { font-size: 3.5rem; } }
        /* [END] CSS KUSTOM */
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heart-pulse"></i>
                POSYANDU <?= $id_posyandu_sesi ? strtoupper($nama_posyandu_sesi) : 'UNIT' ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <?php if ($id_posyandu_sesi) { ?>
                        <a class="nav-link" href="?page=home"><i class="fas fa-home"></i> Home</a>
                        <a class="nav-link" href="?page=master"><i class="fas fa-download"></i> Data Master</a>
                        <a class="nav-link" href="?page=input"><i class="fas fa-pen-to-square"></i> Input</a>
                        <a class="nav-link" href="?page=sync"><i class="fas fa-cloud-arrow-up"></i> Sinkronisasi</a>
                        <a class="nav-link" href="?page=laporan_pusat"><i class="fas fa-chart-line"></i> Data Pusat</a>
                        <a class="nav-link" href="proses.php?aksi=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php 
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        // Tampilkan notifikasi status
        if ($status == 'saved') echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil disimpan di lokal.</div>';
        if ($status == 'downloaded') echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data master Balita/Petugas berhasil diperbarui dari Server.</div>';
        if ($status == 'uploaded') echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Laporan berhasil dikirim dan database lokal sudah dibersihkan</div>';
        if ($status == 'failed_download') echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Gagal mengunduh data! Pastikan Server aktif dan Anda sudah login.</div>';
        if ($status == 'loggedout') echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Anda telah berhasil logout.</div>';
        if ($status == 'error_data') echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Data input tidak lengkap.</div>';


        // --- HALAMAN LOGIN (Tampil saat belum ada sesi) ---
        if ($page == 'login') { ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-5">
                    <h3 class="section-title text-center"><i class="fas fa-lock"></i> Login Posyandu Unit</h3>
                    <div class="card p-4">
                        <?php if (isset($_GET['error'])) echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>'; ?>
                        <form action="proses.php" method="POST">
                            <input type="hidden" name="aksi" value="login">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
                        </form>
                    </div>
                    <?php if (!$abc->is_connected()) { ?>
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-circle"></i> KONEKSI LOKAL GAGAL! Aplikasi tidak bisa menyimpan sesi.
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php 
        // --- HALAMAN UTAMA (Tampil setelah Login) ---
        // Semua konten di bawah ini hanya tampil jika sudah ada sesi login yang valid
        } elseif ($id_posyandu_sesi) { 
            
            if ($page == 'home') { ?>
                <div class="welcome-card">
                    <div class="welcome-icon">
                        <i class="fas fa-baby"></i>
                    </div>
                    <h1>Selamat Datang, Kader Posyandu <?= $nama_posyandu_sesi ?>! 💕</h1>
                    <p>Aplikasi ini menjalankan sistem Store-and-Forward untuk mempermudah pencatatan saat Posyandu berlangsung (bisa *OFFLINE*).</p>
                    <p>ID Unit Anda: <b><?= $id_posyandu_sesi ?></b></p>
                </div>

            <?php } elseif ($page == 'master') { ?>
                <h3 class="section-title"><i class="fas fa-database"></i> 1. Data Master</h3>
                <p>Data yang diunduh ini hanya yang terkait dengan Posyandu *<?= $nama_posyandu_sesi ?>*.</p>
                <a href="proses.php?aksi=download_master" class="btn btn-warning mb-4">
                    <i class="fas fa-cloud-download-alt"></i> Update Data Balita/Petugas dari Server
                </a>
                <div class="row data-grid">
                    <div class="col-lg-6 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-child-reaching"></i> Data Balita (Lokal)</h5>
                                <ul class="list-group">
                                    <?php 
                                    $balita = $abc->get_all_balita();
                                    if(!empty($balita)) {
                                        foreach($balita as $b) {
                                            echo "<li class='list-group-item'><i class='fas fa-user'></i> $b->nama_balita ($b->jenis_kelamin) - Ibu: $b->nama_ibu</li>";
                                        }
                                    } else {
                                        echo "<li class='list-group-item text-center'><i class='fas fa-info-circle'></i> Belum ada data balita. Klik Update!</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-user-nurse"></i> Data Petugas (Lokal)</h5>
                                <ul class="list-group">
                                    <?php 
                                    $petugas = $abc->get_all_petugas();
                                    if(!empty($petugas)) {
                                        foreach($petugas as $p) {
                                            echo "<li class='list-group-item'><i class='fas fa-id-badge'></i> $p->nama_petugas ($p->jabatan)</li>";
                                        }
                                    } else {
                                        echo "<li class='list-group-item text-center'><i class='fas fa-info-circle'></i> Belum ada data petugas. Klik Update!</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            <?php } elseif ($page == 'input') { ?>
                <h3 class="section-title"><i class="fas fa-clipboard-check"></i> 2. Input Pemeriksaan (Mode Offline)</h3>
                <div class="card">
                    <div class="card-body">
                        <form action="proses.php" method="POST">
                            <input type="hidden" name="aksi" value="tambah">
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-baby"></i> Pilih Balita</label>
                                <select name="id_balita" class="form-select" required>
                                    <option value="">-- Pilih Balita --</option>
                                    <?php foreach($abc->get_all_balita() as $b) {
                                        echo "<option value='$b->id_balita'>$b->nama_balita</option>";
                                    } ?>
                                </select>
                                <small class="text-danger"><i class="fas fa-info-circle"></i> Pastikan sudah download master data</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-calendar-alt"></i> Tanggal Periksa</label>
                                <input type="date" name="tgl_periksa" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-weight"></i> Berat Badan (kg)</label>
                                    <input type="number" step="0.1" name="berat_badan" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-ruler-vertical"></i> Tinggi Badan (cm)</label>
                                    <input type="number" step="0.1" name="tinggi_badan" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-notes-medical"></i> Catatan Gizi</label>
                                <textarea name="catatan_gizi" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-user-doctor"></i> Petugas Pemeriksa</label>
                                <select name="id_petugas" class="form-select" required>
                                    <option value="">-- Pilih Petugas --</option>
                                    <?php foreach($abc->get_all_petugas() as $p) {
                                        echo "<option value='$p->id_petugas'>$p->nama_petugas</option>";
                                    } ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-save"></i> Simpan ke Database Lokal
                            </button>
                        </form>
                    </div>
                </div>

            <?php } elseif ($page == 'sync') { 
                $data = $abc->tampil_pemeriksaan_lokal();
                ?>
                <h3 class="section-title"><i class="fas fa-sync-alt"></i> 3. Sinkronisasi Data</h3>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i> Data di bawah ini adalah data <strong>Pending</strong> yang tersimpan di lokal (Offline).
                </div>
                
                <a href="proses.php?aksi=upload_laporan" class="btn btn-primary btn-lg mb-4" onclick="return confirm('Apakah Anda yakin mengirim semua data lokal ini ke Server Pusat?')">
                     <i class="fas fa-cloud-upload-alt"></i> Upload Laporan ke Server
                </a>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                <th><i class="fas fa-child"></i> Nama Balita</th>
                                <th><i class="fas fa-weight"></i> BB / TB</th>
                                <th><i class="fas fa-user-nurse"></i> Petugas</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $d) { ?>
                            <tr>
                                <td><?= $d->tgl_periksa ?></td>
                                <td><?= $d->nama_balita ?></td>
                                <td><?= $d->berat_badan ?> kg / <?= $d->tinggi_badan ?> cm</td>
                                <td><?= $d->nama_petugas ?></td>
                                <td><span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> PENDING</span></td>
                            </tr>
                            <?php } ?>
                            <?php if(count($data) == 0) echo "<tr><td colspan='5' class='text-center'><i class='fas fa-check-circle'></i> Semua data sudah tersinkronisasi (Database Lokal Clean)</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

            <?php } elseif ($page == 'laporan_pusat') { 
                $data_server = $abc->tampil_data_server();
                ?>
                <h3 class="section-title"><i class="fas fa-server"></i> 4. Cek Data Pusat</h3>
                <div class="alert alert-success">
                    <i class="fas fa-cloud"></i> Data ini diambil *LANGSUNG* dari Server Pusat via API (Semua data dari semua Posyandu).
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Tgl Periksa</th>
                                <th><i class="fas fa-baby"></i> Nama Balita</th>
                                <th><i class="fas fa-weight"></i> Berat (kg)</th>
                                <th><i class="fas fa-ruler-vertical"></i> Tinggi (cm)</th>
                                <th><i class="fas fa-notes-medical"></i> Catatan</th>
                                <th><i class="fas fa-user-doctor"></i> Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($data_server) && is_array($data_server)) {
                                foreach ($data_server as $d) { ?>
                                <tr>
                                    <td><?= $d->tgl_periksa ?></td>
                                    <td><?= $d->nama_balita ?></td>
                                    <td><?= $d->berat_badan ?></td>
                                    <td><?= $d->tinggi_badan ?></td>
                                    <td><?= $d->catatan_gizi ?></td>
                                    <td><?= $d->nama_petugas ?></td>
                                </tr>
                                <?php } 
                            } else { ?>
                                <tr><td colspan='6' class='text-center'><i class="fas fa-exclamation-circle"></i> Belum ada data di Server Pusat. Silakan lakukan sinkronisasi (Upload) terlebih dahulu.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } 
        } // Akhir dari blok if ($id_posyandu_sesi)
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>