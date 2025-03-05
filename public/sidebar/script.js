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