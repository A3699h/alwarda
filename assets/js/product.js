const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(function () {
    Routing.setRoutingData(routes);
    $('.product-toggle-enabled-switch').on('click', function () {
        $('#loading-spinner').show();
        let userId = $(this).data('id-product');
        $.post(
            Routing.generate('toggle_product_enabled', {id: userId})
        ).then((res) => {
            $('.content-wrapper').prepend(`<div class="alert alert-success" role="alert">
                                                 Product updated
                                            </div>`);
            setTimeout(function () {
                $('.alert').remove()
            }, 2000)
            console.log(res)
        }).catch((err) => {
            $('.content-wrapper').prepend(`<div class="alert alert-danger" role="alert">
                                                 Error occured, Please refresh the page and try again
                                            </div>`);
            setTimeout(function () {
                $('.alert').remove()
            }, 2000)
            console.log(err)
        }).always(() => {
            $('#loading-spinner').hide();
        })
    })

});
