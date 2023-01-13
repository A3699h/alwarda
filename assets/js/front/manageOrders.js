import '../../css/front/manageOrders.scss';

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import $ from "jquery";

$(function () {
    Routing.setRoutingData(routes);

    $('.trackBtn').on('click', function () {
        $('#loading-spinner').show();
        let orderId = $(this).data('order-id');
        $.post(
            Routing.generate('front_api_get_token')
        ).then((res) => {
            localStorage.setItem('token', res);
            $.ajax({
                url: Routing.generate('api_client_order', {id: orderId}),
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + res
                },
            }).then((res) => {
                // populate and display model
                let trackItemsHtml = [];
                res['order_details'].forEach((detail) => {
                    trackItemsHtml.push(`<div class="d-flex justify-content-center align-content-between mt-3">
                        <div class="col-3">
                            <img class="w-100" src="${detail.product.images[0]['image_path']}" alt="product image">
                        </div>
                        <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                            <h5>${detail.product.name}</h5>
                            <h6 class="mt-3">${detail.product.price} SAR</h6>
                        </div>
                        <div class="col-1 d-flex justify-content-center align-items-center">
                            <span>${detail.quantity}</span>
                        </div>
                    </div>`);
                });
                $('#trackOrderModal .order-sommary').html(trackItemsHtml);
                let address = res['delivery_address']['reciever_full_address'];
                let totalPrice = res['total_price'] + ' SAR';
                let recipientName = res['message_to'];
                let orderRef = res.reference;
                let deliveryTime = res['delivery_date'] + ' : ' + res['delivery_slot']['delivery_at'] + ' - ' + res['delivery_slot']['delivery_to'];
                $('#trackOrderModalAddress').text(address);
                $('#trackOrderTotalPrice').text(totalPrice);
                $('#trackOrderModalRecipientName').text(recipientName);
                $('#trackOrderModalOrderRef').text(orderRef);
                $('#trackOrderModalDeliveryTime').text(deliveryTime);

                let orderStep = parseInt(res['custom_status_code']);
                $('#trackOrderModal .order-status > span').each(function (index) {
                    let thisStep = parseInt($(this).data('step'));
                    if (orderStep >= thisStep) {
                        $(this).addClass('validated');
                        let image = $(this).find('img');
                        $(image).attr('src', $(image).data('validated-icon'));
                    } else {
                        $(this).removeClass('validated');
                        let image = $(this).find('img');
                        $(image).attr('src', $(image).data('icon'));
                    }
                });
                $('#trackOrderModal .order-status i').each(function (index) {
                    let thisStep = parseInt($(this).data('step'));
                    if (orderStep >= thisStep) {
                        $(this).addClass('validated');
                    } else {
                        $(this).removeClass('validated');
                    }
                });

                $('#trackOrderModal').modal('show');
            }).catch((err) => {
                console.log('Order Error: ' + err)
            }).always(() => {
                $('#loading-spinner').hide();
            })
        }).catch((err) => {
            console.log('Token Error: ' + err);
            $('#loading-spinner').hide();
        })
    });
    $('.cancelBtn').on('click', function () {
        $('#loading-spinner').show();
        let orderId = $(this).data('order-id');
        let elementParent = $(this).parent().parent();
        $.post(
            Routing.generate('front_api_get_token')
        ).then((res) => {
            localStorage.setItem('token', res);
            $.ajax({
                url: Routing.generate('api_client_cancel_order', {id: orderId}),
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + res
                },
            }).then((res) => {
                $(elementParent).fadeOut(1000);
                setTimeout(function () {
                    $(elementParent).remove();
                }, 1000)
            }).catch((err) => {
                console.log('Order Error: ' + err)
            }).always(() => {
                $('#loading-spinner').hide();
            })
        }).catch((err) => {
            console.log('Token Error: ' + err);
            $('#loading-spinner').hide();
        })
    });

    // treat the redirection from payment
    const urlParams = new URLSearchParams(window.location.search);
    const paymentStatus = urlParams.get('payment');
    if (paymentStatus == 'success') {
        // console.log(res);
        $('#checkOutModal .step1').addClass('d-none');
        $('#checkOutModal .step2').addClass('d-none');
        $('#checkOutModal .step3').addClass('d-none');
        $('#checkOutModal .step4').removeClass('d-none');
        localStorage.removeItem('checkoutStep');
        localStorage.removeItem('cart');
        localStorage.removeItem('order');
        $('#checkOutModal').modal('show');
        refreshCartData();
        refreshCartNotifications();
    }else if(paymentStatus == 'failed'){
        localStorage.setItem('checkoutStep', '1');
        $('#checkOutModal .step1').removeClass('d-none');
        $('#checkOutModal .step2').addClass('d-none');
        $('#checkOutModal .step3').addClass('d-none');
        $('#checkOutModal .step4').addClass('d-none');
        $('#checkOutModal').modal('show');
    }

    $('#loading-spinner').hide();

});


