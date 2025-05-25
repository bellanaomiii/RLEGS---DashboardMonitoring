    /**
     * revenue.js - Script terintegrasi penuh untuk Revenue Data Dashboard
     * Full integration dengan controller export import terbaru
     */

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Revenue dashboard JavaScript initialized');

        // Initialize CSRF token untuk semua AJAX request
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }

        // Initialize semua functionality
        initImportHandlers();
        initEditFunctionality();
        initSearchAndAutocomplete();
        initDivisiButtonGroup();
        initTabFunctionality();
        initFilterToggle();
        initDeleteConfirmation();
        initNotificationSystem();
        initUtilityFunctions();
        initEnhancedErrorHandling();

        hideAllLoadingOverlays();
    });

    // ===== ENHANCED ERROR HANDLING =====
    function initEnhancedErrorHandling() {
        console.log('Initializing enhanced error handling');

        // Error log download functionality
        document.getElementById('downloadErrorLog')?.addEventListener('click', function() {
            downloadErrorLog();
        });
    }

    function showEnhancedImportError(response, typeName) {
        console.log('Showing enhanced import error:', response);

        const modal = document.getElementById('enhancedImportErrorModal');
        const modalLabel = document.getElementById('enhancedImportErrorModalLabel');

        if (modalLabel) {
            modalLabel.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Detail Error Import ${typeName}`;
        }

        const data = response.data || {};

        // Update summary statistics
        updateErrorSummaryStats(data);

        // Populate error details
        populateErrorDetails(data);

        // Generate suggestions
        generateErrorSuggestions(data);

        // Show modal
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    function updateErrorSummaryStats(data) {
        const totalRows = (data.imported || 0) + (data.updated || 0) + (data.duplicates || 0) + (data.errors || 0);
        const successRows = (data.imported || 0) + (data.updated || 0);
        const failedRows = data.errors || 0;
        const duplicateRows = data.duplicates || 0;

        document.getElementById('error-total-rows').textContent = totalRows;
        document.getElementById('error-success-rows').textContent = successRows;
        document.getElementById('error-failed-rows').textContent = failedRows;
        document.getElementById('error-duplicate-rows').textContent = duplicateRows;
    }

    function populateErrorDetails(data) {
        const validationContainer = document.getElementById('validation-error-list');
        const dataContainer = document.getElementById('data-error-list');

        if (validationContainer) validationContainer.innerHTML = '';
        if (dataContainer) dataContainer.innerHTML = '';

        if (data.error_details && data.error_details.length > 0) {
            data.error_details.forEach((error, index) => {
                const errorItem = createErrorItem(error, index + 1);

                // Categorize errors
                if (isValidationError(error)) {
                    validationContainer?.appendChild(errorItem);
                } else {
                    dataContainer?.appendChild(errorItem);
                }
            });
        }
    }

    function createErrorItem(error, rowNumber) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-item-enhanced';

        const errorType = getErrorType(error);
        const errorIcon = getErrorIcon(errorType);
        const errorColor = getErrorColor(errorType);

        errorDiv.innerHTML = `
            <div class="error-header">
                <span class="error-row-number">Baris ${rowNumber}</span>
                <span class="error-type ${errorColor}">
                    <i class="${errorIcon} me-1"></i>${errorType}
                </span>
            </div>
            <div class="error-message">${error}</div>
            <div class="error-suggestion">${getErrorSuggestion(error)}</div>
        `;

        return errorDiv;
    }

    function isValidationError(error) {
        const validationKeywords = ['required', 'format', 'invalid', 'missing', 'empty', 'null'];
        return validationKeywords.some(keyword => error.toLowerCase().includes(keyword));
    }

    function getErrorType(error) {
        if (error.toLowerCase().includes('nik')) return 'NIK Error';
        if (error.toLowerCase().includes('nama')) return 'Nama Error';
        if (error.toLowerCase().includes('witel')) return 'Witel Error';
        if (error.toLowerCase().includes('regional')) return 'Regional Error';
        if (error.toLowerCase().includes('divisi')) return 'Divisi Error';
        if (error.toLowerCase().includes('nipnas')) return 'NIPNAS Error';
        if (error.toLowerCase().includes('revenue')) return 'Revenue Error';
        return 'General Error';
    }

    function getErrorIcon(errorType) {
        const iconMap = {
            'NIK Error': 'fas fa-id-card',
            'Nama Error': 'fas fa-user',
            'Witel Error': 'fas fa-building',
            'Regional Error': 'fas fa-map-marker-alt',
            'Divisi Error': 'fas fa-sitemap',
            'NIPNAS Error': 'fas fa-hashtag',
            'Revenue Error': 'fas fa-dollar-sign',
            'General Error': 'fas fa-exclamation-circle'
        };
        return iconMap[errorType] || 'fas fa-exclamation-circle';
    }

    function getErrorColor(errorType) {
        const colorMap = {
            'NIK Error': 'text-danger',
            'Nama Error': 'text-warning',
            'Witel Error': 'text-info',
            'Regional Error': 'text-info',
            'Divisi Error': 'text-primary',
            'NIPNAS Error': 'text-warning',
            'Revenue Error': 'text-success',
            'General Error': 'text-secondary'
        };
        return colorMap[errorType] || 'text-secondary';
    }

    function getErrorSuggestion(error) {
        if (error.toLowerCase().includes('nik')) {
            return 'Pastikan NIK berupa 4-10 digit angka tanpa spasi atau karakter khusus.';
        }
        if (error.toLowerCase().includes('nama') && error.toLowerCase().includes('empty')) {
            return 'Kolom nama tidak boleh kosong. Periksa kembali data di Excel.';
        }
        if (error.toLowerCase().includes('witel')) {
            return 'Nama Witel harus sesuai dengan data master. Cek ejaan dan format.';
        }
        if (error.toLowerCase().includes('regional')) {
            return 'Regional harus berformat "TREG-X" dimana X adalah angka 1-7.';
        }
        if (error.toLowerCase().includes('nipnas')) {
            return 'NIPNAS berupa 3-20 digit angka. Periksa format data.';
        }
        if (error.toLowerCase().includes('revenue')) {
            return 'Revenue harus berupa angka positif tanpa format mata uang.';
        }
        return 'Periksa format data sesuai template yang disediakan.';
    }

    function generateErrorSuggestions(data) {
        const suggestionsContainer = document.getElementById('error-suggestions');
        if (!suggestionsContainer) return;

        const suggestions = [];

        if (data.errors > 0) {
            suggestions.push({
                icon: 'fas fa-file-excel',
                title: 'Periksa Template Excel',
                description: 'Pastikan format file sesuai dengan template yang disediakan dan tidak ada kolom yang hilang.'
            });

            suggestions.push({
                icon: 'fas fa-spell-check',
                title: 'Validasi Data Master',
                description: 'Periksa ejaan nama Witel, Regional, dan Divisi sesuai dengan data master sistem.'
            });

            suggestions.push({
                icon: 'fas fa-sort-numeric-up',
                title: 'Format Angka',
                description: 'Pastikan NIK dan NIPNAS berupa angka saja tanpa spasi, tanda baca, atau karakter khusus.'
            });

            suggestions.push({
                icon: 'fas fa-list-ul',
                title: 'Cek Kelengkapan Data',
                description: 'Pastikan semua kolom wajib terisi dan tidak ada sel yang kosong.'
            });
        }

        let suggestionsHTML = '';
        suggestions.forEach(suggestion => {
            suggestionsHTML += `
                <div class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="${suggestion.icon}"></i>
                    </div>
                    <div class="suggestion-content">
                        <h6>${suggestion.title}</h6>
                        <p>${suggestion.description}</p>
                    </div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = suggestionsHTML;
    }

    function downloadErrorLog() {
        // Get error data from the current modal
        const errorItems = document.querySelectorAll('.error-item-enhanced');
        let logContent = `ERROR LOG - ${new Date().toISOString()}\n`;
        logContent += `==========================================\n\n`;

        errorItems.forEach((item, index) => {
            const rowNumber = item.querySelector('.error-row-number')?.textContent || `Error ${index + 1}`;
            const errorType = item.querySelector('.error-type')?.textContent || 'Unknown';
            const errorMessage = item.querySelector('.error-message')?.textContent || 'No message';
            const suggestion = item.querySelector('.error-suggestion')?.textContent || 'No suggestion';

            logContent += `${rowNumber}\n`;
            logContent += `Type: ${errorType}\n`;
            logContent += `Message: ${errorMessage}\n`;
            logContent += `Suggestion: ${suggestion}\n`;
            logContent += `------------------------------------------\n\n`;
        });

        // Create and download file
        const blob = new Blob([logContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `import_error_log_${new Date().toISOString().split('T')[0]}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // ===== IMPORT HANDLERS =====
    function initImportHandlers() {
        console.log('Initializing import handlers');

        // Account Manager Import
        const amImportForm = document.getElementById('amImportForm');
        if (amImportForm) {
            amImportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleImportSubmission(this, 'Account Manager', '#addAccountManagerModal');
            });
        }

        // Corporate Customer Import
        const ccImportForm = document.getElementById('ccImportForm');
        if (ccImportForm) {
            ccImportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleImportSubmission(this, 'Corporate Customer', '#addCorporateCustomerModal');
            });
        }

        // Revenue Import
        const revenueImportForm = document.getElementById('revenueImportForm');
        if (revenueImportForm) {
            revenueImportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleImportSubmission(this, 'Revenue', '#importRevenueModal');
            });
        }
    }

    function handleImportSubmission(form, typeName, modalSelector) {
        console.log(`Handling ${typeName} import submission`);

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');

        // Show loading state
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengimpor Data...';
        submitButton.disabled = true;

        // Hide the modal
        if (typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(document.querySelector(modalSelector));
            if (modal) modal.hide();
        }

        // Show processing indicator
        showProcessingIndicator();

        // Determine the correct route based on form action
        const actionUrl = form.getAttribute('action');

        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            hideProcessingIndicator();

            // Reset form and button
            form.reset();
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;

            // Show appropriate result modal
            if (response.success && (!response.data || !response.data.errors || response.data.errors === 0)) {
                showImportResult(response, typeName);
            } else {
                // Show enhanced error modal for detailed error handling
                showEnhancedImportError(response, typeName);
            }
        })
        .catch(error => {
            console.error('Import error:', error);
            hideProcessingIndicator();

            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;

            // Show error in enhanced modal
            showEnhancedImportError({
                success: false,
                message: 'Terjadi kesalahan saat mengimpor data.',
                data: {
                    imported: 0,
                    updated: 0,
                    duplicates: 0,
                    errors: 1,
                    error_details: [error.message || 'Network error atau server tidak merespons']
                }
            }, typeName);
        });
    }

    function showImportResult(response, typeName) {
        console.log('Showing import result:', response);

        // Set modal title and type
        const modalLabel = document.getElementById('importResultModalLabel');
        const importTypeName = document.getElementById('import-type-name');

        if (modalLabel) modalLabel.innerHTML = `<i class="fas fa-chart-bar me-2"></i>Hasil Import ${typeName}`;
        if (importTypeName) importTypeName.textContent = typeName;

        const data = response.data || {};

        // Calculate totals
        const totalRows = (data.imported || 0) + (data.updated || 0) + (data.duplicates || 0) + (data.errors || 0);
        const successRows = (data.imported || 0) + (data.updated || 0);
        const errorRows = data.errors || 0;

        // Update statistics
        const totalRowsEl = document.getElementById('total-rows');
        const successRowsEl = document.getElementById('success-rows');
        const errorRowsEl = document.getElementById('error-rows');

        if (totalRowsEl) totalRowsEl.textContent = totalRows;
        if (successRowsEl) successRowsEl.textContent = successRows;
        if (errorRowsEl) errorRowsEl.textContent = errorRows;

        // Show success message
        const successMessage = document.getElementById('success-message');
        const errorSection = document.getElementById('error-section');

        if (successMessage) successMessage.style.display = 'block';
        if (errorSection) errorSection.style.display = 'none';

        let successText = `Semua ${totalRows} baris data berhasil diimpor ke database.`;
        if (data.imported > 0 && data.updated > 0) {
            successText = `${data.imported} data baru ditambahkan dan ${data.updated} data diperbarui.`;
        } else if (data.imported > 0) {
            successText = `${data.imported} data baru berhasil ditambahkan.`;
        } else if (data.updated > 0) {
            successText = `${data.updated} data berhasil diperbarui.`;
        }

        const successDetails = document.getElementById('success-details');
        if (successDetails) successDetails.textContent = successText;

        // Show modal
        const importResultModal = document.getElementById('importResultModal');
        if (importResultModal && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(importResultModal);
            modal.show();
        }

        // Start auto refresh countdown
        startAutoRefreshCountdown();
    }

    function startAutoRefreshCountdown() {
        console.log('Starting auto refresh countdown');

        let countdown = 20;

        const updateCountdown = () => {
            const refreshCountdown = document.getElementById('refresh-countdown');

            if (refreshCountdown) refreshCountdown.textContent = countdown;

            if (countdown <= 0) {
                window.location.reload();
                return;
            }

            countdown--;
            setTimeout(updateCountdown, 1000);
        };

        updateCountdown();
    }

    function showProcessingIndicator() {
        const indicator = document.getElementById('importProcessingIndicator');
        if (indicator) {
            indicator.style.display = 'block';
        }
    }

    function hideProcessingIndicator() {
        const indicator = document.getElementById('importProcessingIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    // ===== EDIT FUNCTIONALITY =====
    function initEditFunctionality() {
        console.log('Initializing edit functionality');

        initEditAccountManager();
        initEditCorporateCustomer();
        initEditRevenue();
    }

    function initEditAccountManager() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-account-manager')) {
                e.preventDefault();
                const button = e.target.closest('.edit-account-manager');
                const accountManagerId = button.getAttribute('data-id');

                if (!accountManagerId) {
                    showNotification('error', 'Error', 'ID Account Manager tidak valid');
                    return;
                }

                // Show loading
                const loadingOverlay = document.getElementById('edit-am-loading');
                if (loadingOverlay) loadingOverlay.style.display = 'flex';

                const modal = document.getElementById('editAccountManagerModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }

                // Fetch data
                fetch(`/account-manager/${accountManagerId}/edit`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        fillAccountManagerEditForm(data.data);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal mengambil data Account Manager');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Account Manager data:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat mengambil data');
                })
                .finally(() => {
                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                });
            }
        });

        // Setup form submission
        const editAmForm = document.getElementById('editAmForm');
        if (editAmForm) {
            editAmForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const divisiIdsInput = document.getElementById('edit_divisi_ids');
                if (!divisiIdsInput || !divisiIdsInput.value) {
                    showNotification('warning', 'Peringatan', 'Silakan pilih minimal satu divisi!');
                    return;
                }

                const accountManagerId = document.getElementById('edit_am_id').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

                const formData = new FormData(this);

                fetch(`/account-manager/${accountManagerId}`, {
                    method: 'PUT',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editAccountManagerModal'));
                        if (modal) modal.hide();

                        showNotification('success', 'Berhasil', data.message || 'Data Account Manager berhasil diperbarui');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal memperbarui data Account Manager');
                    }
                })
                .catch(error => {
                    console.error('Error updating Account Manager:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat memperbarui data');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }
    }

    function initEditCorporateCustomer() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-corporate-customer')) {
                e.preventDefault();
                const button = e.target.closest('.edit-corporate-customer');
                const customerId = button.getAttribute('data-id');

                if (!customerId) {
                    showNotification('error', 'Error', 'ID Corporate Customer tidak valid');
                    return;
                }

                // Show loading
                const loadingOverlay = document.getElementById('edit-cc-loading');
                if (loadingOverlay) loadingOverlay.style.display = 'flex';

                const modal = document.getElementById('editCorporateCustomerModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }

                // Fetch data
                fetch(`/corporate-customer/${customerId}/edit`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        fillCorporateCustomerEditForm(data.data);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal mengambil data Corporate Customer');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Corporate Customer data:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat mengambil data');
                })
                .finally(() => {
                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                });
            }
        });

        // Setup form submission
        const editCcForm = document.getElementById('editCcForm');
        if (editCcForm) {
            editCcForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const customerId = document.getElementById('edit_cc_id').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

                const formData = new FormData(this);

                fetch(`/corporate-customer/${customerId}`, {
                    method: 'PUT',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editCorporateCustomerModal'));
                        if (modal) modal.hide();

                        showNotification('success', 'Berhasil', data.message || 'Data Corporate Customer berhasil diperbarui');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal memperbarui data Corporate Customer');
                    }
                })
                .catch(error => {
                    console.error('Error updating Corporate Customer:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat memperbarui data');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }
    }

    function initEditRevenue() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-revenue')) {
                e.preventDefault();
                const button = e.target.closest('.edit-revenue');
                const revenueId = button.getAttribute('data-id');

                if (!revenueId) {
                    showNotification('error', 'Error', 'ID Revenue tidak valid');
                    return;
                }

                // Show loading
                const loadingOverlay = document.getElementById('edit-revenue-loading');
                if (loadingOverlay) loadingOverlay.style.display = 'flex';

                const modal = document.getElementById('editRevenueModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }

                // Fetch data
                fetch(`/revenue/${revenueId}/edit`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        fillRevenueEditForm(data.data);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal mengambil data Revenue');
                    }
                })
                .catch(error => {
                    console.error('Error fetching Revenue data:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat mengambil data');
                })
                .finally(() => {
                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                });
            }
        });

        // Setup form submission
        const editRevenueForm = document.getElementById('editRevenueForm');
        if (editRevenueForm) {
            editRevenueForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const revenueId = document.getElementById('edit_revenue_id').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

                const formData = new FormData(this);

                fetch(`/revenue/${revenueId}`, {
                    method: 'PUT',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editRevenueModal'));
                        if (modal) modal.hide();

                        showNotification('success', 'Berhasil', data.message || 'Data Revenue berhasil diperbarui');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', 'Error', data.message || 'Gagal memperbarui data Revenue');
                    }
                })
                .catch(error => {
                    console.error('Error updating Revenue:', error);
                    showNotification('error', 'Error', 'Terjadi kesalahan saat memperbarui data');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }
    }

    // Fill form functions
    function fillAccountManagerEditForm(data) {
        const elements = {
            'edit_am_id': data.id,
            'edit_nama': data.nama,
            'edit_nik': data.nik,
            'edit_witel_id': data.witel_id,
            'edit_regional_id': data.regional_id
        };

        // Fill basic fields
        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });

        // Handle divisi selection
        const divisiButtons = document.querySelectorAll('.edit-divisi-btn-group .divisi-btn');
        divisiButtons.forEach(button => {
            button.classList.remove('active');
            resetDivisiButtonStyle(button);
        });

        if (data.divisis && data.divisis.length > 0) {
            const divisiIds = data.divisis.map(divisi => divisi.id);
            document.getElementById('edit_divisi_ids').value = divisiIds.join(',');

            divisiButtons.forEach(button => {
                const divisiId = parseInt(button.dataset.divisiId);
                if (divisiIds.includes(divisiId)) {
                    button.classList.add('active');
                    setActiveDivisiButtonStyle(button);
                }
            });
        }
    }

    function fillCorporateCustomerEditForm(data) {
        const elements = {
            'edit_cc_id': data.id,
            'edit_nama_customer': data.nama,
            'edit_nipnas': data.nipnas
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });
    }

    function fillRevenueEditForm(data) {
        const elements = {
            'edit_revenue_id': data.id,
            'edit_account_manager': data.account_manager ? data.account_manager.nama : '',
            'edit_account_manager_id': data.account_manager_id,
            'edit_divisi_nama': data.divisi ? data.divisi.nama : 'N/A',
            'edit_divisi_id': data.divisi_id,
            'edit_corporate_customer': data.corporate_customer ? data.corporate_customer.nama : '',
            'edit_corporate_customer_id': data.corporate_customer_id,
            'edit_target_revenue': data.target_revenue,
            'edit_real_revenue': data.real_revenue,
            'edit_bulan': data.bulan
        };

        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = elements[id] || '';
        });

        // Format bulan display
        if (data.bulan) {
            const date = new Date(data.bulan + '-01');
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const monthName = monthNames[date.getMonth()];
            const year = date.getFullYear();

            const bulanDisplayElement = document.getElementById('edit_bulan_display');
            if (bulanDisplayElement) {
                bulanDisplayElement.value = `${monthName} ${year}`;
            }
        }
    }

    // ===== SEARCH AND AUTOCOMPLETE =====
    function initSearchAndAutocomplete() {
        console.log('Initializing search and autocomplete');

        initGlobalSearch();
        initAccountManagerAutocomplete();
        initCorporateCustomerAutocomplete();
    }

    function initGlobalSearch() {
        const globalSearchInput = document.getElementById('globalSearch');
        const searchButton = document.getElementById('searchButton');
        let searchTimeout;

        if (globalSearchInput) {
            globalSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();

                clearTimeout(searchTimeout);

                if (searchTerm.length === 0) {
                    hideSearchResults();
                    return;
                }

                if (searchTerm.length < 2) return;

                searchTimeout = setTimeout(() => {
                    performGlobalSearch(searchTerm);
                }, 500);
            });
        }

        if (searchButton) {
            searchButton.addEventListener('click', function() {
                const searchTerm = globalSearchInput.value.trim();
                if (searchTerm.length >= 2) {
                    performGlobalSearch(searchTerm);
                }
            });
        }
    }

    function performGlobalSearch(searchTerm) {
        const resultsContainer = document.getElementById('searchResultsContainer');
        const termDisplay = document.getElementById('search-term-display');
        const loadingDiv = document.getElementById('search-results-loading');
        const contentDiv = document.getElementById('search-results-content');
        const noResultsDiv = document.getElementById('search-no-results');

        if (termDisplay) termDisplay.textContent = searchTerm;
        if (resultsContainer) resultsContainer.style.display = 'block';
        if (loadingDiv) loadingDiv.style.display = 'block';
        if (contentDiv) contentDiv.style.display = 'none';
        if (noResultsDiv) noResultsDiv.style.display = 'none';

        fetch(`/revenue/search?search=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (loadingDiv) loadingDiv.style.display = 'none';

            if (data.success && data.stats) {
                const stats = data.stats;

                if (stats.total_results > 0) {
                    const amCount = document.getElementById('total-am-count');
                    const ccCount = document.getElementById('total-cc-count');
                    const revCount = document.getElementById('total-rev-count');

                    if (amCount) amCount.textContent = `AM: ${stats.account_managers_count || 0}`;
                    if (ccCount) ccCount.textContent = `CC: ${stats.corporate_customers_count || 0}`;
                    if (revCount) revCount.textContent = `Revenue: ${stats.revenues_count || 0}`;

                    if (contentDiv) contentDiv.style.display = 'block';
                } else {
                    if (noResultsDiv) noResultsDiv.style.display = 'block';
                }
            } else {
                if (noResultsDiv) noResultsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (noResultsDiv) noResultsDiv.style.display = 'block';
        });
    }

    function hideSearchResults() {
        const resultsContainer = document.getElementById('searchResultsContainer');
        if (resultsContainer) {
            resultsContainer.style.display = 'none';
        }
    }

    function initAccountManagerAutocomplete() {
        const amInput = document.getElementById('account_manager');
        const amIdInput = document.getElementById('account_manager_id');
        const suggestionsContainer = document.getElementById('account_manager_suggestions');
        let searchTimeout;

        if (!amInput || !amIdInput || !suggestionsContainer) return;

        amInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();

            clearTimeout(searchTimeout);

            if (searchTerm.length === 0) {
                suggestionsContainer.style.display = 'none';
                amIdInput.value = '';
                resetDivisiDropdown();
                return;
            }

            if (searchTerm.length < 2) return;

            searchTimeout = setTimeout(() => {
                searchAccountManagers(searchTerm);
            }, 300);
        });

        // Handle suggestion click
        document.addEventListener('click', function(e) {
            if (e.target.closest('#account_manager_suggestions .suggestion-item')) {
                const item = e.target.closest('.suggestion-item');
                const id = item.dataset.id;
                const name = item.dataset.name;

                amInput.value = name;
                amIdInput.value = id;
                suggestionsContainer.style.display = 'none';

                // Load divisi for selected AM
                loadAccountManagerDivisions(id);
            }
        });
    }

    function searchAccountManagers(searchTerm) {
        fetch(`/revenue/search-account-manager?search=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const suggestionsContainer = document.getElementById('account_manager_suggestions');

            if (data.success && data.data && data.data.length > 0) {
                let suggestions = '<ul class="list-unstyled mb-0">';
                data.data.forEach(am => {
                    suggestions += `<li class="suggestion-item p-2" data-id="${am.id}" data-name="${am.nama}">
                        <strong>${am.nama}</strong> <small class="text-muted">(${am.nik})</small>
                    </li>`;
                });
                suggestions += '</ul>';

                suggestionsContainer.innerHTML = suggestions;
                suggestionsContainer.style.display = 'block';
            } else {
                suggestionsContainer.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Account Manager search error:', error);
            document.getElementById('account_manager_suggestions').style.display = 'none';
        });
    }

    function loadAccountManagerDivisions(amId) {
        const divisiSelect = document.getElementById('divisi_id');
        if (!divisiSelect) return;

        divisiSelect.disabled = true;
        divisiSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`/revenue/account-manager/${amId}/divisions`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.divisis) {
                let options = '<option value="">Pilih Divisi</option>';
                data.divisis.forEach(divisi => {
                    options += `<option value="${divisi.id}">${divisi.nama}</option>`;
                });
                divisiSelect.innerHTML = options;
                divisiSelect.disabled = false;
            } else {
                divisiSelect.innerHTML = '<option value="">Tidak ada divisi</option>';
                divisiSelect.disabled = true;
            }
        })
        .catch(error => {
            console.error('Load divisions error:', error);
            divisiSelect.innerHTML = '<option value="">Error loading divisi</option>';
            divisiSelect.disabled = true;
        });
    }

    function resetDivisiDropdown() {
        const divisiSelect = document.getElementById('divisi_id');
        if (divisiSelect) {
            divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
            divisiSelect.disabled = true;
        }
    }

    function initCorporateCustomerAutocomplete() {
        const ccInput = document.getElementById('corporate_customer');
        const ccIdInput = document.getElementById('corporate_customer_id');
        const suggestionsContainer = document.getElementById('corporate_customer_suggestions');
        let searchTimeout;

        if (!ccInput || !ccIdInput || !suggestionsContainer) return;

        ccInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();

            clearTimeout(searchTimeout);

            if (searchTerm.length === 0) {
                suggestionsContainer.style.display = 'none';
                ccIdInput.value = '';
                return;
            }

            if (searchTerm.length < 2) return;

            searchTimeout = setTimeout(() => {
                searchCorporateCustomers(searchTerm);
            }, 300);
        });

        // Handle suggestion click
        document.addEventListener('click', function(e) {
            if (e.target.closest('#corporate_customer_suggestions .suggestion-item')) {
                const item = e.target.closest('.suggestion-item');
                const id = item.dataset.id;
                const name = item.dataset.name;

                ccInput.value = name;
                ccIdInput.value = id;
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    function searchCorporateCustomers(searchTerm) {
        fetch(`/revenue/search-corporate-customer?search=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const suggestionsContainer = document.getElementById('corporate_customer_suggestions');

            if (data.success && data.data && data.data.length > 0) {
                let suggestions = '<ul class="list-unstyled mb-0">';
                data.data.forEach(cc => {
                    suggestions += `<li class="suggestion-item p-2" data-id="${cc.id}" data-name="${cc.nama}">
                        <strong>${cc.nama}</strong> <small class="text-muted">(${cc.nipnas})</small>
                    </li>`;
                });
                suggestions += '</ul>';

                suggestionsContainer.innerHTML = suggestions;
                suggestionsContainer.style.display = 'block';
            } else {
                suggestionsContainer.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Corporate Customer search error:', error);
            document.getElementById('corporate_customer_suggestions').style.display = 'none';
        });
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.position-relative')) {
            const containers = document.querySelectorAll('.suggestions-container');
            containers.forEach(container => {
                container.style.display = 'none';
            });
            hideSearchResults();
        }
    });

    // ===== DIVISI BUTTON GROUP =====
    function initDivisiButtonGroup() {
        console.log('Initializing divisi button group');

        // For regular divisi buttons (add form)
        initDivisiButtons('.divisi-btn-group:not(.edit-divisi-btn-group)', 'divisi_ids');

        // For edit form divisi buttons
        initDivisiButtons('.edit-divisi-btn-group', 'edit_divisi_ids');
    }

    function initDivisiButtons(containerSelector, targetInputId) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Setup click handlers for divisi buttons
        document.addEventListener('click', function(e) {
            const button = e.target.closest(`${containerSelector} .divisi-btn`);
            if (button) {
                button.classList.toggle('active');

                if (button.classList.contains('active')) {
                    setActiveDivisiButtonStyle(button);
                } else {
                    resetDivisiButtonStyle(button);
                }

                updateDivisiIds(containerSelector, targetInputId);
            }
        });
    }

    function setActiveDivisiButtonStyle(button) {
        button.style.backgroundColor = '#1e3c72';
        button.style.color = 'white'; // ✅ FIXED: Pastikan text putih untuk kontras yang baik
        button.style.borderColor = '#1e3c72';
    }

    function resetDivisiButtonStyle(button) {
        button.style.backgroundColor = 'white';
        button.style.color = '#495057'; // Text abu-abu untuk button tidak aktif
        button.style.borderColor = '#dee2e6';
    }

    function updateDivisiIds(containerSelector, targetInputId) {
        const container = document.querySelector(containerSelector);
        const activeButtons = container.querySelectorAll('.divisi-btn.active');
        const divisiIdsInput = document.getElementById(targetInputId);

        if (divisiIdsInput) {
            const selectedIds = Array.from(activeButtons).map(btn => btn.dataset.divisiId);
            divisiIdsInput.value = selectedIds.join(',');
            console.log(`Selected divisi IDs for ${targetInputId}:`, divisiIdsInput.value);
        }
    }

    // ===== TAB FUNCTIONALITY =====
    function initTabFunctionality() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.tab-item')) {
                const tabItem = e.target.closest('.tab-item');
                const targetTab = tabItem.getAttribute('data-tab');

                // Remove active class from all tabs
                document.querySelectorAll('.tab-item').forEach(item => {
                    item.classList.remove('active');
                });

                // Add active class to clicked tab
                tabItem.classList.add('active');

                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Show the selected tab content
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }
        });
    }

    // ===== FILTER TOGGLE =====
    function initFilterToggle() {
        const filterToggle = document.getElementById('filterToggle');
        const filterArea = document.getElementById('filterArea');

        if (filterToggle && filterArea) {
            filterToggle.addEventListener('click', function() {
                if (filterArea.style.display === 'none' || filterArea.style.display === '') {
                    filterArea.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-filter me-2"></i>Tutup Filter';
                } else {
                    filterArea.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-filter me-2"></i>Filter Data';
                }
            });
        }
    }

    // ===== DELETE CONFIRMATION =====
    function initDeleteConfirmation() {
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('delete-form')) {
                e.preventDefault();

                if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.target.submit();
                }
            }
        });
    }

    // ===== NOTIFICATION SYSTEM =====
    function initNotificationSystem() {
        const notificationClose = document.getElementById('notification-close');
        if (notificationClose) {
            notificationClose.addEventListener('click', function() {
                const notification = document.getElementById('notification-container');
                if (notification) {
                    notification.classList.remove('show');
                }
            });
        }
    }

    function showNotification(type, title, message, duration = 5000) {
        const notification = document.getElementById('notification-container');
        const titleElement = document.getElementById('notification-title');
        const messageElement = document.getElementById('notification-message');

        if (!notification || !titleElement || !messageElement) {
            // Fallback to alert if notification elements don't exist
            alert(`${title}: ${message}`);
            return;
        }

        // Set content
        titleElement.textContent = title;
        messageElement.textContent = message;

        // Set type styling
        notification.className = `notification-persistent ${type} show`;

        // Auto hide for success notifications
        if (type === 'success' && duration > 0) {
            setTimeout(() => {
                notification.classList.remove('show');
            }, duration);
        }
    }

    // ===== UTILITY FUNCTIONS =====
    function initUtilityFunctions() {
        // Change per page function
        window.changePerPage = function(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        };

        // Reload revenue data function
        window.reloadRevenueData = function() {
            window.location.href = window.location.pathname + "?t=" + new Date().getTime();
        };
    }

    function hideAllLoadingOverlays() {
        const overlays = document.querySelectorAll('.modal-loading-overlay');
        overlays.forEach(overlay => {
            overlay.style.display = 'none';
        });
    }

    // ===== MODAL RESET =====
    // Reset forms when modal is closed
    document.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        const forms = modal.querySelectorAll('form');

        forms.forEach(form => {
            form.reset();
        });

        const divisiButtons = modal.querySelectorAll('.divisi-btn');
        divisiButtons.forEach(button => {
            button.classList.remove('active');
            resetDivisiButtonStyle(button);
        });

        const loadingOverlays = modal.querySelectorAll('.modal-loading-overlay');
        loadingOverlays.forEach(overlay => {
            overlay.style.display = 'none';
        });
    });

    console.log('Revenue.js fully loaded and integrated');
