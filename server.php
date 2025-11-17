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

    if ($aksi == 'tambah_pemeriksaan') {
        // Mapping data JSON ke Array PHP
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
    } elseif ($aksi == 'hapus') {
        $db->hapus_pemeriksaan($data->id_periksa);
        echo json_encode(["status" => "sukses", "pesan" => "Data dihapus"]);
    }

    unset($postdata, $data, $data_insert, $aksi, $db);
}

// --- PROSES GET (Mengirim Data ke Client / Tampil di Web) ---
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Jika Client minta data master (balita/petugas)
    if (isset($_GET['tabel'])) {
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