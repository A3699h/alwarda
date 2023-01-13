// import '../../css/front/frontCatalog.scss';

const routes = require('../../../public/js/fos_js_routes.json');
// import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

import 'jquery-ui/ui/widgets/slider'

const filters = {
    sortBy: null,
    color: null,
    minPrice: 0,
    maxPrice: $('#filterMaxPrice').text(),
    category: null,
    page: 1
};


$(function () {
    Routing.setRoutingData(routes);
    const categoriesIds = [];
    $('.categories-section > ul > li > button').each(function (el) {
        categoriesIds.push($(this).data('category-id'));
    });
    // Get the selected category from query
    const urlParams = new URLSearchParams(window.location.search);
    const categoryQueryparam = urlParams.get('category');
    // Activate selected category if exists in query
    if (categoryQueryparam && categoriesIds.includes(parseInt(categoryQueryparam))) {
        let categoryBtn = $('.categories-section > ul > li > button').filter(function (el) {
            $(this).removeClass('active');
            return $(this).data('category-id') == categoryQueryparam;
        });
        if (categoryBtn) {
            $(categoryBtn).addClass('active');
            filters.category = categoryQueryparam;
        }
    } else {
        filters.category = $('.selectCategory.active').data('category-id');
    }

    $('#loading-spinner').hide()
    $("#priceFilter").slider({
        range: true,
        min: 0,
        max: $('#filterMaxPrice').text(),
        values: [0, $('#filterMaxPrice').text()],
        slide: function (event, ui) {
            $("#filterMinPrice").text(ui.values[0]);
            $("#filterMaxPrice").text(ui.values[1]);
            filters.minPrice = ui.values[0];
            filters.maxPrice = ui.values[1];
        },
        stop: function () {
            filters.page = 1;
            displaySelectedCategoryProducts(true);
        }
    });

    $('#sortFilter .dropdown-item').on('click', function (e) {
        e.preventDefault();
        if ($(this).data('sort') == 'none') {
            filters.sortBy = null;
            $(this).parent().siblings('button').text($(this).parent().siblings('button').data('text'));
        } else {
            filters.sortBy = $(this).data('sort');
            $(this).parent().siblings('button').text($(this).text());
        }
        filters.page = 1;
        displaySelectedCategoryProducts(true);
    });

    $('#colorFilter .dropdown-item').on('click', function (e) {
        e.preventDefault();
        if ($(this).data('sort') == 'none') {
            filters.color = null;
            $(this).parent().siblings('button').text( $(this).parent().siblings('button').data('text'));
        } else {
            filters.color = $(this).data('sort');
            $(this).parent().siblings('button').text($(this).text());
        }
        filters.page = 1;
        displaySelectedCategoryProducts(true);
    });

    displaySelectedCategoryProducts(true);

    $('.selectCategory').on('click', function (e) {
        e.preventDefault();
        $('.selectCategory.active').removeClass('active');
        $(this).addClass('active');
        filters.category = $(this).data('category-id');
        filters.page = 1;
        displaySelectedCategoryProducts(true);
    });

    $('#catalogSelect').on('change', function () {
        filters.category = $("#catalogSelect option:selected").val();
        filters.page = 1;
        displaySelectedCategoryProducts(true);
    });

    $('.footer-section button').on('click', function (e) {
        e.preventDefault();
        filters.page++;
        displaySelectedCategoryProducts(false);
    })
});

function displaySelectedCategoryProducts(empty) {
    let storedDeliveryArea = localStorage.getItem('deliveryArea');

    if (storedDeliveryArea) {
        $('#loading-spinner').show();
        $.post(
            Routing.generate('api_front_catalog_filtred'),
            {filters}
        ).then((data) => {
            if (data) {
                filters.page = data.page;
                if (data.pages == filters.page || data.pages == 0) {
                    $('.footer-section').hide();
                } else {
                    $('.footer-section').show();
                }
                displayProducts(empty, data.products)
            }
        }).catch((error) => {
            console.log(error)
        }).always(() => {
            $('#loading-spinner').hide();
        })
        return true;
    } else {
        setTimeout(function () {
            // console.log('trying again')
            displaySelectedCategoryProducts(empty);
        }, 200);

    }
}

function displayProducts(empty, products) {
    let storedDeliveryArea = localStorage.getItem('deliveryArea');
    products = products.filter((item) => {
        let product = item[0];
        let productProviders = product.users;
        let providedInAreas = [];
        productProviders.forEach((user) => {
            providedInAreas.push(user.area.id)
        });
        return providedInAreas.some(id => id == storedDeliveryArea);
    });
    let heartIcon = $('#productSection').data('heart-icon');
    let cartIcon = $('#productSection').data('cart-icon');
    let bodyProducts = [];
    for (let product of products) {
        let productImages = '';
        product[0].images.forEach((el) => {
            productImages += '_|_' + el['image_path'];
        });
        let productImage = product[0].images.length > 0 ? `<img src="${product[0].images[0]['image_path']}" alt="product image">` : ' <div class="w-100 h-100 bg-secondary"></div>';
        bodyProducts.push(`<div class="h-50 mt-4 col-lg-3 col-12 col-sm-6">
                    <div class="flower-card h-100 w-100 d-flex flex-column" 
                    data-id="${product[0].id}"
                    data-price="${product.price}"
                    data-name="${$('body').data('locale')=='en' ? product[0].name : product[0]['name_ar']}"
                    data-images="${productImages}"
                    data-description="${$('body').data('locale')=='en' ? product[0].description :product[0]['description_ar'] }">
                        <div class="flower-image-container">
                            <div class="w-100 h-100">
                               ${productImage}
                            </div>
                        </div>
                        <h3 class="text-center">${$('body').data('locale')=='en' ? product[0].name : product[0]['name_ar']}</h3>
                        <h4 class="d-lg-none d-block">${product.price} SAR</h4>
                        <div class="flower-card-footer-mobile d-flex d-lg-none justify-content-between align-items-center">
                            <button class="btnAddCart btn">Add to Cart</button>
                            <button class="btn d-none">
                                <img src="${heartIcon}" alt="heart icon">
                            </button>
                        </div>
                        <div class="flower-card-footer mt-auto mb-3 d-none d-lg-flex justify-content-around align-items-center">
                            <button class="btn d-none">
                                <img src="${heartIcon}" alt="heart icon">
                            </button>
                            <h4>${product.price} SAR</h4>
                            <button class="btnAddCart btn cart-icon">
                                <img src="${cartIcon}" alt="cart icon">
                            </button>
                        </div>
                    </div>
                </div>`);
    }

    if (empty) {
        let bodyElementHtml = $('<div class="body-many col-lg-11 col-12 d-flex flex-wrap"></div>');
        bodyElementHtml.html(bodyProducts);
        $('#productSection').empty();
        $('#productSection').append(bodyElementHtml);
    } else {
        $('#productSection .body-many').append(bodyProducts);
    }
}
