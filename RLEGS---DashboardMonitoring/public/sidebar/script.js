const hamburger = document.querySelector("#toggle-btn");

hamburger.addEventListener("click", function(){
    document.querySelector("#sidebar").classList.toggle("expand")
});

    document.addEventListener("DOMContentLoaded", function() {
        // Mencegah dropdown tertutup saat klik dalam dropdown-menu
        document.getElementById("filterDropdown").addEventListener("click", function(event) {
            event.stopPropagation();
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        const btnLeft = document.querySelector(".left-btn");
        const btnRight = document.querySelector(".right-btn");
        const tabMenu = document.querySelector(".tab-menu");

        if (!btnLeft || !btnRight || !tabMenu) {
            console.error("Elemen navigasi tab tidak ditemukan.");
            return;
        }

        // Fungsi untuk mengontrol visibilitas tombol navigasi
        const IconVisibility = () => {
            let scrollLeftValue = Math.ceil(tabMenu.scrollLeft);
            let scrollableWidth = tabMenu.scrollWidth - tabMenu.clientWidth;

            btnLeft.style.display = scrollLeftValue > 0 ? "block" : "none";
            btnRight.style.display = scrollableWidth > scrollLeftValue ? "block" : "none";
        };

        // Event listener untuk tombol navigasi
        btnRight.addEventListener("click", () => {
            tabMenu.scrollLeft += 150;
            setTimeout(IconVisibility, 100); // Delay agar scroll selesai sebelum update tombol
        });

        btnLeft.addEventListener("click", () => {
            tabMenu.scrollLeft -= 150;
            setTimeout(IconVisibility, 100);
        });

        // Tambahkan event listener untuk mendeteksi perubahan scroll secara manual
        tabMenu.addEventListener("scroll", IconVisibility);

        // Panggil IconVisibility() saat halaman pertama kali dimuat
        IconVisibility();
    });

let activeDrag = false;

tabMenu.addEventListener("mousemove", (drag) => {
    if(!activeDrag) return;
    tabMenu.scrollLeft -= drag.movementX;
});

tabMenu.addEventListener("mousedown", () => {
    activeDrag = true;
});




// Add this JavaScript code to make the filter dropdown work correctly
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    var filterTabs = document.querySelectorAll('#filterTabs .nav-link');

    filterTabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            // Remove active class from all tabs
            filterTabs.forEach(function(t) {
                t.classList.remove('active');
            });

            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all tab panes
            var tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });

            // Show the corresponding tab pane
            var targetId = this.getAttribute('data-bs-target').substring(1);
            var targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });

    // Handle dropdown toggle
    var filterDropdownBtn = document.querySelector('[data-bs-toggle="dropdown"]');
    var filterDropdown = document.getElementById('filterDropdown');

    if (filterDropdownBtn && filterDropdown) {
        filterDropdownBtn.addEventListener('click', function() {
            filterDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!filterDropdownBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterDropdown.classList.remove('show');
            }
        });
    }

    // Add missing tab pane for Periode
    if (!document.getElementById('periode')) {
        var periodeTab = document.createElement('div');
        periodeTab.id = 'periode';
        periodeTab.className = 'tab-pane fade';
        periodeTab.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="periode1" value="2023">
                <label class="form-check-label" for="periode1">2023</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="periode2" value="2024">
                <label class="form-check-label" for="periode2">2024</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="periode3" value="2025">
                <label class="form-check-label" for="periode3">2025</label>
            </div>
        `;

        document.querySelector('.tab-content').appendChild(periodeTab);
    }
});
