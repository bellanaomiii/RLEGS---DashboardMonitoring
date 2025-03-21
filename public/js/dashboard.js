// Unified dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi snackbar
    if (!document.getElementById('snackbar')) {
        const snackbar = document.createElement('div');
        snackbar.id = 'snackbar';
        document.body.appendChild(snackbar);
    }

    // ====== Month Picker Implementation ======
    const monthNames = [
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
        let selectedMonth = new Date().getMonth();
        let selectedYear = currentYear;
        let isMonthPickerOpen = false;

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
        function renderMonthGrid() {
            if (!monthGrid) return;

            monthGrid.innerHTML = '';
            monthNames.forEach((month, index) => {
                const monthItem = document.createElement('div');
                monthItem.className = 'month-item';
                if (selectedMonth === index && selectedYear === currentYear) {
                    monthItem.classList.add('selected');
                    monthItem.classList.add('active');
                }

                monthItem.textContent = month;
                monthItem.dataset.month = index;

                monthItem.addEventListener('click', function() {
                    // Remove active/selected class from all month items
                    document.querySelectorAll('.month-item').forEach(item => {
                        item.classList.remove('selected');
                        item.classList.remove('active');
                    });

                    // Add active/selected class to selected month
                    this.classList.add('selected');
                    this.classList.add('active');

                    // Update selected month
                    selectedMonth = index;
                });

                monthGrid.appendChild(monthItem);
            });
        }

        // Show month picker when input is clicked
        monthYearInput.addEventListener('click', function() {
            monthPicker.style.display = 'block';
            monthPicker.classList.add('active');
            isMonthPickerOpen = true;
            renderMonthGrid();
        });

        // Year navigation
        if (prevYearButton) {
            prevYearButton.addEventListener('click', function(e) {
                e.preventDefault();
                currentYear--;
                currentYearElement.textContent = currentYear;
                renderMonthGrid();
            });
        }

        if (nextYearButton) {
            nextYearButton.addEventListener('click', function(e) {
                e.preventDefault();
                currentYear++;
                currentYearElement.textContent = currentYear;
                renderMonthGrid();
            });
        }

        // Cancel month selection
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                monthPicker.style.display = 'none';
                monthPicker.classList.remove('active');
                isMonthPickerOpen = false;
            });
        }

        // Apply month selection
        if (applyButton) {
            applyButton.addEventListener('click', function() {
                const formattedDate = `${monthNames[selectedMonth]} ${currentYear}`;
                monthYearInput.value = formattedDate;

                // Set hidden inputs
                if (hiddenMonthInput) hiddenMonthInput.value = monthCodes[selectedMonth];
                if (hiddenYearInput) hiddenYearInput.value = currentYear;
                if (hiddenBulanInput) hiddenBulanInput.value = `${currentYear}-${monthCodes[selectedMonth]}`;

                // Save selected values
                selectedYear = currentYear;

                monthPicker.style.display = 'none';
                monthPicker.classList.remove('active');
                isMonthPickerOpen = false;
            });
        }

        // Close month picker when clicking outside
        document.addEventListener('click', function(event) {
            if (monthPicker && isMonthPickerOpen && !monthPicker.contains(event.target) && event.target !== monthYearInput) {
                monthPicker.style.display = 'none';
                monthPicker.classList.remove('active');
                isMonthPickerOpen = false;
            }
        });

        // Initialize with current month and year
        const now = new Date();
        selectedMonth = now.getMonth();
        selectedYear = now.getFullYear();
        currentYear = now.getFullYear();

        if (currentYearElement) {
            currentYearElement.textContent = currentYear;
        }

        // Set initial values for hidden inputs
        if (monthYearInput) monthYearInput.value = `${monthNames[selectedMonth]} ${currentYear}`;
        if (hiddenMonthInput) hiddenMonthInput.value = monthCodes[selectedMonth];
        if (hiddenYearInput) hiddenYearInput.value = currentYear;
        if (hiddenBulanInput) hiddenBulanInput.value = `${currentYear}-${monthCodes[selectedMonth]}`;

        // Generate month grid on initial load
        renderMonthGrid();
    }

    // ====== Account Manager search functionality ======
    const accountManagerInput = document.getElementById('account_manager');
    const accountManagerIdInput = document.getElementById('account_manager_id');
    const accountManagerSuggestions = document.getElementById('account_manager_suggestions');

    if (accountManagerInput) {
        accountManagerInput.addEventListener('input', function() {
            const search = this.value.trim();

            if (search.length < 2) {
                if (accountManagerSuggestions) {
                    accountManagerSuggestions.innerHTML = '';
                    accountManagerSuggestions.style.display = 'none';
                }
                return;
            }

            fetch('/search-am?search=' + encodeURIComponent(search))
                .then(response => response.json())
                .then(data => {
                    if (!accountManagerSuggestions) return;

                    accountManagerSuggestions.innerHTML = '';

                    if (data.length === 0) {
                        const noResult = document.createElement('div');
                        noResult.className = 'suggestion-item';
                        noResult.textContent = 'Tidak ada hasil yang ditemukan';
                        accountManagerSuggestions.appendChild(noResult);
                    } else {
                        data.forEach(am => {
                            const item = document.createElement('div');
                            item.className = 'suggestion-item';
                            item.textContent = `${am.nama} - ${am.nik || 'NIK tidak tersedia'}`;

                            item.addEventListener('click', () => {
                                accountManagerInput.value = am.nama;
                                if (accountManagerIdInput) {
                                    accountManagerIdInput.value = am.id;
                                }
                                accountManagerSuggestions.style.display = 'none';
                            });

                            accountManagerSuggestions.appendChild(item);
                        });
                    }

                    accountManagerSuggestions.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching account managers:', error);

                    if (accountManagerSuggestions) {
                        accountManagerSuggestions.innerHTML = '';
                        const errorItem = document.createElement('div');
                        errorItem.className = 'suggestion-item text-danger';
                        errorItem.textContent = 'Error: Tidak dapat memuat data';
                        accountManagerSuggestions.appendChild(errorItem);
                        accountManagerSuggestions.style.display = 'block';
                    }
                });
        });
    }

    // ====== Corporate Customer search functionality ======
    const corporateCustomerInput = document.getElementById('corporate_customer');
    const corporateCustomerIdInput = document.getElementById('corporate_customer_id');
    const corporateCustomerSuggestions = document.getElementById('corporate_customer_suggestions');

    if (corporateCustomerInput) {
        corporateCustomerInput.addEventListener('input', function() {
            const search = this.value.trim();

            if (search.length < 2) {
                if (corporateCustomerSuggestions) {
                    corporateCustomerSuggestions.innerHTML = '';
                    corporateCustomerSuggestions.style.display = 'none';
                }
                return;
            }

            fetch('/search-customer?search=' + encodeURIComponent(search))
                .then(response => response.json())
                .then(data => {
                    if (!corporateCustomerSuggestions) return;

                    corporateCustomerSuggestions.innerHTML = '';

                    if (data.length === 0) {
                        const noResult = document.createElement('div');
                        noResult.className = 'suggestion-item';
                        noResult.textContent = 'Tidak ada hasil yang ditemukan';
                        corporateCustomerSuggestions.appendChild(noResult);
                    } else {
                        data.forEach(cc => {
                            const item = document.createElement('div');
                            item.className = 'suggestion-item';
                            item.textContent = `${cc.nama} - NIPNAS: ${cc.nipnas || 'Tidak tersedia'}`;

                            item.addEventListener('click', () => {
                                corporateCustomerInput.value = cc.nama;
                                if (corporateCustomerIdInput) {
                                    corporateCustomerIdInput.value = cc.id;
                                }
                                corporateCustomerSuggestions.style.display = 'none';
                            });

                            corporateCustomerSuggestions.appendChild(item);
                        });
                    }

                    corporateCustomerSuggestions.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching corporate customers:', error);

                    if (corporateCustomerSuggestions) {
                        corporateCustomerSuggestions.innerHTML = '';
                        const errorItem = document.createElement('div');
                        errorItem.className = 'suggestion-item text-danger';
                        errorItem.textContent = 'Error: Tidak dapat memuat data';
                        corporateCustomerSuggestions.appendChild(errorItem);
                        corporateCustomerSuggestions.style.display = 'block';
                    }
                });
        });
    }

    // ====== Clear suggestions when clicking outside ======
    document.addEventListener('click', function(event) {
        const amSuggestions = document.getElementById('account_manager_suggestions');
        const ccSuggestions = document.getElementById('corporate_customer_suggestions');

        if (accountManagerInput && amSuggestions && !accountManagerInput.contains(event.target) && !amSuggestions.contains(event.target)) {
            amSuggestions.style.display = 'none';
        }

        if (corporateCustomerInput && ccSuggestions && !corporateCustomerInput.contains(event.target) && !ccSuggestions.contains(event.target)) {
            ccSuggestions.style.display = 'none';
        }
    });

    // ====== Tab switching functionality ======
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', function() {
            // Find the closest tab container
            const tabContainer = this.closest('.tab-menu-container');
            const parentContainer = tabContainer.parentElement;
            let contentContainer;

            // Check if parent is modal body, or use parent container
            if (parentContainer.classList.contains('modal-body')) {
                contentContainer = parentContainer;
            } else {
                contentContainer = tabContainer.parentElement;
            }

            // Remove active class from all tabs in this container
            tabContainer.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all tab contents in this container
            contentContainer.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Show the selected tab content
            const targetId = this.getAttribute('data-tab');
            const targetContent = contentContainer.querySelector(`#${targetId}`);
            if (targetContent) targetContent.classList.add('active');
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

    // Show snackbar if URL has success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    if (successMsg) {
        window.showSnackbar(decodeURIComponent(successMsg));
    }

    // ====== Toggle filter area ==========
    const filterToggle = document.getElementById('filterToggle');
    const filterArea = document.getElementById('filterArea');

    if (filterToggle && filterArea) {
        filterToggle.addEventListener('click', function() {
            if (filterArea.style.display === 'none') {
                filterArea.style.display = 'block';
                filterToggle.classList.add('active');
            } else {
                filterArea.style.display = 'none';
                filterToggle.classList.remove('active');
            }
        });
    }

    // ====== AJAX Form submissions ======
    const setupFormSubmission = (formId, actionOnSuccess) => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSnackbar(data.message || 'Data berhasil disimpan!', 'success');
                        if (actionOnSuccess) actionOnSuccess(this, data);
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
    };

    // Setup form submissions
    setupFormSubmission('revenueForm', (form, data) => {
        setTimeout(() => window.location.reload(), 1500);
    });

    setupFormSubmission('amForm', (form, data) => {
        form.reset();
        document.querySelector('#addAccountManagerModal .btn-close')?.click();
        setTimeout(() => window.location.reload(), 1500);
    });

    setupFormSubmission('amImportForm', (form, data) => {
        form.reset();
        document.querySelector('#addAccountManagerModal .btn-close')?.click();
        setTimeout(() => window.location.reload(), 1500);
    });

    setupFormSubmission('ccForm', (form, data) => {
        form.reset();
        document.querySelector('#addCorporateCustomerModal .btn-close')?.click();
        setTimeout(() => window.location.reload(), 1500);
    });

    setupFormSubmission('ccImportForm', (form, data) => {
        form.reset();
        document.querySelector('#addCorporateCustomerModal .btn-close')?.click();
        setTimeout(() => window.location.reload(), 1500);
    });

    setupFormSubmission('revenueImportForm', (form, data) => {
        form.reset();
        document.querySelector('#importRevenueModal .btn-close')?.click();
        setTimeout(() => window.location.reload(), 1500);
    });

    // ====== Filter Revenue by Year (for dashboard) ======
    const yearFilter = document.getElementById('yearFilter');
    const applyYearFilter = document.getElementById('applyYearFilter');

    if (yearFilter && applyYearFilter) {
        // Event handler untuk tombol filter tahun
        applyYearFilter.addEventListener('click', function() {
            const year = yearFilter.value;
            if (year && year >= 2000 && year <= 2100) {
                filterRevenueByYear(year);
            }
        });

        // Mendukung tombol Enter pada input filter tahun
        yearFilter.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                applyYearFilter.click();
            }
        });
    }

    // Fungsi untuk memfilter revenue berdasarkan tahun (di dashboard)
    function filterRevenueByYear(year) {
        const monthlyRevenueTable = document.getElementById('monthlyRevenueTable');
        if (!monthlyRevenueTable) return;

        // Tampilkan loading state
        monthlyRevenueTable.querySelector('tbody').innerHTML = '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin fs-4"></i> Loading data...</td></tr>';

        fetch('/dashboard/revenues?year=' + year)
            .then(response => response.json())
            .then(response => {
                updateRevenueTable(response.data, response.year);
            })
            .catch(error => {
                // Tampilkan pesan error
                monthlyRevenueTable.querySelector('tbody').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fs-4 mb-2"></i><br>Gagal memuat data. Silakan coba lagi.</td></tr>';
                console.error('Error fetching revenue data:', error);
            });
    }

    // Fungsi untuk mengupdate tabel revenue
    function updateRevenueTable(data, year) {
        const monthlyRevenueTable = document.getElementById('monthlyRevenueTable');
        if (!monthlyRevenueTable) return;

        const months = {
            1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
            5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
            9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
        };

        let tableHtml = '';

        if (data.length > 0) {
            data.forEach(function(revenue) {
                const achievement = revenue.target > 0
                    ? Math.round((revenue.realisasi / revenue.target) * 100 * 10) / 10
                    : 0;

                const statusClass = achievement >= 100
                    ? 'bg-success-soft'
                    : (achievement >= 80 ? 'bg-warning-soft' : 'bg-danger-soft');

                const statusIcon = achievement >= 100
                    ? 'check-circle'
                    : (achievement >= 80 ? 'clock' : 'times-circle');

                const iconColorClass = achievement >= 100
                    ? 'text-success'
                    : (achievement >= 80 ? 'text-warning' : 'text-danger');

                tableHtml += `
                <tr>
                    <td>${months[revenue.month] || 'Unknown'}</td>
                    <td class="text-end">Rp ${formatNumber(revenue.target)}</td>
                    <td class="text-end">Rp ${formatNumber(revenue.realisasi)}</td>
                    <td class="text-end">
                        <span class="status-badge ${statusClass}">${achievement}%</span>
                    </td>
                    <td class="text-center">
                        <i class="fas fa-${statusIcon} ${iconColorClass}"></i>
                    </td>
                </tr>
                `;
            });
        } else {
            tableHtml = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-chart-bar fs-4 d-block mb-2"></i>
                    Tidak ada data revenue untuk tahun ${year}
                </td>
            </tr>
            `;
        }

        monthlyRevenueTable.querySelector('tbody').innerHTML = tableHtml;

        // Update judul filter jika ada
        const yearFilterTitle = document.getElementById('yearFilterTitle');
        if (yearFilterTitle) {
            yearFilterTitle.textContent = yearFilterTitle.textContent.replace(/\(\d+\)/, `(${year})`);
        }
    }

    // Helper function untuk format angka
    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
});