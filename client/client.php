<?php
error_reporting(1);

class Client
{
    private $host = "localhost";
    private $dbname = "db_client_posyandu"; // DB Lokal
    private $user = "root";
    private $password = ""; 
    private $conn = null; // Diinisialisasi null untuk keamanan
    private $url_server; 

    public function __construct($url)
    {
        $this->url_server = $url;
        try {
            // Jika koneksi berhasil, $this->conn diisi objek PDO
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->user, $this->password);
        } catch (PDOException $e) {
            // Jika koneksi gagal, $this->conn tetap null
        }
    }
    
    // FUNGSI HELPER: Cek status koneksi lokal (Penting untuk menghindari error fatal)
    public function is_connected() {
        return $this->conn !== null;
    }

    // --- FUNGSI LOGIN / SESI ---
    
    // 1. Cek Login ke Server melalui API POST
    public function cek_login_server($username, $password)
    {
        // Data dikirim ke Server dengan aksi 'login'
        $data_kirim = array("aksi" => "login", "username" => $username, "password" => $password);
        
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $this->url_server);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data_kirim));
        $response = curl_exec($c);
        curl_close($c);

        $res = json_decode($response);
        return $res;
    }
    
    // 2. Menyimpan sesi ID dan Nama Posyandu di database lokal (Tabel 'setting')
    public function simpan_sesi_lokal($id_posyandu, $nama_posyandu)
    {
        if (!$this->is_connected()) return;
        
        $this->conn->query("DELETE FROM setting"); // Hapus sesi lama
        
        $sql_id = "INSERT INTO setting (key_name, key_value) VALUES ('id_posyandu', ?)";
        $stmt_id = $this->conn->prepare($sql_id);
        $stmt_id->execute([$id_posyandu]);
        
        $sql_nama = "INSERT INTO setting (key_name, key_value) VALUES ('nama_posyandu', ?)";
        $stmt_nama = $this->conn->prepare($sql_nama);
        $stmt_nama->execute([$nama_posyandu]);
    }

    // 3. Menghapus sesi lokal (Aksi Logout)
    public function logout_lokal()
    {
        if (!$this->is_connected()) return;
        $this->conn->query("DELETE FROM setting");
    }
    
    // 4. Mendapatkan ID Posyandu dari sesi lokal
    public function get_id_posyandu_lokal()
    {
        if (!$this->is_connected()) return null;
        $query = $this->conn->query("SELECT key_value FROM setting WHERE key_name = 'id_posyandu'");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['key_value'] : null;
    }

    // 5. Mendapatkan Nama Posyandu dari sesi lokal
    public function get_nama_posyandu_lokal()
    {
        if (!$this->is_connected()) return null;
        $query = $this->conn->query("SELECT key_value FROM setting WHERE key_name = 'nama_posyandu'");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['key_value'] : null;
    }

    // --- FUNGSI DOWNLOAD MASTER DATA (MODIFIKASI: Mengirim ID Posyandu) ---
    public function sync_download_master($tabel)
    {
        if (!$this->is_connected()) return false;
        
        $id_posyandu = $this->get_id_posyandu_lokal(); 
        if (!$id_posyandu) return false; // Gagal jika belum login

        // MENGIRIM ID POSYANDU BERSAMA URL KE SERVER
        $url_lengkap = $this->url_server . "?tabel=" . $tabel . "&id_posyandu=" . $id_posyandu;
        $client = curl_init($url_lengkap);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($client);
        curl_close($client);
        $data = json_decode($response);
        
        // Pengecekan respons server
        if (is_array($data)) {
            $this->conn->query("DELETE FROM $tabel"); 
            
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
            return true; // Sukses download
        }
        return false; // Gagal download
    }
    
    // --- FUNGSI CRUD LOKAL ---
    public function get_all_balita() { if (!$this->is_connected()) return []; $q = $this->conn->query("SELECT * FROM balita"); return $q->fetchAll(PDO::FETCH_OBJ); }
    public function get_all_petugas() { if (!$this->is_connected()) return []; $q = $this->conn->query("SELECT * FROM petugas"); return $q->fetchAll(PDO::FETCH_OBJ); }
    public function tambah_pemeriksaan_lokal($data) { 
        if (!$this->is_connected()) return;
        $sql = "INSERT INTO pemeriksaan (tgl_periksa, berat_badan, tinggi_badan, catatan_gizi, id_balita, id_petugas) VALUES (?,?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$data['tgl_periksa'], $data['berat_badan'], $data['tinggi_badan'], $data['catatan_gizi'], $data['id_balita'], $data['id_petugas']]);
    }
    public function tampil_pemeriksaan_lokal() { 
        if (!$this->is_connected()) return [];
        $sql = "SELECT p.*, b.nama_balita, pt.nama_petugas FROM pemeriksaan p LEFT JOIN balita b ON p.id_balita = b.id_balita LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas ORDER BY p.id_periksa DESC";
        $q = $this->conn->query($sql); return $q->fetchAll(PDO::FETCH_OBJ);
    }

    // FUNGSI BARU: Mendapatkan data pemeriksaan berdasarkan ID
    public function get_pemeriksaan_by_id($id_periksa) {
        if (!$this->is_connected()) return null;
        $sql = "SELECT * FROM pemeriksaan WHERE id_periksa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_periksa]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // FUNGSI BARU: Update pemeriksaan lokal
    public function update_pemeriksaan_lokal($data) {
        if (!$this->is_connected()) return false;
        $sql = "UPDATE pemeriksaan SET tgl_periksa = ?, berat_badan = ?, tinggi_badan = ?, catatan_gizi = ?, id_balita = ?, id_petugas = ? WHERE id_periksa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tgl_periksa'], 
            $data['berat_badan'], 
            $data['tinggi_badan'], 
            $data['catatan_gizi'], 
            $data['id_balita'], 
            $data['id_petugas'],
            $data['id_periksa']
        ]);
    }

    // FUNGSI BARU: Delete pemeriksaan lokal
    public function delete_pemeriksaan_lokal($id_periksa) {
        if (!$this->is_connected()) return false;
        $sql = "DELETE FROM pemeriksaan WHERE id_periksa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_periksa]);
    }

    // --- FUNGSI UPLOAD DATA ---
    public function sync_upload_laporan() {
        if (!$this->is_connected()) return false;
        $data_lokal = $this->tampil_pemeriksaan_lokal();
        foreach ($data_lokal as $row) {
            $data_kirim = array("aksi" => "tambah_pemeriksaan", "tgl_periksa" => $row->tgl_periksa, "berat_badan" => $row->berat_badan, "tinggi_badan" => $row->tinggi_badan, "catatan_gizi" => $row->catatan_gizi, "id_balita" => $row->id_balita, "id_petugas" => $row->id_petugas);
            $c = curl_init(); curl_setopt($c, CURLOPT_URL, $this->url_server); curl_setopt($c, CURLOPT_RETURNTRANSFER, true); curl_setopt($c, CURLOPT_POST, true); curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data_kirim)); $response = curl_exec($c); curl_close($c);
            $res = json_decode($response);
            if (isset($res->status) && $res->status == 'sukses') {
                $del = $this->conn->prepare("DELETE FROM pemeriksaan WHERE id_periksa = ?");
                $del->execute([$row->id_periksa]);
            }
        }
        return true;
    }

    // --- FUNGSI CEK DATA PUSAT ---
    public function tampil_data_server()
    {
        $client = curl_init($this->url_server);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($client);
        curl_close($client);
        $data = json_decode($response);
        return $data;
    }
}

// SETUP URL SERVER (Menggunakan IP/Path yang Anda berikan)
$url_server = 'http://10.90.33.173/sister-uas/server/server.php';
$abc = new Client($url_server);
?>