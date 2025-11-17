<?php include "client.php"; ?>
<!doctype html>
<html>
<head>
    <title>Si-Posyandu Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">POSYANDU MELATI (CLIENT)</a>
            <div class="navbar-nav">
                <a class="nav-link" href="?page=home">Home</a>
                <a class="nav-link" href="?page=master">1. Data Master (Download)</a>
                <a class="nav-link" href="?page=input">2. Input Pemeriksaan</a>
                <a class="nav-link" href="?page=sync">3. Sinkronisasi (Upload)</a>
                <a class="nav-link" href="?page=laporan_pusat">4. Cek Data Pusat</a> 
            </div>
        </div>
    </nav>

    <div class="container">
        <?php 
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        // Tampilkan notifikasi status
        if ($status == 'saved') echo '<div class="alert alert-success">Data berhasil disimpan di lokal (offline).</div>';
        if ($status == 'downloaded') echo '<div class="alert alert-success">Data master Balita/Petugas berhasil diperbarui dari Server.</div>';
        if ($status == 'uploaded') echo '<div class="alert alert-success">Laporan berhasil dikirim dan database lokal sudah dibersihkan (Clean).</div>';

        if ($page == 'home') { ?>
            <div class="p-5 bg-light rounded-3">
                <h1>Selamat Datang, Kader!</h1>
                <p>Aplikasi ini menjalankan sistem Store-and-Forward (simpan lalu kirim) untuk Posyandu.</p>
                
            </div>

        <?php } elseif ($page == 'master') { ?>
            <h3>1. Data Master (Download dari Pusat)</h3>
            <a href="proses.php?aksi=download_master" class="btn btn-warning mb-3">
                <i class="icon-download"></i> Update Data Balita/Petugas dari Server
            </a>
            <div class="row">
                <div class="col-md-6">
                    <h5>Data Balita (Lokal)</h5>
                    <ul class="list-group">
                        <?php foreach($abc->get_all_balita() as $b) {
                            echo "<li class='list-group-item'>$b->nama_balita ($b->jenis_kelamin) - Ibu: $b->nama_ibu</li>";
                        } ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Data Petugas (Lokal)</h5>
                    <ul class="list-group">
                        <?php foreach($abc->get_all_petugas() as $p) {
                            echo "<li class='list-group-item'>$p->nama_petugas ($p->jabatan)</li>";
                        } ?>
                    </ul>
                </div>
            </div>

        <?php } elseif ($page == 'input') { ?>
            <h3>2. Input Pemeriksaan (Mode Offline)</h3>
            <div class="card p-4">
                <form action="proses.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    
                    <div class="mb-3">
                        <label>Pilih Balita</label>
                        <select name="id_balita" class="form-control" required>
                            <option value="">-- Pilih Balita --</option>
                            <?php foreach($abc->get_all_balita() as $b) {
                                echo "<option value='$b->id_balita'>$b->nama_balita</option>";
                            } ?>
                        </select>
                        <small class="text-danger">*Pastikan sudah download master data</small>
                    </div>

                    <div class="mb-3">
                        <label>Tanggal Periksa</label>
                        <input type="date" name="tgl_periksa" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="row">
                        <div class="col">
                            <label>Berat (kg)</label>
                            <input type="number" step="0.1" name="berat_badan" class="form-control" required>
                        </div>
                        <div class="col">
                            <label>Tinggi (cm)</label>
                            <input type="number" step="0.1" name="tinggi_badan" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label>Catatan Gizi</label>
                        <textarea name="catatan_gizi" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Petugas Pemeriksa</label>
                        <select name="id_petugas" class="form-control" required>
                            <?php foreach($abc->get_all_petugas() as $p) {
                                echo "<option value='$p->id_petugas'>$p->nama_petugas</option>";
                            } ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan ke Database Lokal</button>
                </form>
            </div>

        <?php } elseif ($page == 'sync') { 
            $data = $abc->tampil_pemeriksaan_lokal();
            ?>
            <h3>3. Sinkronisasi Data (Upload Laporan)</h3>
            <div class="alert alert-info">
                Data di bawah ini adalah data *Pending* yang tersimpan di lokal (Offline).
            </div>
            
            <a href="proses.php?aksi=upload_laporan" class="btn btn-primary btn-lg mb-3" onclick="return confirm('Apakah Anda yakin mengirim semua data lokal ini ke Server Pusat?')">
                 <i class="icon-upload"></i> Upload Laporan ke Server (Pusat)
            </a>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tgl</th>
                        <th>Nama Balita</th>
                        <th>BB / TB</th>
                        <th>Petugas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $d) { ?>
                    <tr>
                        <td><?= $d->tgl_periksa ?></td>
                        <td><?= $d->nama_balita ?></td>
                        <td><?= $d->berat_badan ?> kg / <?= $d->tinggi_badan ?> cm</td>
                        <td><?= $d->nama_petugas ?></td>
                        <td><span class="badge bg-warning text-dark">PENDING</span></td>
                    </tr>
                    <?php } ?>
                    <?php if(count($data) == 0) echo "<tr><td colspan='5' class='text-center'>Semua data sudah tersinkronisasi (Database Lokal Clean)</td></tr>"; ?>
                </tbody>
            </table>

        <?php } elseif ($page == 'laporan_pusat') { 
            $data_server = $abc->tampil_data_server();
            ?>
            <h3>4. Cek Data Pusat (Laporan Akhir)</h3>
            <div class="alert alert-success">
                Data ini diambil *LANGSUNG* dari Server Pusat via API.
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tgl Periksa</th>
                        <th>Nama Balita</th>
                        <th>Berat (kg)</th>
                        <th>Tinggi (cm)</th>
                        <th>Catatan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($data_server)) {
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
                        <tr><td colspan='6' class='text-center'>Belum ada data di Server Pusat. Silakan lakukan sinkronisasi (Upload) terlebih dahulu.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>