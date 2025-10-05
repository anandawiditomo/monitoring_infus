<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INFUS-SIMRS | Menu Utama Monitoring</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
        }
        .menu-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 1rem;
            min-height: 200px; /* Tinggi minimum untuk konsistensi */
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
    </style>
</head>
<body>
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Masuk ke Dashboard</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="passwordInput" class="form-label">Masukkan Kata Sandi (admin)</label>
                        <input type="password" class="form-control" id="passwordInput">
                        <div id="passwordError" class="form-text text-danger d-none">Kata sandi salah.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="loginBtn">Masuk</button>
                </div>
            </div>
        </div>
    </div>    

    <div class="container py-5 d-none" id="dashboardContent">
        <header class="text-center mb-5">
            <img src="logo_infus.jpg" alt="Logo Infus" class="mb-3" style="max-height: 100px;"> 
            <h1 class="fw-bold text-primary">Sistem Monitoring Infus</h1>
            <p class="lead text-secondary"><sup>Akses cepat ke modul manajemen cairan infus.</sup></p>
        </header>

        <div class="row justify-content-center g-4">
            
            <div class="col-md-5 col-lg-4">
                <a href="pages/dashboard.php" class="text-decoration-none">
                    <div class="card menu-card shadow-sm bg-white p-4 text-center">
                        <div class="icon-circle bg-gradient-primary mx-auto">
                            <i class="fa-solid fa-desktop fa-3x"></i> 
                        </div>
                        <h4 class="card-title fw-bold text-dark">Monitoring Infus (Desktop)</h4>
                        <p class="text-muted mb-0">Dashboard pemantauan pasien rawat inap secara real-time.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-5 col-lg-4">
                <a href="pages/mobile_dashboard.php" class="text-decoration-none"> 
                    <div class="card menu-card shadow-sm bg-white p-4 text-center">
                        <div class="icon-circle bg-gradient-primary mx-auto">
                            <i class="fa-solid fa-mobile-screen-button fa-3x"></i>
                        </div>
                        <h4 class="card-title fw-bold text-dark">Android Monitoring Infus</h4>
                        <p class="text-muted mb-0">Mobile Dashboard pemantauan pasien rawat inap secara real-time.</p>
                    </div>
                </a>
            </div>
    
            <div class="col-md-5 col-lg-4">
                <a href="pages/master_infus.php" class="text-decoration-none">
                    <div class="card menu-card shadow-sm bg-white p-4 text-center">
                        <div class="icon-circle bg-gradient-success mx-auto">
                            <i class="fa-solid fa-vial-virus fa-3x"></i>
                        </div>
                        <h4 class="card-title fw-bold text-dark">Edit Master Cairan</h4>
                        <p class="text-muted mb-0">Manajemen data kode, volume, dan faktor tetes cairan infus.</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-5 col-lg-4">
                <a href="infus.zip" class="text-decoration-none">
                    <div class="card menu-card shadow-sm bg-white p-4 text-center">
                        <div class="icon-circle bg-gradient-success mx-auto">
                            <i class="fa-solid fa-vial-virus fa-3x"></i>
                        </div>
                        <h4 class="card-title fw-bold text-dark">Download Aplikasi ini</h4>
                        <p class="text-muted mb-0">Implementasi Aplikasi dengan data Khaza / Medic lite.</p>
                    </div>
                </a>
            </div>            
            
            
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dashboardContent = document.getElementById('dashboardContent'); // ID dikoreksi
            const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
            const passwordInput = document.getElementById('passwordInput');
            const loginBtn = document.getElementById('loginBtn');
            const passwordError = document.getElementById('passwordError');
            
            // Password yang valid
            const passwords = ['admin', 'server'];

            function showDashboard() {
                dashboardContent.classList.remove('d-none');
            }

            // Check if user is already logged in
            if (sessionStorage.getItem('isLoggedIn') === 'true') {
                showDashboard();
            } else {
                passwordModal.show();
            }
            
            loginBtn.addEventListener('click', () => {
                const enteredPassword = passwordInput.value;
                if (passwords.includes(enteredPassword)) {
                    sessionStorage.setItem('isLoggedIn', 'true');
                    passwordModal.hide();
                    showDashboard();
                } else {
                    passwordError.classList.remove('d-none');
                    passwordInput.value = ''; // Kosongkan input setelah salah
                    passwordInput.focus();
                }
            });

            passwordInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    loginBtn.click();
                }
            });

            // Pastikan modal disembunyikan sebelum showDashboard dipanggil
            document.getElementById('passwordModal').addEventListener('hidden.bs.modal', () => {
                if (sessionStorage.getItem('isLoggedIn') === 'true') {
                    showDashboard();
                }
            });
        });
    </script>    
</body>
</html>