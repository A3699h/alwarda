import '../css/navbar.scss';

// toggle sidebar in small devices
function toggleOffcanvas() {
    document.querySelector('.sidebar-offcanvas').classList.toggle('active');
}

// toggle sidebar
function toggleSidebar() {

    let body = document.querySelector('body');
    if ((!body.classList.contains('sidebar-toggle-display')) && (!body.classList.contains('sidebar-absolute'))) {
        let iconOnlyToggled = localStorage.getItem('iconOnlyToggled') === 'true';
        localStorage.setItem('iconOnlyToggled', !iconOnlyToggled);
        if (!iconOnlyToggled) {
            body.classList.add('sidebar-icon-only');
        } else {
            body.classList.remove('sidebar-icon-only');
        }
    } else {
        let sidebarToggled = localStorage.getItem('sidebarToggled') === 'true';
        localStorage.setItem('sidebarToggled', !sidebarToggled);
        if (!sidebarToggled) {
            body.classList.add('sidebar-hidden');
        } else {
            body.classList.remove('sidebar-hidden');
        }
    }
}

// toggle right sidebar
function toggleRightSidebar() {
    document.querySelector('#right-sidebar').classList.toggle('open');
}

function initSidebar() {
    let localStorageKeys = Object.keys(localStorage);

    if (!localStorageKeys.includes('iconOnlyToggled')) {
        localStorage.setItem('iconOnlyToggled', false);
    }
    if (!localStorageKeys.includes('sidebarToggled')) {
        localStorage.setItem('sidebarToggled', false);
    }

    let body = document.querySelector('body');
    if ((!body.classList.contains('sidebar-toggle-display')) && (!body.classList.contains('sidebar-absolute'))) {
        let iconOnlyToggled = localStorage.getItem('iconOnlyToggled') === 'true';
        if (iconOnlyToggled) {
            body.classList.add('sidebar-icon-only');
        } else {
            body.classList.remove('sidebar-icon-only');
        }
    } else {
        let sidebarToggled = localStorage.getItem('sidebarToggled') === 'true';
        if (sidebarToggled) {
            body.classList.add('sidebar-hidden');
        } else {
            body.classList.remove('sidebar-hidden');
        }
    }
}

$(function () {

    initSidebar();
    $('#navbar_toggler').on('click', function () {
        toggleSidebar();
    });
    $('#right_sidebar_toggler').on('click', function () {
        toggleRightSidebar();
    });
    $('#off_canvas_toggler').on('click', function () {
        toggleOffcanvas();
    });
});
