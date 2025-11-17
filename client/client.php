<?php
error_reporting(1);

class Client
{
    private $host = "localhost";
    private $dbname = "db_client_posyandu"; // DB Lokal
    private $user = "root";
    private $password = ""; 
    private $conn;
    private $url_server; // Alamat Server Pusat

    public function __construct($url)
    {
        $this->url_server = $url;
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->user, $this->password);
        } catch (PDOException $e) {
            echo "Koneksi Lokal Gagal: " . $e->getMessage();
        }
    }

    // --- 1. FITUR DOWNLOAD MASTER DATA (Server -> Client) ---
    // Mengambil data balita dan petugas dari server dan disimpan lokal
    public function sync_download_master($tabel)
    {
        // Ambil data dari Server via API
        $client = curl_init($this->url_server . "?tabel=" . $tabel);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($client);
        curl_close($client);
        $data = json_decode($response);

        if ($data) {
            // Kosongkan tabel lokal dulu (Reset)
            $this->conn->query("DELETE FROM $tabel");
            
            // Masukkan data baru dari server
            foreach ($data as $r) {
                if ($tabel == 'balita') {
                    $sql = "INSERT INTO balita VALUES (?,?,?,?,?,?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$r->id_balita, $r->nama_balita, $r->tgl_lahir, $r->jenis_kelamin, $r->nama_ibu, $r->id_posyandu]);
                } elseif ($tabel == 'petugas') {
                    $sql = "INSERT INTO petugas VALUES (?,?,?,?,?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$r->id_petugas, $r->nama_petugas, $r->jabatan, $r->no_hp, $r->id_posyandu]);
                }
            }
            return true;
        }
        return false;
    }

    // --- 2. FITUR CRUD LOKAL (Input Offline) ---
    
    // Ambil list balita lokal untuk dropdown
    public function get_all_balita() {
        $q = $this->conn->query("SELECT * FROM balita");
        return $q->fetchAll(PDO::FETCH_OBJ);
    }

    // Ambil list petugas lokal untuk dropdown
    public function get_all_petugas() {
        $q = $this->conn->query("SELECT * FROM petugas");
        return $q->fetchAll(PDO::FETCH_OBJ);
    }

    public function tambah_pemeriksaan_lokal($data)
    {
        $sql = "INSERT INTO pemeriksaan (tgl_periksa, berat_badan, tinggi_badan, catatan_gizi, id_balita, id_petugas) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['tgl_periksa'], $data['berat_badan'], $data['tinggi_badan'], 
            $data['catatan_gizi'], $data['id_balita'], $data['id_petugas']
        ]);
    }

    public function tampil_pemeriksaan_lokal()
    {
        // Join tabel lokal untuk menampilkan nama Balita/Petugas
        $sql = "SELECT p.*, b.nama_balita, pt.nama_petugas 
                FROM pemeriksaan p 
                LEFT JOIN balita b ON p.id_balita = b.id_balita
                LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas
                ORDER BY p.id_periksa DESC";
        $q = $this->conn->query($sql);
        return $q->fetchAll(PDO::FETCH_OBJ);
    }

    // --- 3. FITUR UPLOAD DATA (Client -> Server) ---
    public function sync_upload_laporan()
    {
        // 1. Ambil semua data pemeriksaan lokal
        $data_lokal = $this->tampil_pemeriksaan_lokal();

        foreach ($data_lokal as $row) {
            // Siapkan data JSON seperti yang diminta Server
            $data_kirim = array(
                "aksi" => "tambah_pemeriksaan",
                "tgl_periksa" => $row->tgl_periksa,
                "berat_badan" => $row->berat_badan,
                "tinggi_badan" => $row->tinggi_badan,
                "catatan_gizi" => $row->catatan_gizi,
                "id_balita" => $row->id_balita,
                "id_petugas" => $row->id_petugas
            );

            // Kirim pakai cURL POST
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $this->url_server);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data_kirim));
            $response = curl_exec($c);
            curl_close($c);
            
            // Setelah terkirim, hapus dari lokal (logika Store-and-Forward)
            $res = json_decode($response);
            if (isset($res->status) && $res->status == 'sukses') {
                $del = $this->conn->prepare("DELETE FROM pemeriksaan WHERE id_periksa = ?");
                $del->execute([$row->id_periksa]);
            }
        }
        return true;
    }

    // --- 4. FITUR CEK DATA PUSAT (GET Live Data) ---
    public function tampil_data_server()
    {
        // Melakukan GET request ke URL Server Pusat (tanpa parameter, default GET)
        $client = curl_init($this->url_server);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($client);
        curl_close($client);
        
        // Server merespon dengan data JSON yang sudah di-JOIN
        $data = json_decode($response);
        
        return $data;
        unset($client, $response, $data);
    }
}

// SETUP URL SERVER (PENTING: Pastikan ini sesuai dengan IP Server jika beda laptop)
$url_server = 'http://10.10.20.148/sister-uas/server.php';
$abc = new Client($url_server);
?>