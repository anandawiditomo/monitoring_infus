<?php
// File Baru: master_infus.php
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMRS | Master Data Cairan Infus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>
    <div class="container-fluid mt-4">
        <h2 class="mb-4">Master Data Cairan Infus</h2>

        <button class="btn btn-primary mb-3" id="btnAddMaster">
            <i class="fa-solid fa-plus"></i> Tambah Cairan Baru
        </button>

        <div class="card shadow">
            <div class="card-header bg-success text-white">
                Daftar Cairan Infus Tersedia
            </div>
            <div class="card-body">
                <table id="masterInfusTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Cairan Infus</th>
                            <th>Volume Standar (ml)</th>
                            <th>Faktor Tetes</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="masterModal" tabindex="-1" aria-labelledby="masterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="masterModalLabel">Form Master Cairan Infus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="masterForm">
                        <div class="mb-3">
                            <label for="kd_obat_infus" class="form-label">Kode Obat Infus</label>
                            <input type="text" class="form-control" id="kd_obat_infus" name="kd_obat_infus" required maxlength="10">
                            <small class="text-muted" id="kdHelp">Gunakan kode unik (max 10 karakter).</small>
                        </div>
                        <div class="mb-3">
                            <label for="nama_obat_infus" class="form-label">Nama Cairan Infus</label>
                            <input type="text" class="form-control" id="nama_obat_infus" name="nama_obat_infus" required>
                        </div>
                        <div class="mb-3">
                            <label for="volume_standar" class="form-label">Volume Standar (ml)</label>
                            <input type="number" class="form-control" id="volume_standar" name="volume_standar" value="500" min="50" max="1000">
                        </div>
                        <div class="mb-3">
                            <label for="faktor_tetes" class="form-label">Faktor Tetes (tetes/ml)</label>
                            <input type="number" class="form-control" id="faktor_tetes" name="faktor_tetes" value="20" required min="10" max="60">
                            <small class="text-muted">Contoh: 20 (set standar) atau 60 (mikro/pediatri).</small>
                        </div>
                        <input type="hidden" id="actionType" value="add">
                        <button type="submit" class="btn btn-success w-100" id="btnSubmitMaster">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.js"></script>
    <script src="../assets/js/master_infus.js"></script>
</body>
</html>