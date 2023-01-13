import $ from "jquery";
import 'bootstrap';
import '../../css/front/frontApp.scss';

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import '../front/_authTreatment';
import '../front/_checkoutTreatment';

$(function () {
    // console.log($('body').data('locale'))

    Routing.setRoutingData(routes);

    redesignNavbar();

    $(document).on('scroll', function () {
        redesignNavbar();
    });
    // get areas to populate dropdown
    $.get(
        Routing.generate('api_client_areas')
    ).then((data) => {
        let storedDeliveryArea = localStorage.getItem('deliveryArea');
        if (data && data.length > 0) {
            if (!storedDeliveryArea) {
                storedDeliveryArea = data[0].id;
                localStorage.setItem('deliveryArea', data[0].id);
            }
            let areaHtml = [];
            let areaName = $('body').data('locale') == 'en' ? data[0]['name_en'] : data[0]['name_ar'];
            let dropDownLabel = `<a class="btn" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ${areaName}
                            </a>`;
            data.forEach((area) => {
                let areaName = $('body').data('locale') == 'en' ? area['name_en'] : area['name_ar'];
                if (area.id == storedDeliveryArea) {
                    localStorage.setItem('deliveryPrice', data[0]['delivery_price']);
                    dropDownLabel = `<a class="btn" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                                ${areaName}
                            </a>`;
                }
                areaHtml.push(`<a class="dropdown-item" data-price="${area['delivery_price']}" data-id="${area.id}" href="#">${areaName}</a>`);
            });
            $("#navbarDeliverySelect").prepend(dropDownLabel);
            $("#navbarDeliverySelect > .dropdown-menu").html(areaHtml);
        }
    }).catch((error) => {
        console.log(error);
    });
    // Get app params to put in footer
    $.get(
        Routing.generate('front_get_params')
    ).then((data) => {
        if (data) {
            data.forEach((param) => {
                let slug = param.slug;
                let value = param.value;
                if (['paramIosLink', 'paramAndroidLink'].includes(slug)) {
                    $(`#${slug}`).attr('href', value);
                    $(`#${slug}Top`).attr('href', value);
                } else {
                    $(`#${slug}`).text(value);
                }
            })
        }
    }).catch((error) => {
        console.log(error);
    });


    $(document).on('click', '#navbarDeliverySelect > .dropdown-menu .dropdown-item', function (e) {
        e.preventDefault();
        let areaId = $(this).data('id');
        let areaPrice = $(this).data('price');
        let areaName = $(this).text();
        localStorage.setItem('deliveryPrice', areaPrice);
        localStorage.setItem('deliveryArea', areaId);
        $("#navbarDeliverySelect #dropdownMenuLink").text(areaName);
        refreshCartData();
        location.reload()
    });

    $.post(Routing.generate('api_front_categories_having_products')).then((data) => {
        if (data) {
            let categoriesHTML = [];
            data.forEach((category) => {
                let categoryUrl = Routing.generate('front_catalog', {
                    category: category.id,
                    _locale: $('body').data('locale')
                })
                let categoryName = $('body').data('locale') == 'en' ? category.name : category['name_ar'];
                categoriesHTML.push(`<a class="dropdown-item" href="${categoryUrl}">${categoryName}</a>`);
            });
            $('.categories-dropdown').html(categoriesHTML);
        }
    }).catch((error) => {
        console.log(error);
    });

    $(document).on('click', '#singleProductModal .productQuantity .increase', function () {
        let qte = parseInt($('#singleProductModal .productQuantity input').val());
        $('#singleProductModal .productQuantity span').text(qte + 1);
        $('#singleProductModal .productQuantity input').val(qte + 1);
    });

    $(document).on('click', '#singleProductModal .productQuantity .decrease', function () {
        let qte = parseInt($('#singleProductModal .productQuantity input').val());
        if (qte > 1) {
            $('#singleProductModal .productQuantity span').text(qte - 1);
            $('#singleProductModal .productQuantity input').val(qte - 1);
        }
    });

    $(document).on('click', '.flower-card .btnAddCart', function () {
        $('#singleProductModal .productQuantity span').text(1);
        $('#singleProductModal .productQuantity input').val(1);
        $('#singleProductModal').modal('show');
        let flowerCard = $(this).closest('.flower-card');
        let name = $(flowerCard).data('name');
        let description = $(flowerCard).data('description');
        let price = $(flowerCard).data('price');
        let images = $(flowerCard).data('images').split('_|_');
        $('.modal-body .productName').text(name);
        $('.modal-body .productDescription').text(description);
        $('.modal-body .productPrice').text(price + ' SAR');
        let imagesHTML = [];
        let carouselIndicatorsHTML = [];
        imagesHTML = `<div class=" ">
                                <div class="d-flex h-100">
                                    <img class="w-100" src="${images[1]}" alt="product image">
                                </div>
                            </div>`;
        $('#singleProductModal .addCart').data('product-image', images[1]);
        if (images.length > 1) {
            $('#singleProductModal .productImages').html(imagesHTML);
        } else {
            $('#singleProductModal .productImages').html('');
        }
        $('#singleProductModal .addCart').data('product-id', $(flowerCard).data('id'));
        $('#singleProductModal .addCart').data('product-name', name);
        $('#singleProductModal .addCart').data('product-price', price);
    });

    $('#singleProductModal .addCart').on('click', function () {
        let product = $(this).data('product-id');
        let name = $(this).data('product-name');
        let price = $(this).data('product-price');
        let image = $(this).data('product-image');
        let quantity = parseInt($('#qteInput').val());
        let currentCart = JSON.parse(localStorage.getItem('cart'));
        if (!currentCart) {
            localStorage.setItem('cart', JSON.stringify([{product, quantity, name, price, image}]));
        } else {
            let added = false;
            currentCart.forEach((el, index) => {
                if (el.product == product) {
                    currentCart[index].quantity += quantity;
                    added = true;
                }
            });
            if (!added) {
                currentCart.push({product, quantity, name, price, image});
            }
            localStorage.setItem('cart', JSON.stringify(currentCart));
        }
        $('#singleProductModal').modal('hide');
        refreshCartNotifications();
        refreshCartData();
    });

    $('#navbarTogglerDemo01 .nav-item:not(.dropdown)').on('click', function (e) {
        let link = $(this).find('a').attr('href');
        if (link == '#about') {
            e.preventDefault();
            let offset = $('#adsSection').offset();
            if (offset) {
                $('html, body').animate({
                    scrollTop: offset.top - 20
                })
            } else {
                window.location = Routing.generate('front_index') + '#adsSection'
            }
        } else if (link == '#delivery') {
            e.preventDefault();
            let offset = $('#deliverySection').offset();
            if (offset) {
                $('html, body').animate({
                    scrollTop: offset.top - 100
                })
            } else {
                window.location = Routing.generate('front_index') + '#deliverySection'
            }
        } else if (link == '#footer') {
            e.preventDefault();
            let offset = $('footer').offset();
            $('html, body').animate({
                scrollTop: offset.top - 120
            })
        }
    });

    $('#singleProductModal').on('hide.bs.modal', function () {
        $('#singleProductModal .addCart').data('product-image', '');
        $('#singleProductModal .productImages').html('');
        $('#singleProductModal .addCart').data('product-id', '');
        $('#singleProductModal .addCart').data('product-name', '');
        $('#singleProductModal .addCart').data('product-price', '');
    })

    refreshCartNotifications();
    refreshCartData();
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

function redesignNavbar() {
    if (window.scrollY <= 0) {
        $('body').removeClass('navbar-retracted');
    } else {
        $('body').addClass('navbar-retracted');
    }
}
