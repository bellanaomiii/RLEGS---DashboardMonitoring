/**
 * revenue.js - Script untuk menangani fungsionalitas pada Revenue Data Dashboard
 * Menangani fungsi edit, divisi dropdown, dan fitur lainnya
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi fungsi edit pada tiap tab
    initEditAccountManager();
    initEditCorporateCustomer();
    initEditRevenue();

    // Inisialisasi fitur yang sudah ada
    initDivisiButtonGroup();
    setupTabs();
    setupDeleteConfirmation();

    // Inisialisasi dropdown divisi pada form tambah revenue
    initAccountManagerDivisiDropdown();

    // Sembunyikan loading overlay ketika halaman dimuat
    hideAllLoadingOverlays();
});

/**
 * Inisialisasi dropdown divisi yang terkait dengan account manager pada form revenue
 */
function initAccountManagerDivisiDropdown() {
    // Pengaturan event handler untuk input account manager
    const accountManagerInput = document.getElementById('account_manager');
    const accountManagerIdInput = document.getElementById('account_manager_id');

    if (!accountManagerInput || !accountManagerIdInput) return;

    // Fungsi untuk memproses pemilihan account manager
    function handleAccountManagerSelection() {
        const accountManagerId = accountManagerIdInput.value;
        const divisiSelect = document.getElementById('divisi_id');

        if (!divisiSelect) return;

        if (accountManagerId) {
            console.log(`Account Manager selected: ${accountManagerInput.value} (ID: ${accountManagerId})`);

            // Matikan select divisi selama loading
            divisiSelect.disabled = true;
            divisiSelect.innerHTML = '<option value="">Loading divisi...</option>';

            // Fetch divisi options dari server
            fetch(`/api/account-manager/${accountManagerId}/divisi`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset dropdown
                    divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                    if (data.success && data.divisis && data.divisis.length > 0) {
                        // Tambahkan options untuk setiap divisi
                        data.divisis.forEach(divisi => {
                            const option = document.createElement('option');
                            option.value = divisi.id;
                            option.textContent = divisi.nama;
                            divisiSelect.appendChild(option);
                        });

                        // Enable select
                        divisiSelect.disabled = false;

                        // Log info
                        console.log(
                            `Loaded ${data.divisis.length} divisi options for Account Manager ID: ${accountManagerId}`
                        );
                    } else {
                        // Jika tidak ada divisi, tampilkan pesan
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = "Tidak ada divisi terkait";
                        divisiSelect.appendChild(option);

                        // Disable select
                        divisiSelect.disabled = true;

                        console.warn(`No divisions found for Account Manager ID: ${accountManagerId}`);
                    }
                })
                .catch(error => {
                    console.error('Error fetching divisi data:', error);

                    // Reset dropdown dengan pesan error
                    divisiSelect.innerHTML = '<option value="">Error loading divisi</option>';
                    divisiSelect.disabled = true;
                });
        } else {
            // Reset divisi select jika tidak ada account manager yang dipilih
            divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
            divisiSelect.disabled = true;
        }
    }

    // Event listener untuk perubahan pada input account manager
    // Gunakan event yang ada jika menggunakan library autocomplete
    if (typeof $ !== 'undefined') {
        // Jika menggunakan jQuery/Bootstrap autocomplete
        $(document).on('accountManagerSelected', function(event, data) {
            if (data && data.id) {
                accountManagerIdInput.value = data.id;
                handleAccountManagerSelection();
            }
        });
    }

    // Backup untuk metode standar
    accountManagerInput.addEventListener('change', function() {
        // Jika nilai sudah diset oleh autocomplete plugin
        setTimeout(handleAccountManagerSelection, 100);
    });

    // Deteksi event kustom jika digunakan
    document.addEventListener('amSelected', function(event) {
        if (event.detail && event.detail.id) {
            accountManagerIdInput.value = event.detail.id;
            handleAccountManagerSelection();
        }
    });

    // Tambahan event listener untuk opsi klik pada suggestion
    document.addEventListener('click', function(event) {
        if (event.target.closest('.suggestion-item')) {
            // Tunggu sebentar agar nilai accountManagerIdInput diperbarui
            setTimeout(handleAccountManagerSelection, 100);
        }
    });
}

