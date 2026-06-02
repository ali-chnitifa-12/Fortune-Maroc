function openMobileSidebar() {
    const sidebar = document.getElementById('erp-sidebar');
    const overlay = document.getElementById('erp-mobile-overlay');

    if (sidebar) {
        sidebar.classList.add('open');
    }

    if (overlay) {
        overlay.classList.add('show');
    }

    document.body.style.overflow = 'hidden';
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('erp-sidebar');
    const overlay = document.getElementById('erp-mobile-overlay');

    if (sidebar) {
        sidebar.classList.remove('open');
    }

    if (overlay) {
        overlay.classList.remove('show');
    }

    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeMobileSidebar();
    }
});

document.addEventListener('click', function (event) {
    const link = event.target.closest('.erp-menu-link');

    if (link && window.innerWidth <= 1024) {
        closeMobileSidebar();
    }
});

window.addEventListener('resize', function () {
    if (window.innerWidth > 1024) {
        closeMobileSidebar();
    }
});