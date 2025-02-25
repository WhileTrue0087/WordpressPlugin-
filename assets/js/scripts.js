document.addEventListener("DOMContentLoaded", function() {
    const tabLinks = document.querySelectorAll(".grd-tab-link");
    const tabContents = document.querySelectorAll(".grd-tab-content");

    tabLinks.forEach(link => {
        link.addEventListener("click", function() {
            const tabID = this.getAttribute("data-tab");

            // حذف کلاس active از همه تب‌ها
            tabLinks.forEach(l => l.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));

            // افزودن کلاس active به تب انتخاب‌شده
            this.classList.add("active");
            document.getElementById(tabID).classList.add("active");
        });
    });
});
