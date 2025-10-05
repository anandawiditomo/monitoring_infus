// File Baru: master_infus.js

$(document).ready(function() {
    // GANTI 'index.php' MENJADI 'master.php'
    const BASE_API_URL_ROOT = 'https://web.budirahayu.com/api/infusDemo/v1/'; 
    const API_MASTER_LIST = BASE_API_URL_ROOT + 'master.php/master_list';
    const API_MASTER_GET = BASE_API_URL_ROOT + 'master.php/master_get';
    const API_MASTER_ADD = BASE_API_URL_ROOT + 'master.php/master_add';
    const API_MASTER_UPDATE = BASE_API_URL_ROOT + 'master.php/master_update';
    const API_MASTER_DELETE = BASE_API_URL_ROOT + 'master.php/master_delete';    
    
    let masterInfusTable = $('#masterInfusTable').DataTable({
        "processing": true,
        "ajax": {
            "url": API_MASTER_LIST + '?_t=' + new Date().getTime(),
            "dataSrc": "data"
        },
        "columns": [
            { "data": "kd_obat_infus" }, 
            { "data": "nama_obat_infus" }, 
            { "data": "volume_standar" },
            { "data": "faktor_tetes" },
            { "data": "kd_obat_infus" }
        ],
        "columnDefs": [
            {
                "targets": 4, 
                "render": function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit-master me-2" data-id="${data}">Edit</button>
                        <button class="btn btn-sm btn-danger btn-delete-master" data-id="${data}" data-nama="${row.nama_obat_infus}">Hapus</button>
                    `;
                }
            }
        ]
    });

    // 1. TAMPILKAN MODAL TAMBAH BARU
    $('#btnAddMaster').on('click', function() {
        $('#masterForm')[0].reset();
        $('#masterModalLabel').text('Tambah Cairan Infus Baru');
        $('#actionType').val('add');
        $('#kd_obat_infus').prop('readonly', false);
        $('#kdHelp').show();
        $('#btnSubmitMaster').text('Simpan Baru').removeClass('btn-warning').addClass('btn-success');
        $('#masterModal').modal('show');
    });

    // 2. TAMPILKAN MODAL EDIT
    $(document).on('click', '.btn-edit-master', function() {
        const kd_obat_infus = $(this).data('id');
        
        $.ajax({
            url: API_MASTER_GET + '?id=' + kd_obat_infus,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    $('#masterModalLabel').text('Edit Cairan Infus: ' + data.kd_obat_infus);
                    $('#actionType').val('update');
                    
                    // Isi form
                    $('#kd_obat_infus').val(data.kd_obat_infus).prop('readonly', true); // Kode tidak bisa diubah saat edit
                    $('#nama_obat_infus').val(data.nama_obat_infus);
                    $('#volume_standar').val(data.volume_standar);
                    $('#faktor_tetes').val(data.faktor_tetes);

                    $('#kdHelp').hide();
                    $('#btnSubmitMaster').text('Update Data').removeClass('btn-success').addClass('btn-warning');
                    $('#masterModal').modal('show');
                } else {
                    alert("Data tidak ditemukan.");
                }
            },
            error: function() { alert("Gagal mengambil data."); }
        });
    });

    // 3. SUBMIT FORM (ADD/UPDATE)
    $('#masterForm').on('submit', function(e) {
        e.preventDefault();
        const action = $('#actionType').val();
        const url = (action === 'add') ? API_MASTER_ADD : API_MASTER_UPDATE;
        
        const formData = {
            kd_obat_infus: $('#kd_obat_infus').val(),
            nama_obat_infus: $('#nama_obat_infus').val(),
            volume_standar: $('#volume_standar').val(),
            faktor_tetes: $('#faktor_tetes').val()
        };

        $.ajax({
            url: url, type: 'POST', contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                alert(response.message);
                $('#masterModal').modal('hide');
                masterInfusTable.ajax.reload(null, false); // Reload DataTables
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                alert(errorMsg);
            }
        });
    });

    // 4. DELETE MASTER INFUS
    $(document).on('click', '.btn-delete-master', function() {
        const kd_obat_infus = $(this).data('id');
        const nama = $(this).data('nama');
        
        if (confirm(`Anda yakin ingin menghapus Master Infus: ${nama} (${kd_obat_infus})?`)) {
            $.ajax({
                url: API_MASTER_DELETE,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ kd_obat_infus: kd_obat_infus }),
                success: function(response) {
                    alert(response.message);
                    masterInfusTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus.';
                    alert(errorMsg);
                }
            });
        }
    });

});