<?php
// File Baru: MasterInfusController.php

require_once 'Database.php'; 

class MasterInfusController {
    private $conn;
    private $table_master = "rsk_infus_master"; 

    public function __construct($db) {
        $this->conn = $db;
    }

    // R. READ - Mendapatkan semua daftar master infus (untuk Datatables)
    public function getInfusMasterListDetailed() {
        $query = "SELECT kd_obat_infus, nama_obat_infus, volume_standar, faktor_tetes FROM " . $this->table_master . " ORDER BY nama_obat_infus ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting detailed infus master list: " . $e->getMessage());
            return [];
        }
    }
    
    // R. READ - Mendapatkan satu item berdasarkan ID (untuk Edit)
    public function getInfusMasterById($kd_obat_infus) {
        $query = "SELECT kd_obat_infus, nama_obat_infus, volume_standar, faktor_tetes FROM " . $this->table_master . " WHERE kd_obat_infus = :kd_obat_infus LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kd_obat_infus', $kd_obat_infus);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting infus master by ID: " . $e->getMessage());
            return false;
        }
    }

    // C. CREATE - Menambahkan master infus baru
    public function addInfusMaster($data) {
        if (empty($data['kd_obat_infus']) || empty($data['nama_obat_infus'])) {
            return false;
        }
        $query = "INSERT INTO " . $this->table_master . " (kd_obat_infus, nama_obat_infus, volume_standar, faktor_tetes) VALUES (:kd_obat_infus, :nama_obat_infus, :volume_standar, :faktor_tetes)";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":kd_obat_infus", $data['kd_obat_infus']);
            $stmt->bindParam(":nama_obat_infus", $data['nama_obat_infus']);
            $stmt->bindParam(":volume_standar", $data['volume_standar']);
            $stmt->bindParam(":faktor_tetes", $data['faktor_tetes']);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Error duplikasi PK
                 error_log("Duplicate entry for KD OBAT INFUS: " . $data['kd_obat_infus']);
                 return 'DUPLICATE_ENTRY';
            }
            error_log("Error adding infus master: " . $e->getMessage());
            return false;
        }
    }

    // U. UPDATE - Mengupdate master infus
    public function updateInfusMaster($data) {
        if (empty($data['kd_obat_infus']) || empty($data['nama_obat_infus'])) {
            return false;
        }
        $query = "UPDATE " . $this->table_master . " SET nama_obat_infus = :nama_obat_infus, volume_standar = :volume_standar, faktor_tetes = :faktor_tetes WHERE kd_obat_infus = :kd_obat_infus";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":kd_obat_infus", $data['kd_obat_infus']);
            $stmt->bindParam(":nama_obat_infus", $data['nama_obat_infus']);
            $stmt->bindParam(":volume_standar", $data['volume_standar']);
            $stmt->bindParam(":faktor_tetes", $data['faktor_tetes']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating infus master: " . $e->getMessage());
            return false;
        }
    }

    // D. DELETE - Menghapus master infus
    public function deleteInfusMaster($kd_obat_infus) {
        $query = "DELETE FROM " . $this->table_master . " WHERE kd_obat_infus = :kd_obat_infus";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kd_obat_infus', $kd_obat_infus);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting infus master: " . $e->getMessage());
            return false;
        }
    }
}