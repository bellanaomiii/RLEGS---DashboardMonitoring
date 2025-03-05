// JavaScript for dashboard functionality

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi snackbar jika belum ada
    if (!document.getElementById('snackbar')) {
        const snackbar = document.createElement('div');
        snackbar.id = 'snackbar';
        document.body.appendChild(snackbar);
    }

    // ====== Month Picker Implementation ======
    const months = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    const monthCodes = [
        "01", "02", "03", "04", "05", "06",
        "07", "08", "09", "10", "11", "12"
    ];

    // Get elements
    const monthYearInput = document.getElementById('month_year_picker');
    const hiddenMonthInput = document.getElementById('bulan_month');
    const hiddenYearInput = document.getElementById('bulan_year');
    const hiddenBulanInput = document.getElementById('bulan');
    const monthPicker = document.getElementById('month_picker');

    if (monthYearInput && monthPicker) {
        // Set current year
        let currentYear = new Date().getFullYear();
        let selectedMonth = null;
        let selectedYear = currentYear;
        let selectedMonthIndex = null;

        // Initialize month grid
        const monthGrid = document.getElementById('month_grid');
        const currentYearElement = document.getElementById('current_year');
        const prevYearButton = document.getElementById('prev_year');
        const nextYearButton = document.getElementById('next_year');
        const cancelButton = document.getElementById('cancel_month');
        const applyButton = document.getElementById('apply_month');

        // Set current year on initial load
        if (currentYearElement) {
            currentYearElement.textContent = currentYear;
        }

        // Generate month grid
        function generateMonthGrid() {
            if (!monthGrid) return;

            monthGrid.innerHTML = '';
            months.forEach((month, index) => {
                const monthItem = document.createElement('div');
                monthItem.className = 'month-item';
                if (selectedMonthIndex === index && selectedYear === currentYear) {
                    monthItem.classList.add('active');
                }

                monthItem.textContent = month;
                monthItem.dataset.month = index;

                monthItem.addEventListener('click', function() {
                    // Remove active class from all month items
                    document.querySelectorAll('.month-item').forEach(item => {
                        item.classList.remove('active');
                    });

                    // Add active class to selected month
                    this.classList.add('active');

                    // Update selected month
                    selectedMonth = month;
                    selectedMonthIndex = parseInt(this.dataset.month);
                });

                monthGrid.appendChild(monthItem);
            });
        }

        // Navigate to previous year
        if (prevYearButton) {
            prevYearButton.addEventListener('click', function() {
                currentYear--;
                currentYearElement.textContent = currentYear;
                generateMonthGrid();
            });
        }

        // Navigate to next year
        if (nextYearButton) {
            nextYearButton.addEventListener('click', function() {
                currentYear++;
                currentYearElement.textContent = currentYear;
                generateMonthGrid();
            });
        }

        // Show month picker when input is clicked
        monthYearInput.addEventListener('click', function() {
            monthPicker.classList.add('active');
            generateMonthGrid();
        });

        // Cancel month selection
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                monthPicker.classList.remove('active');
            });
        }

        // Apply month selection
        if (applyButton) {
            applyButton.addEventListener('click', function() {
                if (selectedMonth !== null && selectedMonthIndex !== null) {
                    // Format display value
                    monthYearInput.value = `${selectedMonth} ${currentYear}`;

                    // Set hidden inputs
                    if (hiddenMonthInput) hiddenMonthInput.value = monthCodes[selectedMonthIndex];
                    if (hiddenYearInput) hiddenYearInput.value = currentYear;
                    if (hiddenBulanInput) hiddenBulanInput.value = `${currentYear}-${monthCodes[selectedMonthIndex]}`;

                    // Save selected values
                    selectedYear = currentYear;
                }

                monthPicker.classList.remove('active');
            });
        }

        // Close month picker when clicking outside
        document.addEventListener('click', function(event) {
            if (monthPicker && !monthPicker.contains(event.target) && event.target !== monthYearInput) {
                monthPicker.classList.remove('active');
            }
        });

        // Initialize with current month and year
        const now = new Date();
        selectedMonth = months[now.getMonth()];
        selectedMonthIndex = now.getMonth();
        selectedYear = now.getFullYear();
        currentYear = now.getFullYear();

        if (currentYearElement) {
            currentYearElement.textContent = currentYear;
        }

        // Set initial values for hidden inputs
        if (monthYearInput) monthYearInput.value = `${selectedMonth} ${currentYear}`;
        if (hiddenMonthInput) hiddenMonthInput.value = monthCodes[selectedMonthIndex];
        if (hiddenYearInput) hiddenYearInput.value = currentYear;
        if (hiddenBulanInput) hiddenBulanInput.value = `${currentYear}-${monthCodes[selectedMonthIndex]}`;

        // Generate month grid on initial load
        generateMonthGrid();
    }

    // ====== Account Manager search functionality ======
    const accountManagerInput = document.getElementById('account_manager');
    if (accountManagerInput) {
        accountManagerInput.addEventListener('input', function() {
            fetch("/search-am?search=" + this.value)
                .then(response => response.json())
                .then(data => {
                    let suggestionBox = document.getElementById('account_manager_suggestions');
                    suggestionBox.innerHTML = ""; // Clear any previous suggestions
                    if (data.length === 0) {
                        suggestionBox.innerHTML = "<p class='p-3'>Data tidak ditemukan, silakan tambah Account Manager baru.</p>";
                    }
                    data.forEach(am => {
                        let item = document.createElement('div');
                        item.textContent = am.nama;
                        item.className = 'hover:bg-gray-100';
                        item.onclick = function() {
                            document.getElementById('account_manager').value = am.nama;
                            document.getElementById('account_manager_id').value = am.id;
                            suggestionBox.innerHTML = "";
                        };
                        suggestionBox.appendChild(item);
                    });
                });
        });
    }

    // ====== Corporate Customer search functionality ======
    const corporateCustomerInput = document.getElementById('corporate_customer');
    if (corporateCustomerInput) {
        corporateCustomerInput.addEventListener('input', function() {
            fetch("/search-customer?search=" + this.value)
                .then(response => response.json())
                .then(data => {
                    let suggestionBox = document.getElementById('corporate_customer_suggestions');
                    suggestionBox.innerHTML = ""; // Clear any previous suggestions
                    if (data.length === 0) {
                        suggestionBox.innerHTML = "<p class='p-3'>Data tidak ditemukan, silakan tambah Corporate Customer baru.</p>";
                    }
                    data.forEach(cust => {
                        let item = document.createElement('div');
                        item.textContent = cust.nama;
                        item.className = 'hover:bg-gray-100';
                        item.onclick = function() {
                            document.getElementById('corporate_customer').value = cust.nama;
                            document.getElementById('corporate_customer_id').value = cust.id;
                            suggestionBox.innerHTML = "";
                        };
                        suggestionBox.appendChild(item);
                    });
                });
        });
    }

    // ====== Clear suggestions when clicking outside ======
    document.addEventListener('click', function(event) {
        const amSuggestions = document.getElementById('account_manager_suggestions');
        const ccSuggestions = document.getElementById('corporate_customer_suggestions');
        const accountManagerInput = document.getElementById('account_manager');
        const corporateCustomerInput = document.getElementById('corporate_customer');

        if (accountManagerInput && amSuggestions && !accountManagerInput.contains(event.target) && !amSuggestions.contains(event.target)) {
            amSuggestions.innerHTML = '';
        }

        if (corporateCustomerInput && ccSuggestions && !corporateCustomerInput.contains(event.target) && !ccSuggestions.contains(event.target)) {
            ccSuggestions.innerHTML = '';
        }
    });

    // ====== Tab switching functionality ======
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', function() {
            // Find the closest tab container
            const tabContainer = this.closest('.tab-menu-container');
            const tabContents = tabContainer.parentElement.querySelectorAll('.tab-content');

            // Remove active class from all tabs and tab contents
            tabContainer.querySelectorAll('.tab-item').forEach(item => item.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to selected tab
            this.classList.add('active');

            // Get tab content id and activate it
            const tabContentId = this.getAttribute('data-tab');
            const tabContent = document.getElementById(tabContentId);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });

    // ====== Delete confirmation ======
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                event.preventDefault();
            }
        });
    });

    // ====== Function to show snackbar notifications ======
    window.showSnackbar = function(message, type = 'info') {
        const snackbar = document.getElementById('snackbar');
        if (!snackbar) return;

        snackbar.textContent = message;

        // Remove existing classes
        snackbar.classList.remove('hidden', 'show', 'success', 'error', 'info');

        // Add appropriate classes
        snackbar.classList.add('show', type);

        // Hide the snackbar after 3 seconds
        setTimeout(() => {
            snackbar.classList.remove('show');
            snackbar.classList.add('hidden');
        }, 3000);
    };

    // ====== Helper function to get CSRF token ======
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            : '';
    }

    // ====== Revenue form submission ======
    const revenueForm = document.getElementById('revenueForm');
    if (revenueForm) {
        revenueForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Check if account_manager_id and corporate_customer_id are set
            const amId = document.getElementById('account_manager_id').value;
            const ccId = document.getElementById('corporate_customer_id').value;

            if (!amId) {
                showSnackbar('Silakan pilih Account Manager dari daftar yang tersedia.', 'error');
                return;
            }

            if (!ccId) {
                showSnackbar('Silakan pilih Corporate Customer dari daftar yang tersedia.', 'error');
                return;
            }

            // Prepare form data
            const formData = new FormData(this);

            // Add bulan value from month and year inputs
            const monthValue = document.getElementById('bulan_month').value;
            const yearValue = document.getElementById('bulan_year').value;

            if (monthValue && yearValue) {
                formData.set('bulan', `${yearValue}-${monthValue}`);
            }

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSnackbar(data.message || 'Data revenue berhasil disimpan!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showSnackbar(data.message || 'Gagal menyimpan data.', 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat menyimpan data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Account Manager form submission (Manual) ======
    const amForm = document.getElementById('amForm');
    if (amForm) {
        amForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSnackbar(data.message || 'Account Manager berhasil ditambahkan!', 'success');
                    this.reset();
                    document.querySelector('#addAccountManagerModal .btn-close').click();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showSnackbar(data.message || 'Gagal menambahkan Account Manager.', 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat menyimpan data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Account Manager Excel Import ======
    const amImportForm = document.getElementById('amImportForm');
    if (amImportForm) {
        amImportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message || 'Data Account Manager berhasil diimpor!';
                    if (data.data) {
                        const importResults = data.data;
                        message = `${importResults.imported} data Account Manager berhasil diimpor.`;
                        if (importResults.duplicates > 0) {
                            message += ` ${importResults.duplicates} data duplikat dilewati.`;
                        }
                        if (importResults.errors > 0) {
                            message += ` ${importResults.errors} data gagal diimpor.`;
                        }
                    }
                    showSnackbar(message, 'success');
                    this.reset();
                    document.querySelector('#addAccountManagerModal .btn-close').click();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    let errorMessage = data.message || 'Gagal mengimpor data.';
                    if (data.errors && Array.isArray(data.errors)) {
                        // Jika ada detail error yang dikirim dari server
                        errorMessage += ' Detail: ' + data.errors.join('; ');
                    }
                    showSnackbar(errorMessage, 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat mengimpor data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Corporate Customer form submission (Manual) ======
    const ccForm = document.getElementById('ccForm');
    if (ccForm) {
        ccForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSnackbar(data.message || 'Corporate Customer berhasil ditambahkan!', 'success');
                    this.reset();
                    document.querySelector('#addCorporateCustomerModal .btn-close').click();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showSnackbar(data.message || 'Gagal menambahkan Corporate Customer.', 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat menyimpan data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Corporate Customer Excel Import ======
    const ccImportForm = document.getElementById('ccImportForm');
    if (ccImportForm) {
        ccImportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message || 'Data Corporate Customer berhasil diimpor!';
                    if (data.data) {
                        const importResults = data.data;
                        message = `${importResults.imported} data Corporate Customer berhasil diimpor.`;
                        if (importResults.duplicates > 0) {
                            message += ` ${importResults.duplicates} data duplikat dilewati.`;
                        }
                        if (importResults.errors > 0) {
                            message += ` ${importResults.errors} data gagal diimpor.`;
                        }
                    }
                    showSnackbar(message, 'success');
                    this.reset();
                    document.querySelector('#addCorporateCustomerModal .btn-close').click();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    let errorMessage = data.message || 'Gagal mengimpor data.';
                    if (data.errors && Array.isArray(data.errors)) {
                        // Jika ada detail error yang dikirim dari server
                        errorMessage += ' Detail: ' + data.errors.join('; ');
                    }
                    showSnackbar(errorMessage, 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat mengimpor data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Revenue Excel Import ======
    const revenueImportForm = document.getElementById('revenueImportForm');
    if (revenueImportForm) {
        revenueImportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message || 'Data Revenue berhasil diimpor!';
                    if (data.data) {
                        const importResults = data.data;
                        message = `${importResults.imported} data Revenue berhasil diimpor.`;
                        if (importResults.duplicates > 0) {
                            message += ` ${importResults.duplicates} data duplikat dilewati.`;
                        }
                        if (importResults.errors > 0) {
                            message += ` ${importResults.errors} data gagal diimpor.`;
                        }

                        // If there are error details, create a more detailed message
                        if (data.error_details && data.error_details.length > 0) {
                            const errorList = data.error_details.join('\n');
                            console.log('Import errors:', errorList);

                            // Create an error summary message
                            let errorSummary = `${importResults.errors} data gagal diimpor:`;
                            if (data.error_details.length > 3) {
                                // Only show the first 3 errors in the snackbar
                                for (let i = 0; i < 3; i++) {
                                    errorSummary += `\n- ${data.error_details[i]}`;
                                }
                                errorSummary += `\n... dan ${data.error_details.length - 3} error lainnya.`;
                            } else {
                                data.error_details.forEach(error => {
                                    errorSummary += `\n- ${error}`;
                                });
                            }

                            // Display a detailed error log in console
                            console.log(errorSummary);

                            // Show a dialog with detailed errors
                            setTimeout(() => {
                                alert('Detail error impor:\n\n' + errorSummary);
                            }, 500);
                        }
                    }

                    showSnackbar(message, 'success');
                    this.reset();
                    document.querySelector('#importRevenueModal .btn-close').click();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    let errorMessage = data.message || 'Gagal mengimpor data.';
                    if (data.errors && Array.isArray(data.errors)) {
                        // Jika ada detail error yang dikirim dari server
                        errorMessage += ' Detail: ' + data.errors.join('; ');
                    }
                    showSnackbar(errorMessage, 'error');
                }
            })
            .catch(error => {
                showSnackbar('Terjadi kesalahan saat mengimpor data.', 'error');
                console.error('Error:', error);
            });
        });
    }

    // ====== Add icons to edit and delete buttons ======
    document.querySelectorAll('a:contains("Edit")').forEach(link => {
        const originalText = link.innerHTML;
        link.innerHTML = `<i class="fas fa-edit"></i> ${originalText}`;
        link.classList.add('edit-btn', 'action-btn');
    });

    document.querySelectorAll('button:contains("Hapus")').forEach(button => {
        const originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-trash"></i> ${originalText}`;
        button.classList.add('delete-btn', 'action-btn');
    });

    // Polyfill for :contains selector
    jQuery.expr[':'].contains = function(a, i, m) {
        return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };
});