/**
 * Menangani fungsi edit Account Manager
 */
function initEditAccountManager() {
    // Tambahkan event listener untuk tombol edit Account Manager
    const editButtons = document.querySelectorAll('.edit-account-manager');

    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const accountManagerId = this.getAttribute('data-id');

            if (!accountManagerId) {
                showNotification('ID Account Manager tidak valid', 'error');
                return;
            }

            // Tampilkan loading overlay
            document.getElementById('edit-am-loading').style.display = 'flex';

            // Fetch data Account Manager untuk diedit
            fetch(`/api/account-manager/${accountManagerId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Isi form dengan data yang diterima
                        fillAccountManagerEditForm(data.data);

                        // Tampilkan modal edit
                        const editModal = new bootstrap.Modal(document.getElementById('editAccountManagerModal'));
                        editModal.show();
                    } else {
                        showNotification(data.message || 'Gagal mengambil data Account Manager', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Account Manager data:', error);
                    showNotification('Terjadi kesalahan saat mengambil data Account Manager', 'error');
                })
                .finally(() => {
                    // Sembunyikan loading overlay
                    document.getElementById('edit-am-loading').style.display = 'none';
                });
        });
    });

    // Setup for edit form submission
    const editAmForm = document.getElementById('editAmForm');
    if (editAmForm) {
        editAmForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validasi divisi dipilih
            const divisiIdsInput = document.getElementById('edit_divisi_ids');
            if (!divisiIdsInput.value) {
                showNotification('Silakan pilih minimal satu divisi!', 'warning');
                return;
            }

            // Dapatkan ID account manager yang sedang diedit
            const accountManagerId = document.getElementById('edit_am_id').value;

            // Tampilkan loading state pada button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

            // Submit form dengan AJAX
            const formData = new FormData(this);

            fetch(`/api/account-manager/${accountManagerId}/update`, {
                method: 'PUT', // PERUBAHAN: Ubah dari POST menjadi PUT
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editAccountManagerModal'));
                    if (modal) modal.hide();

                    // Tampilkan notifikasi sukses
                    showNotification(data.message || 'Data Account Manager berhasil diperbarui', 'success');

                    // Reload halaman untuk menampilkan perubahan
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Tampilkan pesan error
                    showNotification(data.message || 'Gagal memperbarui data Account Manager', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating Account Manager:', error);
                showNotification('Terjadi kesalahan saat memperbarui data', 'error');
            })
            .finally(() => {
                // Kembalikan button ke state awal
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
}

/**
 * Mengisi form edit Account Manager dengan data yang diambil dari server
 */
function fillAccountManagerEditForm(data) {
    // Isi form dengan data Account Manager
    document.getElementById('edit_am_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_nik').value = data.nik;

    // Set value dropdown witel dan regional
    const witelSelect = document.getElementById('edit_witel_id');
    const regionalSelect = document.getElementById('edit_regional_id');

    if (witelSelect) witelSelect.value = data.witel_id;
    if (regionalSelect) regionalSelect.value = data.regional_id;

    // Reset divisi buttons terlebih dahulu
    const divisiButtons = document.querySelectorAll('.edit-divisi-btn-group .divisi-btn');
    divisiButtons.forEach(button => {
        button.classList.remove('active');
        button.style.backgroundColor = '#f8f9fa';
        button.style.color = 'black';
        button.style.borderColor = '#ddd';
    });

    // Set divisi yang aktif
    if (data.divisis && data.divisis.length > 0) {
        const divisiIds = data.divisis.map(divisi => divisi.id);

        // Update hidden input dengan divisi IDs
        document.getElementById('edit_divisi_ids').value = divisiIds.join(',');

        // Aktifkan button untuk setiap divisi
        divisiButtons.forEach(button => {
            const divisiId = parseInt(button.dataset.divisiId);
            if (divisiIds.includes(divisiId)) {
                button.classList.add('active');
                button.style.backgroundColor = '#0d6efd';
                button.style.color = 'white';
                button.style.borderColor = '#0d6efd';
            }
        });
    }
}

/**
 * Menangani fungsi edit Corporate Customer
 */
function initEditCorporateCustomer() {
    // Tambahkan event listener untuk tombol edit Corporate Customer
    const editButtons = document.querySelectorAll('.edit-corporate-customer');

    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const customerId = this.getAttribute('data-id');

            if (!customerId) {
                showNotification('ID Corporate Customer tidak valid', 'error');
                return;
            }

            // Tampilkan loading overlay
            document.getElementById('edit-cc-loading').style.display = 'flex';

            // Fetch data Corporate Customer untuk diedit
            fetch(`/api/corporate-customer/${customerId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Isi form dengan data yang diterima
                        document.getElementById('edit_cc_id').value = data.data.id;
                        document.getElementById('edit_nama_customer').value = data.data.nama;
                        document.getElementById('edit_nipnas').value = data.data.nipnas;

                        // Tampilkan modal edit
                        const editModal = new bootstrap.Modal(document.getElementById('editCorporateCustomerModal'));
                        editModal.show();
                    } else {
                        showNotification(data.message || 'Gagal mengambil data Corporate Customer', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Corporate Customer data:', error);
                    showNotification('Terjadi kesalahan saat mengambil data Corporate Customer', 'error');
                })
                .finally(() => {
                    // Sembunyikan loading overlay
                    document.getElementById('edit-cc-loading').style.display = 'none';
                });
        });
    });

    // Setup for edit form submission
    const editCcForm = document.getElementById('editCcForm');
    if (editCcForm) {
        editCcForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Dapatkan ID corporate customer yang sedang diedit
            const customerId = document.getElementById('edit_cc_id').value;

            // Tampilkan loading state pada button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

            // Submit form dengan AJAX
            const formData = new FormData(this);

            fetch(`/api/corporate-customer/${customerId}/update`, {
                method: 'PUT', // PERUBAHAN: Ubah dari POST menjadi PUT
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCorporateCustomerModal'));
                    if (modal) modal.hide();

                    // Tampilkan notifikasi sukses
                    showNotification(data.message || 'Data Corporate Customer berhasil diperbarui', 'success');

                    // Reload halaman untuk menampilkan perubahan
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Tampilkan pesan error
                    showNotification(data.message || 'Gagal memperbarui data Corporate Customer', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating Corporate Customer:', error);
                showNotification('Terjadi kesalahan saat memperbarui data', 'error');
            })
            .finally(() => {
                // Kembalikan button ke state awal
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
}

/**
 * Menangani fungsi edit Revenue
 */
function initEditRevenue() {
    // Tambahkan event listener untuk tombol edit Revenue
    const editButtons = document.querySelectorAll('.edit-revenue');

    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const revenueId = this.getAttribute('data-id');

            if (!revenueId) {
                showNotification('ID Revenue tidak valid', 'error');
                return;
            }

            // Tampilkan loading overlay
            document.getElementById('edit-revenue-loading').style.display = 'flex';

            // Fetch data Revenue untuk diedit
            fetch(`/api/revenue/${revenueId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Isi form dengan data yang diterima
                        fillRevenueEditForm(data.data);

                        // Tampilkan modal edit
                        const editModal = new bootstrap.Modal(document.getElementById('editRevenueModal'));
                        editModal.show();
                    } else {
                        showNotification(data.message || 'Gagal mengambil data Revenue', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Revenue data:', error);
                    showNotification('Terjadi kesalahan saat mengambil data Revenue', 'error');
                })
                .finally(() => {
                    // Sembunyikan loading overlay
                    document.getElementById('edit-revenue-loading').style.display = 'none';
                });
        });
    });

    // Setup for edit form submission
    const editRevenueForm = document.getElementById('editRevenueForm');
    if (editRevenueForm) {
        editRevenueForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Dapatkan ID revenue yang sedang diedit
            const revenueId = document.getElementById('edit_revenue_id').value;

            // Tampilkan loading state pada button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

            // Submit form dengan AJAX
            const formData = new FormData(this);

            fetch(`/api/revenue/${revenueId}/update`, {
                method: 'PUT', // PERUBAHAN: Ubah dari POST menjadi PUT
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editRevenueModal'));
                    if (modal) modal.hide();

                    // Tampilkan notifikasi sukses
                    showNotification(data.message || 'Data Revenue berhasil diperbarui', 'success');

                    // Reload halaman untuk menampilkan perubahan
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Tampilkan pesan error
                    showNotification(data.message || 'Gagal memperbarui data Revenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating Revenue:', error);
                showNotification('Terjadi kesalahan saat memperbarui data', 'error');
            })
            .finally(() => {
                // Kembalikan button ke state awal
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
}

/**
 * Mengisi form edit Revenue dengan data yang diambil dari server
 */
function fillRevenueEditForm(data) {
    // Isi form dengan data Revenue
    document.getElementById('edit_revenue_id').value = data.id;
    document.getElementById('edit_account_manager').value = data.account_manager.nama;
    document.getElementById('edit_account_manager_id').value = data.account_manager_id;
    document.getElementById('edit_divisi_nama').value = data.divisi ? data.divisi.nama : 'N/A';
    document.getElementById('edit_divisi_id').value = data.divisi_id;
    document.getElementById('edit_corporate_customer').value = data.corporate_customer.nama;
    document.getElementById('edit_corporate_customer_id').value = data.corporate_customer_id;
    document.getElementById('edit_target_revenue').value = data.target_revenue;
    document.getElementById('edit_real_revenue').value = data.real_revenue;

    // Format bulan untuk display
    const bulanDate = new Date(data.bulan + '-01');
    const formattedBulan = bulanDate.toLocaleString('id-ID', { month: 'long', year: 'numeric' });

    document.getElementById('edit_bulan_display').value = formattedBulan;
    document.getElementById('edit_bulan').value = data.bulan;
}

/**
 * Fungsi untuk inisialisasi divisi button group
 */
function initDivisiButtonGroup() {
    console.log('Initializing divisi button group');

    // For regular divisi buttons
    initDivisiButtons('.divisi-btn-group:not(.edit-divisi-btn-group)', 'divisi_ids');

    // For edit form divisi buttons
    initDivisiButtons('.edit-divisi-btn-group', 'edit_divisi_ids');
}

/**
 * Inisialisasi divisi buttons dalam container tertentu
 */
function initDivisiButtons(containerSelector, targetInputId) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const divisiButtons = container.querySelectorAll('.divisi-btn');
    const divisiIdsInput = document.getElementById(targetInputId);

    if (!divisiIdsInput) return;

    console.log(`Found ${divisiButtons.length} divisi buttons for ${targetInputId}`);

    if (divisiButtons.length === 0) {
        console.warn('No divisi buttons found!');

        // Tambahkan fallback untuk divisi jika tidak ada dari server
        if (container.children.length === 0) {
            console.log('Adding fallback divisi buttons');

            // Data divisi yang tetap (3 divisi yang ada)
            const divisiData = [
                { id: 1, nama: 'DGS' },
                { id: 2, nama: 'DPS' },
                { id: 3, nama: 'DSS' }
            ];

            // Tambahkan button untuk setiap divisi
            divisiData.forEach(div => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'divisi-btn';
                btn.dataset.divisiId = div.id;
                btn.textContent = div.nama;
                btn.style.padding = '8px 15px';
                btn.style.border = '1px solid #ddd';
                btn.style.borderRadius = '4px';
                btn.style.backgroundColor = '#f8f9fa';
                btn.style.cursor = 'pointer';
                btn.style.margin = '0 5px 5px 0';
                btn.style.display = 'inline-block';

                container.appendChild(btn);
            });

            // Panggil kembali fungsi ini setelah menambahkan button
            setTimeout(() => initDivisiButtons(containerSelector, targetInputId), 100);
            return;
        }
    }

    divisiButtons.forEach(button => {
        // Hapus event listener lama untuk mencegah duplikasi
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Tambahkan event listener pada button yang baru
        newButton.addEventListener('click', function() {
            console.log(`Button clicked: ${this.dataset.divisiId}`);
            this.classList.toggle('active');

            // Ubah gaya visual saat button aktif/tidak aktif
            if (this.classList.contains('active')) {
                this.style.backgroundColor = '#0d6efd';
                this.style.color = 'white';
                this.style.borderColor = '#0d6efd';
            } else {
                this.style.backgroundColor = '#f8f9fa';
                this.style.color = 'black';
                this.style.borderColor = '#ddd';
            }

            // Update hidden input value
            updateDivisiIds(containerSelector, targetInputId);
        });
    });
}

/**
 * Update nilai input hidden berdasarkan button yang aktif
 */
function updateDivisiIds(containerSelector, targetInputId) {
    const container = document.querySelector(containerSelector);
    const activeButtons = container.querySelectorAll('.divisi-btn.active');
    const divisiIdsInput = document.getElementById(targetInputId);

    const selectedIds = Array.from(activeButtons).map(btn => btn.dataset.divisiId);
    divisiIdsInput.value = selectedIds.join(',');
    console.log(`Selected divisi IDs for ${targetInputId}:`, divisiIdsInput.value);
}

/**
 * Setup tabs untuk navigasi antar tab content
 */
function setupTabs() {
    const tabItems = document.querySelectorAll('.tab-item');

    tabItems.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabItems.forEach(item => item.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all tab content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));

            // Show the selected tab content
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

/**
 * Setup konfirmasi sebelum menghapus data
 */
function setupDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Konfirmasi dengan SweetAlert jika tersedia, atau dengan confirm() biasa
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    this.submit();
                }
            }
        });
    });
}

/**
 * Menyembunyikan semua loading overlay
 */
function hideAllLoadingOverlays() {
    const overlays = document.querySelectorAll('.modal-loading-overlay');
    overlays.forEach(overlay => {
        overlay.style.display = 'none';
    });
}

/**
 * Menampilkan notifikasi
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Cek apakah ada persistent notification container
    const persistentNotif = document.getElementById('notification-container');
    if (persistentNotif) {
        // Set title berdasarkan tipe
        const titleElement = document.getElementById('notification-title');
        if (titleElement) {
            switch (type) {
                case 'success':
                    titleElement.textContent = 'Sukses';
                    break;
                case 'error':
                    titleElement.textContent = 'Error';
                    break;
                case 'warning':
                    titleElement.textContent = 'Peringatan';
                    break;
                default:
                    titleElement.textContent = 'Informasi';
            }
        }

        // Set message
        const messageElement = document.getElementById('notification-message');
        if (messageElement) {
            messageElement.textContent = message;
        }

        // Tambahkan kelas tipe dan show
        persistentNotif.className = `notification-persistent ${type} show`;

        // Tambahkan event listener untuk tombol close
        const closeBtn = document.getElementById('notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                persistentNotif.classList.remove('show');
            });
        }

        // Auto hide setelah beberapa detik jika bukan error
        if (type !== 'error') {
            setTimeout(() => {
                persistentNotif.classList.remove('show');
            }, duration);
        }

        return;
    }

    // Fallback ke snackbar jika tidak ada persistent notification
    const snackbar = document.getElementById('snackbar');
    if (snackbar) {
        snackbar.className = `show ${type}`;
        snackbar.textContent = message;

        setTimeout(() => {
            snackbar.className = snackbar.className.replace('show', '');
        }, duration);
        return;
    }

    // Fallback ke alert jika tidak ada UI notification
    if (type === 'error') {
        alert(`Error: ${message}`);
    } else {
        alert(message);
    }
}

/**
 * Fungsi untuk reload data setelah import selesai
 */
window.reloadRevenueData = function() {
    // Reload halaman dengan parameter timestamp untuk mencegah cache
    window.location.href = "/revenue_data?t=" + new Date().getTime();
};