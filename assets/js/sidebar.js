import '../css/sidebar.scss';

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


$(function () {
    Routing.setRoutingData(routes);
    $.post(
        Routing.generate('check_new_messages')
    ).then((res) => {
        let $messagesLink = $('#messages-link');
        if (res) {
            $messagesLink.addClass('text-danger');
        } else {
            $messagesLink.removeClass('test-danger');
        }
    });

    const body = document.querySelector('body');

    // add class 'hover-open' to sidebar navitem while hover in sidebar-icon-only menu
    document.querySelectorAll('.sidebar .nav-item').forEach(function (el) {
        el.addEventListener('mouseover', function () {
            if (body.classList.contains('sidebar-icon-only')) {
                el.classList.add('hover-open');
            }
        });
        el.addEventListener('mouseout', function () {
            if (body.classList.contains('sidebar-icon-only')) {
                el.classList.remove('hover-open');
            }
        });
    });
})
