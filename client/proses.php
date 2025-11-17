<?php
include "client.php";

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : (isset($_GET['aksi']) ? $_GET['aksi'] : '');

if ($aksi == 'tambah') {
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
} 

elseif ($aksi == 'download_master') {
    // Download data Balita & Petugas dari Server
    $abc->sync_download_master('balita');
    $abc->sync_download_master('petugas');
    header('location:index.php?page=master&status=downloaded');
} 

elseif ($aksi == 'upload_laporan') {
    // Upload data pemeriksaan ke Server
    $abc->sync_upload_laporan();
    header('location:index.php?page=sync&status=uploaded');
}
?>