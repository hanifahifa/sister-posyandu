<?php
include "client.php";

// Pastikan koneksi lokal berhasil sebelum memproses aksi
if (!$abc->is_connected()) {
    header('location:index.php?page=login&error=' . urlencode('Koneksi database lokal gagal. Silakan cek XAMPP/Laragon Anda.'));
    exit;
}

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : (isset($_GET['aksi']) ? $_GET['aksi'] : '');

if ($aksi == 'tambah') {
    if (isset($_POST['tgl_periksa']) && isset($_POST['id_balita'])) {
        $data = array(
            "tgl_periksa" => $_POST['tgl_periksa'],
            "berat_badan" => $_POST['berat_badan'],
            "tinggi_badan" => $_POST['tinggi_badan'],
            "catatan_gizi" => $_POST['catatan_gizi'],
            "id_balita" => $_POST['id_balita'],
            "id_petugas" => $_POST['id_petugas']
        );
        $abc->tambah_pemeriksaan_lokal($data);
        header('location:index.php?page=input&status=saved');
    } else {
        header('location:index.php?page=input&status=error_data');
    }
} 

elseif ($aksi == 'download_master') {
    $success_balita = $abc->sync_download_master('balita');
    $success_petugas = $abc->sync_download_master('petugas');

    if ($success_balita && $success_petugas) {
        header('location:index.php?page=master&status=downloaded');
    } else {
        header('location:index.php?page=master&status=failed_download');
    }
} 

elseif ($aksi == 'upload_laporan') {
    $abc->sync_upload_laporan();
    header('location:index.php?page=sync&status=uploaded');
}

// --- Aksi Login ---
elseif ($aksi == 'login') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $result = $abc->cek_login_server($username, $password);
        
        if (isset($result->status) && $result->status == 'sukses') {
            if (isset($result->data->id_posyandu) && isset($result->data->nama_posyandu)) {
                $abc->simpan_sesi_lokal($result->data->id_posyandu, $result->data->nama_posyandu);
                header('location:index.php?page=home');
            } else {
                header('location:index.php?page=login&error=' . urlencode('Gagal mendapatkan ID Posyandu dari Server.'));
            }
        } else {
            $error_msg = isset($result->pesan) ? $result->pesan : 'Login gagal. Cek koneksi atau kredensial.';
            header('location:index.php?page=login&error=' . urlencode($error_msg));
        }
    } else {
        header('location:index.php?page=login&error=' . urlencode('Username dan password harus diisi.'));
    }
}

// --- Aksi Logout ---
elseif ($aksi == 'logout') {
    $abc->logout_lokal(); 
    header('location:index.php?page=login&status=loggedout');
}
?>