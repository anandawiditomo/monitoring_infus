// File: main.js (VERSI FINAL TERKOREKSI)

$(document).ready(function() {
    const BASE_API_URL_ROOT = 'https://web.budirahayu.com/api/infusDemo/v1/'; 
    const API_LIST_URL = BASE_API_URL_ROOT + 'monitoring.php/list';
    const API_LIST_INFUS_URL = BASE_API_URL_ROOT + 'monitoring.php/list_infus';
    const API_ADD_LOG_URL = BASE_API_URL_ROOT + 'monitoring.php/add_log';
    const API_ADD_ORDER_URL = BASE_API_URL_ROOT + 'monitoring.php/add_order'; 
    const API_END_ORDER_URL = BASE_API_URL_ROOT + 'monitoring.php/end_order';
    
    const REFRESH_INTERVAL = 10000; 
    const REALTIME_UPDATE_INTERVAL = 5000;
    const audioAlert = new Audio('../assets/sounds/alert_sound.mp3'); 
    audioAlert.loop = true; 

    let realTimeUpdater;
    let globalDataRefresher;

    // FUNGSI: MENDAPATKAN ICON BATERAI BERDASARKAN PERSENTASE SISA
    function getBatteryIcon(volume_sisa, volume_total) {
        if (volume_total === 'N/A' || volume_sisa === 'N/A' || volume_total <= 0) {
            return 'N/A';
        }
        
        const total = parseFloat(volume_total);
        const sisa = parseFloat(volume_sisa);
        const percentage = (sisa / total) * 100;

        // Ikon Baterai Horizontal (fa-4x)
        if (percentage > 75) {
            return '<i class="fa-solid fa-battery-full fa-4x text-success" title=">75% Penuh"></i>';
        } else if (percentage > 50) {
            return '<i class="fa-solid fa-battery-three-quarters fa-4x text-success" title="50% - 75%"></i>';
        } else if (percentage > 25) {
            return '<i class="fa-solid fa-battery-half fa-4x text-warning" title="25% - 50%"></i>';
        } else if (percentage > 7) { // 15% - 25% (Rendah)
            return '<i class="fa-solid fa-battery-quarter fa-4x text-warning" title="15% - 25% Rendah"></i>';
        } else { // 0% - 7% (Sangat Kritis / Habis)
            return '<i class="fa-solid fa-battery-empty fa-4x text-danger" title="<15% KRITIS"></i>';
        }
    }

    // FUNGSI: MEMFORMAT BLOK INFORMASI LOG TERAKHIR
    function getLogInfo(row) {
        if (!row.jam_catat) return 'Tidak ada log';

        let formattedTimeLog = 'N/A';
        let volumeLog = (row.volume_sisa !== 'N/A' && row.volume_sisa !== null) ? `${row.volume_sisa} ml` : 'N/A';
        
        // Format waktu sisa berdasarkan log
        if (row.waktu_sisa_menit !== 'N/A' && row.waktu_sisa_menit > 0) {
            const hours = Math.floor(row.waktu_sisa_menit / 60);
            const minutes = row.waktu_sisa_menit % 60;
            formattedTimeLog = (hours > 0 ? hours + ' J ' : '') + minutes + ' M';
        } else if (row.waktu_sisa_menit === 0) {
            formattedTimeLog = 'HABIS';
        } else {
            formattedTimeLog = 'N/A';
        }

        return `
            <small>
                Log Waktu: <strong>${row.jam_catat}</strong><br>
                Log Vol: <strong>${volumeLog}</strong><br>
                Log Wkt Sisa: <strong>${formattedTimeLog}</strong>
            </small>
        `;
    }

    // Inisialisasi Select2 untuk form order
    $('#orderKdObat').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#orderModal'),
        placeholder: 'Cari nama atau kode infus...',
        ajax: {
            url: API_LIST_INFUS_URL,
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });
    
    // Konfigurasi DataTable
    let infusTable = $('#infusTable').DataTable({
        "processing": true,
        "serverSide": false,
        "searching": true,
        "info": true,
        "paging": true, 
        "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "Semua"] ],
        "pageLength": 50, 
        "order": [[7, "asc"]], 
        "columns": [
            { "data": "no_rawat" }, 
            { "data": "nm_pasien" }, 
            { "data": null },              // Index 2: Bangsal/Kamar
            { "data": "nama_obat_infus" }, 
            { "data": null },              // Index 4: Target/Aktual
            { "data": null },              // Index 5: Level (Auto)
            { "data": "volume_sisa" },     // Index 6: Volume Sisa (Auto)
            { "data": "waktu_sisa_menit" },// Index 7: Waktu Sisa (Auto)
            { "data": "predicted_alert" }, // Index 8: Status Alert
            { "data": "jam_catat" },       // Index 9: Waktu Log Terakhir (Blok Info Log)
            { "data": "no_rawat" }         // Index 10: Aksi
        ],
        "createdRow": function (row, data, dataIndex) {
            $(row).attr('id', 'row-' + data.no_rawat.replace(/[\/\.]/g, ''));
        },
        "columnDefs": [
            { 
                "targets": 2, // Kolom ke-3 (Bangsal / Kamar)
                "render": (data, type, row) => `<strong>${row.nm_bangsal}</strong> (${row.kd_kamar})` 
            },
            { "targets": 4, "render": (data, type, row) => `${row.target_tetesan} / ${row.tetesan_aktual} tpm` },
            { 
                "targets": 5, // Level/Baterai (Auto)
                "render": (data, type, row) => getBatteryIcon(row.volume_sisa, row.volume_total)
            },
            { 
                "targets": 6, // Volume Sisa (Auto) - Nilai awal dari log
                "render": (data, type, row) => `<span class="volume-sisa">${data}</span>`
            },
            { 
                "targets": 7, // Waktu Sisa Prediksi (Auto) - Nilai awal dari log
                "render": function (data, type, row) {
                    let formattedTime = 'N/A';
                    if (data !== 'N/A' && data > 0) {
                        const hours = Math.floor(data / 60);
                        const minutes = data % 60;
                        formattedTime = (hours > 0 ? hours + ' Jam ' : '') + minutes + ' Menit';
                    } else if (data === 0) {
                        formattedTime = 'HABIS / BERHENTI';
                    }
                    return `<span class="waktu-sisa">${formattedTime}</span>`;
            }},
            { 
                "targets": 8, // Status Alert (Auto) - Teks
                "render": (data, type, row) => `<span class="status-alert-text">${data}</span>`
            },
            { 
                "targets": 9, // Informasi Log Terakhir (Log Block)
                "render": (data, type, row) => getLogInfo(row)
            },
            { "targets": 10, "render": function (data, type, row) { // Aksi - LABEL BARU
                let btnStop = `<button class="btn btn-sm btn-danger btn-hentikan" data-no-rawat="${row.no_rawat}" data-pasien="${row.nm_pasien}">Off</button>`; 
                let btnLog = `<button class="btn btn-sm btn-success btn-catat me-1" data-no-rawat="${row.no_rawat}">Pantau</button>`; 
                let btnEdit = `<button class="btn btn-sm btn-warning btn-order me-1" data-no-rawat="${row.no_rawat}">Tx</button>`; 
                if(row.target_tetesan !== 'N/A') return  btnLog + btnEdit + btnStop;
                    let btnOrderBaru = `<button class="btn btn-sm btn-primary btn-order" data-no-rawat="${row.no_rawat}">+ Order Infus</button>`;
                return btnOrderBaru;
            }}
        ]
    });

    function loadInfusData() {
        const selectedUnit = $('#filterUnit').val() || 'ALL'; 
        
        $.ajax({
            url: API_LIST_URL + '?unit=' + selectedUnit + '&_t=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    
                    // --- TAMBAHAN KRUSIAL: TANGKAP WAKTU SERVER DAN KONVERSI KE MILIDETIK ---
                    // Menggunakan let karena nilainya akan dimajukan di interval 5 detik
                    let serverNowTime = new Date(response.server_now.replace(/-/g, "/")).getTime(); 
                    // ----------------------------------------------------------------------
                    
                    infusTable.clear().rows.add(response.data).draw(false);
                    
                    // KONSISTENSI AUTO-DECAY: Segera jalankan updater real-time.
                    clearInterval(realTimeUpdater);
                    
                    if (realTimeUpdater) {
                        clearInterval(realTimeUpdater);
                    }
                    // KIRIM WAKTU SERVER SEBAGAI ACUAN AWAL
                    startRealTimeUpdates(serverNowTime); 
                    
                    // Atur interval: Setiap 5 detik, hitung waktu server maju 5 detik
                    realTimeUpdater = setInterval(function() {
                        // Majukan waktu server yang tersimpan sebanyak interval
                        serverNowTime += REALTIME_UPDATE_INTERVAL;
                        startRealTimeUpdates(serverNowTime);
                    }, REALTIME_UPDATE_INTERVAL); 

                } else {
                    infusTable.clear().draw();
                    checkGlobalAlerts([]); 
                }
            },
            error: function(xhr, status, error) { console.error("AJAX Error:", status, error); checkGlobalAlerts([]); }
        });
    }

    // UBAH: Menerima Waktu Acuan dari Server
    function startRealTimeUpdates(time_base_ms) { 
        infusTable.rows().every(function () {
            let data = this.data();
            let rowNode = this.node();
            if (!data || data.tetesan_aktual === 'N/A' || !data.tgl_catat || !data.jam_catat || !data.faktor_tetes) return;

            const lastLogTime = new Date(data.tgl_catat + 'T' + data.jam_catat).getTime();
            // GANTI: Gunakan waktu dari server (time_base_ms), bukan waktu ponsel (new Date())
            const now = time_base_ms; 
            const elapsedSeconds = (now - lastLogTime) / 1000;
            if (elapsedSeconds < 0) return;
            
            const elapsedMinutes = elapsedSeconds / 60;
            const volumeUsed = (data.tetesan_aktual * elapsedMinutes) / data.faktor_tetes;
            
            // --- LOGIKA STABIL (NON-ITERATIF) ---
            // Hitung volume sisa dari Log awal hingga waktu server saat ini
            let estimatedVolumeSisa = parseFloat(data.volume_sisa) - volumeUsed;
            // --- AKHIR LOGIKA STABIL ---
            
            let estimatedWaktuSisaMenit;

            if (estimatedVolumeSisa <= 0) {
                estimatedVolumeSisa = 0;
                estimatedWaktuSisaMenit = 0;
            } else {
                if (data.tetesan_aktual > 0) {
                    estimatedWaktuSisaMenit = Math.round((estimatedVolumeSisa * data.faktor_tetes) / data.tetesan_aktual);
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
            } else if (data.target_tetesan !== 'N/A' && data.target_tetesan > 0 && Math.abs(data.tetesan_aktual - data.target_tetesan) / data.target_tetesan > 0.20) {
                 currentAlertStatus = 'LAJU TIDAK SESUAI';
            }

            // TIDAK ADA LAGI data.volume_sisa = estimatedVolumeSisa; DI SINI
            data.predicted_alert = currentAlertStatus.replace(/ /g, '_'); 
            
            // Perbarui visualisasi baris (warna)
            $(rowNode).removeClass('alert-kritis alert-warning');
            if (currentAlertStatus.includes('KRITIS') || currentAlertStatus.includes('HABIS')) {
                $(rowNode).addClass('alert-kritis'); 
            } else if (currentAlertStatus.includes('PERINGATAN') || currentAlertStatus.includes('LAJU')) {
                $(rowNode).addClass('alert-warning'); 
            }
            
            // Perbarui teks Status Alert di Kolom 8
            $(rowNode).find('td:eq(8)').find('.status-alert-text').text(currentAlertStatus);

            // --- AKHIR LOGIKA ALERT BARU ---
            
            // Update Volume Sisa (Index 6)
            $(rowNode).find('.volume-sisa').text(estimatedVolumeSisa.toFixed(1));
            // Update Waktu Sisa (Index 7)
            $(rowNode).find('.waktu-sisa').text(formattedTime);
            
            // Update Ikon Baterai (Index 5)
            let iconHtml = getBatteryIcon(estimatedVolumeSisa.toFixed(1), data.volume_total);
            $(rowNode).find('td:eq(5)').html(iconHtml); 
        });
        
        // Memanggil checkGlobalAlerts setelah semua baris diperbarui
        checkGlobalAlerts(infusTable.rows().data().toArray());
    }
    
    function checkGlobalAlerts(data) {
        // Bunyi Alert hanya untuk kondisi KRITIS atau HABIS
        let isCritical = data.some(item => item.predicted_alert === 'KRITIS_HABIS' || item.predicted_alert === 'INFUS_HABIS');
        
        if (isCritical) {
            audioAlert.play().catch(e => console.warn("Audio Alert blocked.", e));
        } else {
            audioAlert.pause();
            audioAlert.currentTime = 0;
        }
    }

    // EVENT HANDLER UNTUK FILTER UNIT
    $('#filterUnit').on('change', function() {
        // Hentikan interval dan muat data baru
        clearInterval(globalDataRefresher); 
        clearInterval(realTimeUpdater);
        loadInfusData(); 
        // Interval akan diatur ulang di dalam loadInfusData
    });


    // INISIALISASI
    loadInfusData(); 
    globalDataRefresher = setInterval(loadInfusData, REFRESH_INTERVAL); 

    // Event handler untuk modal log (dan lainnya) tetap sama...
    $(document).on('click', '.btn-catat', function() {
        const rowData = infusTable.row($(this).parents('tr')).data(); 
        $('#modalNoRawat').val(rowData.no_rawat);
        $('#pasienNamaModal').text(rowData.nm_pasien);
        
        $('#logModalNamaObat').text(rowData.nama_obat_infus || '-');
        $('#logModalTargetTetesan').text(rowData.target_tetesan || '-');
        
        $('#infusLogForm')[0].reset();
        $('#logModal').modal('show');
    });

    $(document).on('click', '.btn-order', function() {
        const rowData = infusTable.row($(this).parents('tr')).data(); 
        $('#modalNoRawatOrder').val(rowData.no_rawat);
        $('#pasienNamaOrder').text(rowData.nm_pasien);

        $('#orderKdObat').val(null).trigger('change'); 
        if (rowData.kd_obat_infus && rowData.nama_obat_infus) {
            var option = new Option(rowData.nama_obat_infus, rowData.kd_obat_infus, true, true);
            $('#orderKdObat').append(option).trigger('change');
        }

        $('#orderVolume').val(rowData.volume_total === 'N/A' ? 500 : rowData.volume_total);
        $('#orderTetesan').val(rowData.target_tetesan === 'N/A' ? 25 : rowData.target_tetesan);
        $('#orderModal').modal('show');
    });

    $(document).on('click', '.btn-hentikan', function() {
        const noRawat = $(this).data('no-rawat');
        const namaPasien = $(this).data('pasien');
        if (confirm(`Anda yakin ingin menghentikan order infus untuk pasien: ${namaPasien}?`)) {
            $.ajax({
                url: API_END_ORDER_URL,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ no_rawat: noRawat }),
                success: (response) => { alert('Order infus berhasil dihentikan.'); loadInfusData(); },
                error: (xhr) => {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    alert("Gagal menghentikan order: " + errorMsg);
                }
            });
        }
    });

    $('#infusLogForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            no_rawat: $('#modalNoRawat').val(),
            tetesan_aktual: parseInt($('#tetesanAktual').val()),
            volume_sisa: parseFloat($('#volumeSisa').val()),
            kd_petugas: $('#modalKdPetugas').val(), 
            keterangan_temuan: $('#keteranganTemuan').val()
        };
        $.ajax({
            url: API_ADD_LOG_URL, type: 'POST', contentType: 'application/json',
            data: JSON.stringify(formData),
            success: (response) => { alert("Sukses! Log berhasil disimpan."); $('#logModal').modal('hide'); loadInfusData(); },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                alert("Gagal menyimpan log: " + errorMsg);
            }
        });
    });

    $('#infusOrderForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            no_rawat: $('#modalNoRawatOrder').val(),
            kd_obat_infus: $('#orderKdObat').val(),
            volume_total: $('#orderVolume').val(),
            target_tetesan: $('#orderTetesan').val(),
            kd_petugas: $('#modalKdPetugasOrder').val() 
        };
        $.ajax({
            url: API_ADD_ORDER_URL, type: 'POST', contentType: 'application/json',
            data: JSON.stringify(formData),
            success: (response) => { alert("Sukses! Order Infus berhasil disimpan."); $('#orderModal').modal('hide'); loadInfusData(); },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    alert("Gagal menyimpan order: " + errorMsg);
                }
            });
    });
});