<?php
class Database
{
    private $host = "localhost";
    private $dbname = "db_server_posyandu"; // Sesuaikan nama DB Anda
    private $user = "root";
    private $password = ""; // Sesuaikan password Anda
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

    // FUNGSI PERBAIKAN: Cek Login User (Ambiguity Fix)
    public function cek_login($username, $password)
    {
        // FIX: Menggunakan 'p.id_posyandu' untuk menghilangkan ambiguitas
        $query = $this->conn->prepare("SELECT p.id_posyandu, p.nama_posyandu 
                                        FROM usser u  
                                        JOIN posyandu_unit p ON u.id_posyandu = p.id_posyandu 
                                        WHERE u.username = ? AND u.password = ?");
        $query->execute(array($username, $password));
        $data = $query->fetch(PDO::FETCH_ASSOC);

        return $data;
        $query->closeCursor();
    }

    // FUNGSI BARU: Tampil Data Master Berdasarkan Posyandu (Aman, dipakai Client yang sudah login)
    public function tampil_semua_master_by_posyandu($tabel, $id_posyandu)
    {
        $allowed_tables = ['petugas', 'balita'];
        if (!in_array($tabel, $allowed_tables)) {
            return [];
        }

        $query = $this->conn->prepare("SELECT * FROM $tabel WHERE id_posyandu = ?");
        $query->execute([$id_posyandu]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
        return $data;
        $query->closeCursor();
        unset($data);
    }

    // FUNGSI ASAL: Tampil Semua Master (Tidak Aman, dipakai oleh Client lama jika tidak mengirim id_posyandu)
    public function tampil_semua_master($tabel)
    {
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
