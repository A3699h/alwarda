const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(function () {
    Routing.setRoutingData(routes);

    $('.can-provide').on('click', function () {
        $('#loading-spinner').show();
        let productId = $(this).data('id-product');
        $.post(
            Routing.generate('add_product_to_shop', {id: productId})
        ).then((res) => {
            $('.content-wrapper').prepend(`<div class="alert alert-success" role="alert">
                                                 Product added
                                            </div>`);
            setTimeout(function () {
                $('.alert').remove()
            }, 2000)
            $(this).closest('tr').remove();
            console.log(res)
        }).catch((err) => {
            $('.content-wrapper').prepend(`<div class="alert alert-danger" role="alert">
                                                 Error occured, Please reload the page and try again
                                            </div>`);
            setTimeout(function () {
                $('.alert').remove()
            }, 2000)
            console.log(err)
        }).always(() => {
            $('#loading-spinner').hide();
        })
    })

    $('.cant-provide').on('click', function () {
        $('#loading-spinner').show();
        let productId = $(this).data('id-product');
        $.post(
            Routing.generate('remove_product_from_shop', {id: productId})
        ).then((res) => {
            $('.content-wrapper').prepend(`<div class="alert alert-success" role="alert">
                                                 Product removed
                                            </div>`);
            setTimeout(function () {
                $('.alert').remove()
            }, 2000)
            $(this).closest('tr').remove();
            console.log(res)
        }).catch((err) => {
            $('.content-wrapper').prepend(`<div class="alert alert-danger" role="alert">
                                                 Error occured, Please reload the page and try again
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
