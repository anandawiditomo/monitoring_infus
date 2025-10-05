<?php
// File: InfusController.php (VERSI FINAL DENGAN WAKTU SERVER)
// PASTIKAN TIDAK ADA SPASI ATAU BARIS KOSONG SEBELUM TAG INI
require_once 'Database.php'; 

class InfusController {
    private $conn;
    private $table_order = "rsk_infus_order";
    private $table_monitoring = "rsk_infus_monitoring";
    private $table_master = "rsk_infus_master";
    private $table_reg = "reg_periksa";
    private $table_pasien = "pasien";
    private $table_kamar_inap = "kamar_inap"; 
    private $table_kamar = "kamar"; 
    private $table_bangsal = "bangsal"; 

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getInfusDashboardData($filter_unit = 'ALL') { 
        $where_unit = "";
        if ($filter_unit != 'ALL') {
             // Menggunakan LIKE dengan wildcard (%) untuk mencakup semua nama bangsal yang mengandung kata kunci
            $where_unit = " AND t_bangsal.nm_bangsal LIKE :filter_unit ";
            
            // Logika kustom untuk PAV (PAV 1 - ..., PAV 2 - ...)
            if (strpos($filter_unit, 'PAV') !== false) {
                 $where_unit = " AND t_bangsal.nm_bangsal LIKE :filter_unit ";
            }
        }
        
        $query = "
            SELECT 
                t1.no_rawat, t2.nm_pasien, t3.kd_obat_infus,
                t3.volume_total, t3.target_tetesan, 
                t5.nama_obat_infus, t5.faktor_tetes,
                t_ki.kd_kamar,
                t_kamar.kd_bangsal,               
                t_bangsal.nm_bangsal,           
                log_terakhir.tgl_catat, log_terakhir.jam_catat,
                log_terakhir.tetesan_aktual, log_terakhir.volume_sisa,
                log_terakhir.stts_simrs_alert
            FROM reg_periksa t1
            INNER JOIN pasien t2 ON t1.no_rkm_medis = t2.no_rkm_medis
            INNER JOIN kamar_inap t_ki ON t1.no_rawat = t_ki.no_rawat AND t_ki.stts_pulang != 'Pindah Kamar'
            INNER JOIN kamar t_kamar ON t_ki.kd_kamar = t_kamar.kd_kamar
            INNER JOIN bangsal t_bangsal ON t_kamar.kd_bangsal = t_bangsal.kd_bangsal
            LEFT JOIN rsk_infus_order t3 ON t1.no_rawat = t3.no_rawat AND t3.status_order = 'Aktif'
            LEFT JOIN rsk_infus_master t5 ON t3.kd_obat_infus = t5.kd_obat_infus
            LEFT JOIN (
                SELECT a.* FROM rsk_infus_monitoring a
                INNER JOIN (
                    SELECT no_rawat, MAX(CONCAT(tgl_catat, ' ', jam_catat)) as max_datetime
                    FROM rsk_infus_monitoring GROUP BY no_rawat
                ) b ON a.no_rawat = b.no_rawat AND CONCAT(a.tgl_catat, ' ', a.jam_catat) = b.max_datetime
            ) AS log_terakhir ON t1.no_rawat = log_terakhir.no_rawat
            WHERE (t_ki.tgl_keluar = '0000-00-00' OR t_ki.tgl_keluar IS NULL)
            " . $where_unit . "
            ORDER BY t_bangsal.nm_bangsal, t_ki.kd_kamar ASC
        ";
        try {
            $stmt = $this->conn->prepare($query);
            
            // Binding parameter jika filter digunakan
            if ($filter_unit != 'ALL') {
                $bind_value = $filter_unit . '%'; 
                $stmt->bindParam(":filter_unit", $bind_value);
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("SQL Error in getInfusDashboardData: " . $e->getMessage());
            return [];
        }

        foreach ($result as &$row) {
            if ($row['target_tetesan'] === null) {
                $row['waktu_sisa_menit'] = 'N/A'; $row['predicted_alert'] = 'TANPA_ORDER_INFUS';
                $row['volume_total'] = 'N/A'; $row['target_tetesan'] = 'N/A';
                $row['nama_obat_infus'] = 'N/A'; $row['tetesan_aktual'] = 'N/A';
                $row['volume_sisa'] = 'N/A';
                continue; 
            }
            $row['waktu_sisa_menit'] = $row['waktu_sisa_menit'] ?? 'N/A';
            $row['predicted_alert'] = $row['predicted_alert'] ?? 'ORDER_BARU'; 
            $row['tetesan_aktual'] = $row['tetesan_aktual'] ?? 'N/A';
            $row['volume_sisa'] = $row['volume_sisa'] ?? 'N/A';

            if ($row['volume_sisa'] > 0 && $row['tetesan_aktual'] > 0 && isset($row['faktor_tetes']) && $row['faktor_tetes'] > 0) {
                $tetesan_sisa = $row['volume_sisa'] * $row['faktor_tetes'];
                $row['waktu_sisa_menit'] = round($tetesan_sisa / $row['tetesan_aktual']);
            }
        }
        
        // --- TAMBAHAN BARU: MENGAMBIL WAKTU SERVER ---
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $server_now = $dateTime->format('Y-m-d H:i:s');
        
        // Bungkus hasil dan waktu server
        $output = [
            'data' => $result,
            'server_now' => $server_now
        ];
        
        return $output; 
    }

    public function addInfusOrder($data) {
        if (empty($data['no_rawat']) || empty($data['kd_obat_infus']) || empty($data['volume_total']) || empty($data['target_tetesan']) || empty($data['kd_petugas'])) {
            return false;
        }
        try {
            $query_delete = "DELETE FROM " . $this->table_order . " WHERE no_rawat = :no_rawat";
            $stmt_delete = $this->conn->prepare($query_delete);
            $stmt_delete->bindParam(":no_rawat", $data['no_rawat']);
            $stmt_delete->execute();
            
            $dateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            $tgl_mulai = $dateTime->format('Y-m-d');
            $jam_mulai = $dateTime->format('H:i:s');
            $faktor_tetes = $data['faktor_tetes'] ?? 20; 

            $query_insert = "INSERT INTO " . $this->table_order . " (no_rawat, kd_obat_infus, volume_total, target_tetesan, faktor_tetes, tgl_mulai, jam_mulai, kd_petugas, status_order) VALUES (:no_rawat, :kd_obat_infus, :volume_total, :target_tetesan, :faktor_tetes, :tgl_mulai, :jam_mulai, :kd_petugas, 'Aktif')"; 
            $stmt_insert = $this->conn->prepare($query_insert);
            $stmt_insert->bindParam(":no_rawat", $data['no_rawat']);
            $stmt_insert->bindParam(":kd_obat_infus", $data['kd_obat_infus']);
            $stmt_insert->bindParam(":volume_total", $data['volume_total']);
            $stmt_insert->bindParam(":target_tetesan", $data['target_tetesan']);
            $stmt_insert->bindParam(":faktor_tetes", $faktor_tetes);
            $stmt_insert->bindParam(":tgl_mulai", $tgl_mulai);
            $stmt_insert->bindParam(":jam_mulai", $jam_mulai);
            $stmt_insert->bindParam(":kd_petugas", $data['kd_petugas']); 
            $stmt_insert->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error saving order: " . $e->getMessage());
            return false;
        }
    }
    
    public function addInfusLog($data) {
        if (empty($data['no_rawat']) || !isset($data['tetesan_aktual']) || !isset($data['volume_sisa']) || empty($data['kd_petugas'])) {
            return false;
        }
        try {
            $dateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
            $tgl_catat = $dateTime->format('Y-m-d');
            $jam_catat = $dateTime->format('H:i:s');
            $stts_alert = 'NORMAL'; 
            $query = "INSERT INTO " . $this->table_monitoring . " (no_rawat, tgl_catat, jam_catat, tetesan_aktual, volume_sisa, kd_petugas, keterangan_temuan, stts_simrs_alert) VALUES (:no_rawat, :tgl_catat, :jam_catat, :tetesan_aktual, :volume_sisa, :kd_petugas, :keterangan_temuan, :stts_simrs_alert)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":no_rawat", $data['no_rawat']);
            $stmt->bindParam(":tgl_catat", $tgl_catat);
            $stmt->bindParam(":jam_catat", $jam_catat);
            $stmt->bindParam(":tetesan_aktual", $data['tetesan_aktual']);
            $stmt->bindParam(":volume_sisa", $data['volume_sisa']);
            $stmt->bindParam(":kd_petugas", $data['kd_petugas']);
            $stmt->bindParam(":keterangan_temuan", $data['keterangan_temuan']);
            $stmt->bindParam(":stts_simrs_alert", $stts_alert);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error saving log: " . $e->getMessage());
            return false;
        }
    }

    public function endInfusOrder($data) {
        if (empty($data['no_rawat'])) {
            return false;
        }
        $query = "UPDATE " . $this->table_order . " SET status_order = 'Selesai', tgl_jam_selesai = NOW() WHERE no_rawat = :no_rawat AND status_order = 'Aktif'";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":no_rawat", $data['no_rawat']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error ending order: " . $e->getMessage());
            return false;
        }
    }

    public function getInfusMasterList() {
        $query = "SELECT kd_obat_infus as id, nama_obat_infus as text FROM " . $this->table_master . " ORDER BY nama_obat_infus ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting infus master list: " . $e->getMessage());
            return [];
        }
    }
}