function refreshCartData() {
    let currentCart = JSON.parse(localStorage.getItem('cart'));
    let items = currentCart ? currentCart.length : 0;
    let cartItemsHtml = [];
    let trackItemsHtml = [];
    if (currentCart && currentCart.length > 0) {
        let totalPrice = 0;
        currentCart.forEach((item) => {
            totalPrice += parseFloat(item.price * item.quantity);
            let itemImage = `<div class="cart-item-image col-3"></div>`;
            if (item.image != '') {
                itemImage = `<div class="cart-item-image col-3">
                                <img src="${item.image}" alt="cart item image" class="w-100">
                            </div>`;
            }
            cartItemsHtml.push(`<div class="cart-item mt-5 d-flex position-relative">
                                    <button data-id="${item.product}" class="cart-item-delete btn position-absolute">x</button>
                                    ${itemImage}
                                    <div class="cart-item-body col-7 d-flex flex-column justify-content-around">
                                        <h2>${item.name}</h2>
                                        <div class="productQuantity" data-product-id="${item.product}">
                                            <button class="mr-3 btn decrease">-</button>
                                            <input type="hidden" value="${item.quantity}">
                                            <span class="mr-3 ">${item.quantity}</span>
                                            <button class="btn increase">+</button>
                                        </div>
                                    </div>
                                    <div class="cart-item-price col-2 d-flex align-items-center">
                                        <h2><span>${item.price * item.quantity}</span> SAR</h2>
                                    </div>
                                </div>`);
            trackItemsHtml.push(`<div class="d-flex justify-content-center align-content-between mt-3">
                        <div class="col-3">
                            <img class="w-100" src="${item.image}" alt="">
                        </div>
                        <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                            <h5>${item.name}</h5>
                            <h6 class="mt-3">${item.price} SAR</h6>
                        </div>
                        <div class="col-1 d-flex justify-content-center align-items-center">
                            <span>${item.quantity}</span>
                        </div>
                    </div>`);
        });
        $('#checkOutModal .step1 .modal-footer-container').show();
        $('#checkOutModal .step1 .modal-body').html(cartItemsHtml);
        $('#trackOrderModal .order-sommary').html(trackItemsHtml);
        let taxValue = totalPrice * 0.05;
        $('#cartTotalPrice').text((totalPrice + taxValue) + ' SAR');
        let deliveryPrice = parseFloat(localStorage.getItem('deliveryPrice'));
        $('#cartDeliveryPrice').text(deliveryPrice > 0 ? (deliveryPrice + ' SAR') : 'Free');
        $('#cartTaxPrice').text(parseFloat(taxValue) + ' SAR');
        $('#trackOrderTotalPrice').text((totalPrice + taxValue + deliveryPrice) + ' SAR');

        // update the summary data
        $('#checkOutModal .step3 .orderSubTotalPrice').text(totalPrice + ' SAR');
        $('#checkOutModal .step3 .orderTaxPrice').text(parseFloat(taxValue) + ' SAR');
        $('#checkOutModal .step3 .orderDeliveryPrice').text(deliveryPrice > 0 ? (deliveryPrice + ' SAR') : 'Free');
        $('#checkOutModal .step3 .orderVoucherDiscountValue').text();
        $('#checkOutModal .step3 .orderTotalPrice').text((totalPrice + taxValue + deliveryPrice) + ' SAR');
    } else {
        $('#checkOutModal .step1 .modal-body').html('');
        $('#checkOutModal .step1 .modal-footer-container').hide();
    }
    $('#checkOutModal .step1 .itemsCount').text(items);
}

function refreshCartNotifications() {
    let currentCart = JSON.parse(localStorage.getItem('cart'));
    let items = currentCart ? currentCart.length : 0;
    $('#navbarTogglerDemo01 .notifications').text(items);
    $('#navbarTogglerDemo01 .notifications').removeClass('d-none');
    if (!currentCart || items == 0) {
        $('#navbarTogglerDemo01 .notifications').addClass('d-none');
    }
}
