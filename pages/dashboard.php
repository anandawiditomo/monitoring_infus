<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMRS | Dashboard Monitoring Infus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    
    <style>
        .alert-kritis { animation: pulse-red 0.8s infinite alternate; }
        .alert-warning { background-color: #fff3cd !important; }
        @keyframes pulse-red {
            from { background-color: #ff6b6b; color: white; }
            to { background-color: #ffffff; color: black; }
        }
        /* Style tambahan agar Select2 di modal terlihat benar */
        .select2-container--bootstrap-5 .select2-selection {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h2 class="mb-4">Dashboard Monitoring Infus Rawat Inap</h2>
        <p class="text-muted">Data diperbarui setiap 10 detik.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filterUnit" class="form-label">Filter Unit/Bangsal</label>
                <select class="form-select" id="filterUnit" style="width: 100%;">
                    <option value="ALL" selected>ALL (Supervisor)</option>
                    <option value="PAV 1">PAV 1</option>
                    <option value="PAV 2">PAV 2</option>
                    <option value="PAV 3">PAV 3</option>
                    <option value="PAV 4">PAV 4</option>
                    <option value="PAV 5">PAV 5</option>
                    <option value="ICU">ICU</option>
                    <option value="HD">HD</option>
                    <option value="IGD">IGD</option>
                </select>
            </div>
        </div>
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                Daftar Pasien Aktif Infus
            </div>
            <div class="card-body">
                <table id="infusTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No. Rawat</th>
                            <th>Nama Pasien</th>
                            <th>Bangsal / Kamar</th>
                            <th>Infus Order</th>
                            <th>Target/Aktual (tpm)</th>
                            <th>Level</th> 
                            <th>Volume Sisa (ml)</th>
                            <th>Waktu Sisa Prediksi</th>
                            <th>Status Alert</th>
                            <th>Waktu Log Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">Catat Log Infus Pasien: <span id="pasienNamaModal"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-info" role="alert">
                      <h6 class="alert-heading">Informasi Order Aktif</h6>
                      <p class="mb-0">
                        <strong>Infus:</strong> <span id="logModalNamaObat">-</span>
                      </p>
                      <hr>
                      <p class="mb-0">
                        <strong>Target Tetesan:</strong> <span id="logModalTargetTetesan" class="fw-bold fs-5">-</span> tpm
                      </p>
                    </div>

                    <form id="infusLogForm">
                        <input type="hidden" id="modalNoRawat" name="no_rawat">
                        <div class="mb-3">
                            <label for="tetesanAktual" class="form-label">Tetesan Aktual (tpm)</label>
                            <input type="number" class="form-control" id="tetesanAktual" name="tetesan_aktual" required min="0" max="1000">
                        </div>
                        <div class="mb-3">
                            <label for="volumeSisa" class="form-label">Volume Sisa (ml)</label>
                            <input type="number" class="form-control" id="volumeSisa" name="volume_sisa" required min="0" max="1000">
                        </div>
                        <input type="hidden" id="modalKdPetugas" name="kd_petugas" value="P001"> 
                        <div class="mb-3">
                            <label for="keteranganTemuan" class="form-label">Keterangan Tambahan</label>
                            <textarea class="form-control" id="keteranganTemuan" name="keterangan_temuan"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Log</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Input Order Infus: <span id="pasienNamaOrder"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="infusOrderForm">
                        <input type="hidden" id="modalNoRawatOrder" name="no_rawat">
                        <input type="hidden" id="modalKdPetugasOrder" name="kd_petugas" value="P001">
                        
                        <div class="mb-3">
                            <label for="orderKdObat" class="form-label">Kode/Nama Cairan Infus</label>
                            <select class="form-control" id="orderKdObat" name="kd_obat_infus" required style="width: 100%"></select>
                        </div>

                        <div class="mb-3">
                            <label for="orderVolume" class="form-label">Volume Total (ml)</label>
                            <input type="number" class="form-control" id="orderVolume" name="volume_total" min="50" max="1000" value="500" required>
                        </div>
                        <div class="mb-3">
                            <label for="orderTetesan" class="form-label">Target Tetesan (tpm)</label>
                            <input type="number" class="form-control" id="orderTetesan" name="target_tetesan" required min="1" max="100">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Simpan Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>