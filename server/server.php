<?php
error_reporting(1);
include "database.php";
$db = new Database();

// Header agar bisa diakses dari beda domain/IP
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

$postdata = file_get_contents("php://input");

// --- PROSES POST (Menerima Data / Sinkronisasi Masuk) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode($postdata);

    // Ambil parameter aksi
    $aksi = $data->aksi;

    // FUNGSI BARU: Aksi Login
    // FUNGSI BARU: Aksi Login (SETELAH PERBAIKAN SEMENTARA)
    if ($aksi == 'login') {
        $username = $data->username; // <--- HAPUS PEMANGGILAN $db->filter()
        $password = $data->password; // <--- HAPUS PEMANGGILAN $db->filter()

        $user_data = $db->cek_login($username, $password);
        // ...

        if ($user_data) {
            echo json_encode(["status" => "sukses", "data" => $user_data]);
        } else {
            echo json_encode(["status" => "gagal", "pesan" => "Username atau Password salah."]);
        }
    }
    // FUNGSI LAMA: Tambah Pemeriksaan
    elseif ($aksi == 'tambah_pemeriksaan') {
        $data_insert = array(
            'tgl_periksa' => $data->tgl_periksa,
            'berat_badan' => $data->berat_badan,
            'tinggi_badan' => $data->tinggi_badan,
            'catatan_gizi' => $data->catatan_gizi,
            'id_balita' => $data->id_balita,
            'id_petugas' => $data->id_petugas
        );
        $db->tambah_pemeriksaan($data_insert);
        echo json_encode(["status" => "sukses", "pesan" => "Data pemeriksaan berhasil disinkronkan"]);
    }
    // FUNGSI LAMA: Hapus
    elseif ($aksi == 'hapus') {
        $db->hapus_pemeriksaan($data->id_periksa);
        echo json_encode(["status" => "sukses", "pesan" => "Data dihapus"]);
    }

    unset($postdata, $data, $data_insert, $aksi, $db);
}

// --- PROSES GET (Mengirim Data ke Client / Tampil di Web) ---
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // FUNGSI MODIFIKASI: Jika Client minta data master (balita/petugas) DENGAN ID POSYANDU
    if (isset($_GET['tabel']) && isset($_GET['id_posyandu'])) {
        $tabel = $_GET['tabel'];
        $id_posyandu = $db->filter($_GET['id_posyandu']);

        // Panggil fungsi yang membatasi data berdasarkan ID Posyandu
        $data = $db->tampil_semua_master_by_posyandu($tabel, $id_posyandu);
        echo json_encode($data);
    }

    // FUNGSI ASAL: Jika Client minta data master (tanpa ID Posyandu, menggunakan fungsi lama)
    elseif (isset($_GET['tabel'])) {
        $tabel = $_GET['tabel'];
        $data = $db->tampil_semua_master($tabel);
        echo json_encode($data);
    }

    // Default: Tampilkan data pemeriksaan (Dashboard Server)
    else {
        $data = $db->tampil_semua_pemeriksaan();
        echo json_encode($data);
    }

    unset($postdata, $data, $db);
}
