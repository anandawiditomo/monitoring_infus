<?php
// File: pages/mobile_dashboard.php (VERSI TABEL SEDERHANA AKHIR)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor View | Infus Ringkas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body { background-color: #f0f3f6; }
        .table-infus-simple td, .table-infus-simple th {
            padding: 0.5rem 0.25rem; 
            font-size: 0.85rem; 
            vertical-align: middle;
        }
        .alert-kritis { animation: pulse-red 0.8s infinite alternate; background-color: #f8d7da !important; }
        .alert-warning { background-color: #fff3cd !important; }
        @keyframes pulse-red {
            from { background-color: #dc3545; color: white; }
            to { background-color: #f8d7da; color: black; }
        }
        .icon-level i { font-size: 2.5em; } /* Perbesar icon */
    </style>
</head>
<body>
    <div class="container-fluid mt-2">
        <h5 class="mb-2 fw-bold text-primary">Infus Monitoring (Mobile Ringkas)</h5>

        <div class="row mb-3">
            <div class="col-12">
                <label for="filterUnit" class="form-label small">Filter Unit/Bangsal</label>
                <select class="form-select form-select-sm" id="filterUnit">
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

        <div id="infusTableContainer" class="card shadow">
            <div class="card-body p-0">
                <table id="infusTable" class="table table-striped table-hover table-infus-simple mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 30%">Pasien/Kamar</th>
                            <th style="width: 15%" class="text-center">Level</th>
                            <th style="width: 55%">Detail Auto (Sisa Waktu/Status)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="text-center text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../assets/js/main_android_simple.js"></script> 
</body>
</html>