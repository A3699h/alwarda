const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(function () {
    Routing.setRoutingData(routes);

    $('.editParam').on('click', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        let name = $(this).data('name');
        let value = $(this).data('value');
        $('#paramId').val(id);
        $('#paramValue').val(value);
        $('#paramName').text(name);
        $('#modalEditParam').modal('show');
    });

    $('#submitEditParam').on('click', function (e) {
        e.preventDefault();
        let href = Routing.generate('dashboard_update_param', {
            id: $('#paramId').val(),
            value: $('#paramValue').val()
        });
        window.location.href = href;
    })

})
