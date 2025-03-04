// JavaScript for searching Account Manager and Corporate Customer

document.getElementById('account_manager').addEventListener('input', function() {
    fetch("/search-am?search=" + this.value)
        .then(response => response.json())
        .then(data => {
            let suggestionBox = document.getElementById('account_manager_suggestions');
            suggestionBox.innerHTML = ""; // Clear any previous suggestions
            if (data.length === 0) {
                suggestionBox.innerHTML = "<p>Data tidak ditemukan, silakan tambah Account Manager baru.</p>";
            }
            data.forEach(am => {
                let item = document.createElement('div');
                item.textContent = am.nama;
                item.onclick = function() {
                    document.getElementById('account_manager').value = am.nama;
                    document.getElementById('account_manager_id').value = am.id;
                    suggestionBox.innerHTML = "";
                };
                suggestionBox.appendChild(item);
            });
        });
});

document.getElementById('corporate_customer').addEventListener('input', function() {
    fetch("/search-customer?search=" + this.value)
        .then(response => response.json())
        .then(data => {
            let suggestionBox = document.getElementById('corporate_customer_suggestions');
            suggestionBox.innerHTML = ""; // Clear any previous suggestions
            if (data.length === 0) {
                suggestionBox.innerHTML = "<p>Data tidak ditemukan, silakan tambah Corporate Customer baru.</p>";
            }
            data.forEach(cust => {
                let item = document.createElement('div');
                item.textContent = cust.nama;
                item.onclick = function() {
                    document.getElementById('corporate_customer').value = cust.nama;
                    document.getElementById('corporate_customer_id').value = cust.id;
                    suggestionBox.innerHTML = "";
                };
                suggestionBox.appendChild(item);
            });
        });
});

// Optional: Clear suggestions when the user clicks outside the input field or the dropdown
document.addEventListener('click', function(event) {
    let accountManagerInput = document.getElementById('account_manager');
    let corporateCustomerInput = document.getElementById('corporate_customer');

    if (!accountManagerInput.contains(event.target)) {
        document.getElementById('account_manager_suggestions').innerHTML = '';
    }
    if (!corporateCustomerInput.contains(event.target)) {
        document.getElementById('corporate_customer_suggestions').innerHTML = '';
    }
});

function showSnackbar(message) {
    let snackbar = document.createElement('div');
    snackbar.className = 'snackbar';
    snackbar.textContent = message;
    document.body.appendChild(snackbar);
    setTimeout(() => {
        snackbar.remove();
    }, 3000);
}

document.querySelector("form").addEventListener("submit", function(event) {
    event.preventDefault();
    // Perform form submission logic here, then show the snackbar
    showSnackbar("Data Account Manager berhasil disimpan!");
});


// JavaScript untuk menangani switching antar tab
document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', function(event) {
        // Menghapus class 'active' dari semua tab dan tab content
        document.querySelectorAll('.tab-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

        // Menambahkan class 'active' pada tab yang dipilih dan content yang sesuai
        this.classList.add('active');
        const tabContent = document.getElementById(this.getAttribute('data-tab'));
        tabContent.classList.add('active');
    });
});

// Menangani penutupan pop-up dan memastikan tab bekerja dengan benar setelah dimuat
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-item')[0].classList.add('active');
    document.querySelectorAll('.tab-content')[0].classList.add('active');
});


// Ambil elemen input bulan
const bulanInput = document.getElementById('bulan');

// Tambahkan event listener ketika nilai input berubah
bulanInput.addEventListener('change', function() {
    // Ambil tanggal yang dipilih dari input (format: YYYY-MM-DD)
    const selectedDate = new Date(this.value);

    // Format bulan dan tahun dalam format Y-m (misalnya 2024-01)
    const formattedBulan = selectedDate.getFullYear() + '-' + (selectedDate.getMonth() + 1).toString().padStart(2, '0');

    // Set kembali nilai input ke format bulan dan tahun
    this.value = formattedBulan;
});

