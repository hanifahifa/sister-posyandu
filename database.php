<?php
class Database
{
    private $host = "localhost";
    private $dbname = "db_server_posyandu"; // Sesuaikan nama DB
    private $user = "root";
    private $password = ""; // Sesuaikan password (kosongkan jika default XAMPP)
    private $port = "3306";
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->dbname;charset=utf8", $this->user, $this->password);
        } catch (PDOException $e) {
            echo "Koneksi Server Gagal: " . $e->getMessage();
        }
    }

    public function filter($data)
    {
        $data = preg_replace('/[^a-zA-Z0-9 .]/', '', $data);
        return $data;
    }

    // -- FUNGSI UNTUK DATA MASTER (Posyandu, Balita, Petugas) --
    // Digunakan agar Client bisa menarik data master (Sinkronisasi Turun)
    public function tampil_semua_master($tabel)
    {
        // Validasi nama tabel agar aman
        $allowed_tables = ['posyandu_unit', 'petugas', 'balita'];
        if (!in_array($tabel, $allowed_tables)) {
            return [];
        }

        $query = $this->conn->prepare("SELECT * FROM $tabel");
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
        $query->closeCursor();
        unset($data);
    }

    // -- FUNGSI UNTUK TRANSAKSI (Pemeriksaan) --

    public function tampil_semua_pemeriksaan()
    {
        // Menggunakan JOIN agar data yang tampil lengkap (Nama Balita, Nama Petugas)
        $sql = "SELECT p.*, b.nama_balita, pt.nama_petugas 
                FROM pemeriksaan p
                JOIN balita b ON p.id_balita = b.id_balita
                JOIN petugas pt ON p.id_petugas = pt.id_petugas
                ORDER BY p.tgl_periksa DESC";

        $query = $this->conn->prepare($sql);
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
        $query->closeCursor();
        unset($data);
    }

    // Fungsi Utama Sinkronisasi: Menerima data dari Client
    public function tambah_pemeriksaan($data)
    {
        $query = $this->conn->prepare("INSERT INTO pemeriksaan (tgl_periksa, berat_badan, tinggi_badan, catatan_gizi, id_balita, id_petugas) VALUES (?,?,?,?,?,?)");

        $query->execute(array(
            $data['tgl_periksa'],
            $data['berat_badan'],
            $data['tinggi_badan'],
            $data['catatan_gizi'],
            $data['id_balita'],
            $data['id_petugas']
        ));

        $query->closeCursor();
        unset($data);
    }

    public function hapus_pemeriksaan($id)
    {
        $query = $this->conn->prepare("DELETE FROM pemeriksaan WHERE id_periksa=?");
        $query->execute(array($id));
        $query->closeCursor();
        unset($id);
    }
}