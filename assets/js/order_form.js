import $ from "jquery";

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

var $orderDetailsCollectionHolder;

var $addOrderDetailButton = $('<td colspan="3"><button type="button" class="btn mt-3 float-right btn-success add_image_link"><i class="fas fa-plus"></i></button></td>');
var $newOrderDetailLinkTr = $('<tr></tr>').append($addOrderDetailButton);


$(function () {
    Routing.setRoutingData(routes);

    let $userSelect = $('.user-basic-select2');

    $orderDetailsCollectionHolder = $('table.order-details-table');
    $orderDetailsCollectionHolder.append($newOrderDetailLinkTr);


    $orderDetailsCollectionHolder.data('index', $orderDetailsCollectionHolder.find('tr').length - 2);

    $addOrderDetailButton.on('click', 'button', function (e) {
        // add a new tag form (see next code block)
        addOrderDetailForm($orderDetailsCollectionHolder, $newOrderDetailLinkTr);
    });

    $(document).on('click', '.delete-order-detail-row', function () {
        $(this).parent().parent().fadeOut().remove()
    });


    $userSelect.select2({
        theme: "bootstrap"
    });

    fetchDeliveryAddresses($userSelect.find(':selected').val());

    $userSelect.on('change', function () {
        let $selectedUser = $(this).find(':selected').val();
        fetchDeliveryAddresses($selectedUser);
    });

    $('.products-basic-select2').select2({
        theme: "bootstrap"
    });

    var select2Target = $('.order-details-table tbody')[0];

    if (select2Target) {
        // create an observer instance
        var observer = new MutationObserver(function (mutations) {
            //loop through the detected mutations(added controls)
            mutations.forEach(function (mutation) {
                //addedNodes contains all detected new controls
                if (mutation && mutation.addedNodes) {
                    mutation.addedNodes.forEach(function (elm) {
                        $(elm).find('.products-basic-select2').select2({
                            theme: "bootstrap"
                        });
                    });
                }
            });
        });

        // pass in the target node, as well as the observer options
        observer.observe(select2Target, {
            childList: true
        });
    }
    //
    // getDiscountPercentage('#order_discountVoucher');
    //
    // $('#order_discountVoucher').on('change', function () {
    //     getDiscountPercentage(this);
    // });

    $('#order_messageFile_fileFile_file').on('change', function () {
        let filename = $(this).val();
        const idx = filename.lastIndexOf("\\");
        filename = filename.substr(idx + 1);
        $('label[for=\'order_messageFile_fileFile_file\']').html(filename);
    });

    $('#save_view_btn').on('click', function () {
        $('#willView').val("1");
    });

});


function getDiscountPercentage(elm) {
    let selectedVoucher = $(elm).children("option:selected").text();
    let percentage = selectedVoucher.match(/\-.*\s\%/)[0].replace(/%/, '').replace(/-/, '').trim();

    console.log(percentage)
}


function fetchDeliveryAddresses($selectedUser) {
    $('#loading-spinner').show();
    $.post(
        Routing.generate('addressByClient', {id: $selectedUser})
    ).then((res) => {
        var $addressesSelect = $("#order_deliveryAddress");
        $addressesSelect.html('');
        $.each(res, function (key, address) {
            $addressesSelect.append('<option value="' + address.id + '">' + address.address + '</option>');
        });
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
}


function addOrderDetailForm($collectionHolder, $newLinkLi) {

    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    console.log(index)
    var newForm = prototype;
    // You need this only if you didn't set 'label' => false in your tags field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<tr></tr>').append(newForm);
    $newLinkLi.before($newFormLi);

}

