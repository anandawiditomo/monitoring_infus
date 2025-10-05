// File: assets/js/main_android_simple.js (Logika Stabil & Audio Fix)

$(document).ready(function() {
    const BASE_API_URL_ROOT = 'https://web.budirahayu.com/api/infusDemo/v1/'; 
    const API_LIST_URL = BASE_API_URL_ROOT + 'monitoring.php/list'; 
    
    const API_REFRESH_INTERVAL = 30000; 
    const REALTIME_UPDATE_INTERVAL = 5000;
    const audioAlert = new Audio('../assets/sounds/alert_sound.mp3'); 
    audioAlert.loop = true; 

    let realTimeUpdater;
    let globalDataRefresher; 
    let currentRawatData = {}; // State data global

    // FUNGSI: MENDAPATKAN ICON BATERAI BERDASARKAN PERSENTASE SISA
    function getBatteryIcon(volume_sisa, volume_total) {
        if (volume_total === 'N/A' || volume_sisa === 'N/A' || volume_total <= 0) {
            return 'N/A';
        }
        const total = parseFloat(volume_total);
        const sisa = parseFloat(volume_sisa);
        const percentage = (sisa / total) * 100;

        if (percentage > 75) {
            return '<i class="fa-solid fa-battery-full text-success"></i>';
        } else if (percentage > 50) {
            return '<i class="fa-solid fa-battery-three-quarters text-success"></i>';
        } else if (percentage > 25) {
            return '<i class="fa-solid fa-battery-half text-warning"></i>';
        } else if (percentage > 7) {
            return '<i class="fa-solid fa-battery-quarter text-warning"></i>';
        } else {
            return '<i class="fa-solid fa-battery-empty text-danger"></i>';
        }
    }

    // FUNGSI UTAMA: MEREKAYASA TABEL DARI DATA API
    function renderInfusTable(data) {
        const tbody = $('#infusTable tbody');
        tbody.empty();
        currentRawatData = {}; 

        const filteredData = data.filter(row => 
            row.predicted_alert !== 'TANPA_ORDER_INFUS'
        );

        if (filteredData.length === 0) {
             tbody.append('<tr><td colspan="3" class="text-center text-muted p-3">Tidak ada pasien yang memiliki Order Infus Aktif.</td></tr>');
             return;
        }

        $.each(filteredData, function(index, row) { 
            currentRawatData[row.no_rawat] = row; 

            const initialStatusText = (row.predicted_alert.replace(/_/g, ' ') === 'ORDER BARU') ? 'MENUNGGU PANTAUAN' : row.predicted_alert.replace(/_/g, ' ');
            const initialVol = (row.volume_sisa === 'N/A') ? row.volume_total + ' ml (Baru)' : `${row.volume_sisa} ml`;
            const initialWaktu = (initialStatusText === 'MENUNGGU PANTAUAN') ? 'BELUM TERHITUNG' : 'N/A';
            
            let rowClass = (row.predicted_alert.includes('KRITIS') || row.predicted_alert.includes('HABIS')) ? 'alert-kritis' : (row.predicted_alert.includes('PERINGATAN') || row.predicted_alert.includes('LAJU')) ? 'alert-warning' : '';

            const rowHtml = `
                <tr id="row-${row.no_rawat.replace(/[\/\.]/g, '')}" class="${rowClass}">
                    <td>
                        <strong class="nm-pasien">${row.nm_pasien}</strong><br>
                        <small class="text-muted">${row.nm_bangsal} (${row.kd_kamar})</small><br>
                        <small class="text-primary">${row.nama_obat_infus !== 'N/A' ? row.nama_obat_infus : 'Belum Order'}</small>
                    </td>
                    <td class="text-center icon-level">
                        <div class="battery-icon">
                            ${getBatteryIcon(row.volume_sisa, row.volume_total)}
                        </div>
                    </td>
                    <td>
                        Waktu Sisa: <strong class="waktu-sisa">${initialWaktu}</strong><br>
                        Volume Sisa: <span class="volume-sisa">${initialVol}</span><br>
                        Status: <strong class="status-alert-text">${initialStatusText}</strong>
                    </td>
                </tr>
            `;
            tbody.append(rowHtml);
        });
    }

    // FUNGSI UNTUK MUAT DATA DARI API (DIPANGGIL SECARA BERKALA)
    function loadInfusData() {
        const selectedUnit = $('#filterUnit').val() || 'ALL'; 
        
        if (realTimeUpdater) {
            clearInterval(realTimeUpdater);
        }
        
        $.ajax({
            url: API_LIST_URL + '?unit=' + selectedUnit + '&_t=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    
                    // Mengambil Waktu Server (Milidetik)
                    let serverNowTime = new Date(response.server_now.replace(/-/g, "/")).getTime(); 
                    
                    // 1. Render Tabel dengan data LOG terbaru dari API
                    renderInfusTable(response.data);
                    
                    // 2. Mulai update 5 detik dari waktu server yang baru
                    startRealTimeUpdates(serverNowTime); 
                    
                    realTimeUpdater = setInterval(function() {
                        serverNowTime += REALTIME_UPDATE_INTERVAL; 
                        startRealTimeUpdates(serverNowTime);
                    }, REALTIME_UPDATE_INTERVAL); 
                    
                } else {
                    $('#infusTable tbody').html('<tr><td colspan="3" class="text-center text-muted p-3">Gagal memuat data dari server.</td></tr>');
                    checkGlobalAlerts([]); 
                }
            },
            error: function(xhr, status, error) { 
                console.error("AJAX Error:", status, error); 
                $('#infusTable tbody').html('<tr><td colspan="3" class="text-center text-danger p-3">AJAX Error: Server tidak merespons.</td></tr>');
                checkGlobalAlerts([]); 
            }
        });
    }
    
    // FUNGSI UNTUK UPDATE NILAI REAL-TIME PADA TABEL (Perhitungan Inti NON-ITERATIF)
    function startRealTimeUpdates(time_base_ms) { 
        
        $.each(currentRawatData, function(no_rawat, data) {
            const rowNode = $(`#row-${no_rawat.replace(/[\/\.]/g, '')}`);
            
            const baseVolumeSisa = parseFloat(data.volume_sisa);
            const baseTetesanAktual = parseFloat(data.tetesan_aktual);

            if (data.tetesan_aktual === 'N/A' || !data.tgl_catat || !data.jam_catat || !data.faktor_tetes) return;

            // PERHITUNGAN DARI TITIK LOG AWAL KE WAKTU SEKARANG (NON-ITERATIF)
            const lastLogTime = new Date(data.tgl_catat + 'T' + data.jam_catat).getTime(); 
            const now = time_base_ms; 
            const elapsedSeconds = (now - lastLogTime) / 1000;

            if (elapsedSeconds < 0) return; 
            const elapsedMinutes = elapsedSeconds / 60;
            const volumeUsed = (baseTetesanAktual * elapsedMinutes) / data.faktor_tetes;
            
            let estimatedVolumeSisa = baseVolumeSisa - volumeUsed; 
            
            if (estimatedVolumeSisa < 0) {
                estimatedVolumeSisa = 0;
            }
            
            let estimatedWaktuSisaMenit;
            let tetesanAktual = baseTetesanAktual;

            if (estimatedVolumeSisa === 0) {
                estimatedWaktuSisaMenit = 0;
            } else {
                if (tetesanAktual > 0) {
                    estimatedWaktuSisaMenit = Math.round((estimatedVolumeSisa * data.faktor_tetes) / tetesanAktual);
                } else {
                    estimatedWaktuSisaMenit = 'N/A';
                }
            }
            
            let formattedTime = 'N/A';
            if (estimatedWaktuSisaMenit !== 'N/A' && estimatedWaktuSisaMenit > 0) {
                const hours = Math.floor(estimatedWaktuSisaMenit / 60);
                const minutes = estimatedWaktuSisaMenit % 60;
                formattedTime = (hours > 0 ? hours + ' Jam ' : '') + minutes + ' Menit';
            } else {
                formattedTime = 'HABIS / BERHENTI';
            }

            // --- LOGIKA ALERT BERDASARKAN AUTO-DECAY (FRONTEND) ---
            let currentAlertStatus = 'NORMAL';
            if (estimatedVolumeSisa <= 0 || estimatedWaktuSisaMenit === 0) {
                currentAlertStatus = 'INFUS HABIS';
            } else if (estimatedWaktuSisaMenit !== 'N/A' && estimatedWaktuSisaMenit <= 15) {
                currentAlertStatus = 'KRITIS HABIS';
            } else if (estimatedWaktuSisaMenit !== 'N/A' && estimatedWaktuSisaMenit <= 60) {
                currentAlertStatus = 'PERINGATAN HABIS';
            } else if (data.target_tetesan !== 'N/A' && data.target_tetesan > 0 && Math.abs(tetesanAktual - data.target_tetesan) / data.target_tetesan > 0.20) {
                 currentAlertStatus = 'LAJU TIDAK SESUAI';
            }

            // TIDAK ADA LAGI data.volume_sisa = estimatedVolumeSisa; di sini. Kita mempertahankan data log asli.
            data.predicted_alert = currentAlertStatus.replace(/ /g, '_'); 

            // Perbarui visualisasi Baris (warna)
            rowNode.removeClass('alert-kritis alert-warning');
            if (currentAlertStatus.includes('KRITIS') || currentAlertStatus.includes('HABIS')) {
                rowNode.addClass('alert-kritis'); 
            } else if (currentAlertStatus.includes('PERINGATAN') || currentAlertStatus.includes('LAJU')) {
                rowNode.addClass('alert-warning'); 
            }

            // Perbarui nilai di Sel Tabel
            rowNode.find('.waktu-sisa').text(formattedTime);
            rowNode.find('.volume-sisa').text(`${estimatedVolumeSisa.toFixed(1)} ml`);
            rowNode.find('.battery-icon').html(getBatteryIcon(estimatedVolumeSisa.toFixed(1), data.volume_total));
            rowNode.find('.status-alert-text').text(currentAlertStatus);
        });
        
        checkGlobalAlerts(Object.values(currentRawatData));
    }
    
    // FUNGSI: MEMUTAR AUDIO ALERT
    function checkGlobalAlerts(dataArray) {
        let isCritical = dataArray.some(item => item.predicted_alert === 'KRITIS_HABIS' || item.predicted_alert === 'INFUS_HABIS');
        
        if (isCritical) {
            // Coba putar audio
            audioAlert.play().catch(e => console.warn("Audio Alert blocked.", e));
        } else {
            audioAlert.pause();
            audioAlert.currentTime = 0;
        }
    }
    
    // FUNGSI UNTUK MENGAKTIFKAN AUDIO DENGAN INTERAKSI PERTAMA (Hack Browser)
    function enableAudioOnInteraction() {
        // Hapus event listener ini segera setelah dipanggil
        $(document).off('touchstart click', enableAudioOnInteraction); 

        audioAlert.volume = 0; 
        audioAlert.play().then(() => {
            // Sukses: Audio Context sudah terbuka.
            audioAlert.pause();
            audioAlert.currentTime = 0;
            audioAlert.volume = 1.0; // Kembalikan volume normal
            console.log("Audio Context Activated.");

        }).catch(error => {
            console.warn("Audio activation failed. User interaction still required.");
            // Jika gagal, pasang lagi event listener untuk interaksi berikutnya
            $(document).one('touchstart click', enableAudioOnInteraction);
        });
    }
    
    // EVENT HANDLER UNTUK FILTER UNIT
    $('#filterUnit').on('change', function() {
        if (globalDataRefresher) clearInterval(globalDataRefresher);
        if (realTimeUpdater) clearInterval(realTimeUpdater);
        loadInfusData(); 
    });

    // --- INISIALISASI ---
    loadInfusData(); 
    globalDataRefresher = setInterval(loadInfusData, API_REFRESH_INTERVAL);
    
    // KOREKSI KRUSIAL: TAMBAHKAN LISTENER INTERAKSI PADA LOAD AWAL
    // Coba aktifkan audio segera setelah user menyentuh/mengklik di manapun di halaman.
    $(document).one('touchstart click', enableAudioOnInteraction);
});