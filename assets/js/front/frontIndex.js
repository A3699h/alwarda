import '../../css/front/frontIndex.scss';


const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


$(function () {
    Routing.setRoutingData(routes);



    let selectedCategory = $('#catalogSection >.header>ul>li>button.active').data('category-id');

    $('#catalogSection .openCatalog').attr('href', Routing.generate('front_catalog', {category: selectedCategory, _locale: $('body').data('locale')}));
    getProductsByCategoryLimited(selectedCategory);


    // Activate clicked category button
    $('#catalogSection >.header>ul>li>button').on('click', function () {
        if ($(this).hasClass('active')) {
            return false;
        }
        $('#catalogSection >.header>ul>li>button.active').removeClass('active');
        $(this).addClass('active');
        $('#catalogSection .openCatalog').attr('href', Routing.generate('front_catalog', {category: $(this).data('category-id')}));
        // Get the data from api and add elements to document
        getProductsByCategoryLimited($(this).data('category-id'));
    });
    // mobile version
    $('#catalogSelect').on('change', function () {
        let selectedCategoryId = $(this).children("option:selected").val();
        $('#catalogSection .openCatalog').attr('href', Routing.generate('front_catalog', {category: selectedCategoryId}));
        getProductsByCategoryLimited(selectedCategoryId);
    });


    const urlParams = new URLSearchParams(window.location.search);
    const param = urlParams.get('param');
    if (param == 'login') {
        setTimeout(function () {
            $('#authModal .sign-in-part').removeClass('d-none');
            $('#authModal .sign-up-part').addClass('d-none');
            $('#authModal .verification-code').addClass('d-none');
            $('#authModal').modal('show');
        },500)
    }

});


// function to get the products from api with limit
function getProductsByCategoryLimited(categoryId, limit = 7) {
    let storedDeliveryArea = localStorage.getItem('deliveryArea');

    if (storedDeliveryArea) {
        // console.log('fetching data')
        $('#loading-spinner').show();
        // get products from server
        $.get(
            Routing.generate('api_front_category_products_limited', {id: categoryId, limit: limit})
        ).then((data) => {
            data = data.filter((product) => {
                let productProviders = product.users;
                let providedInAreas = [];
                productProviders.forEach((user) => {
                    providedInAreas.push(user.area.id)
                });
                return providedInAreas.some(id => id == storedDeliveryArea);
            });

            if (data.length > 0) {
                // console.log(filtredData)
                // display products on document
                let catalogSectionBody = $('#catalogSection > .body');
                let heartIcon = $(catalogSectionBody).data('heart-icon');
                let cartIcon = $(catalogSectionBody).data('cart-icon');
                let firstProduct = data[0];
                let firstProductImages = '';
                firstProduct.images.forEach((el) => {
                    firstProductImages += '_|_' + el['image_path'];
                });
                let firstProductImage = firstProduct.images.length > 0 ? `<img src="${firstProduct.images[0]['image_path']}" alt="product image">` : ' <div class="w-100 h-100 bg-secondary"></div>';
                let firstProductHtml = `<div class="col-lg-4 d-none d-lg-block">
                <div class="flower-card h-100 w-100 d-flex flex-column" 
                    data-id="${firstProduct.id}"
                    data-price="${firstProduct.price}"
                    data-name="${$('body').data('locale') == 'en' ? firstProduct.name : firstProduct['name_ar']}"
                    data-images="${firstProductImages}"
                    data-description="${$('body').data('locale') == 'en' ? firstProduct.description : firstProduct['description_ar']}">
                    <div class="flower-image-container">
                        <div class="w-100 h-100">
                            ${firstProductImage}
                        </div>
                    </div>
                    <h3 class="text-center">${$('body').data('locale') == 'en' ? firstProduct.name : firstProduct['name_ar']}</h3>
                    <div class="flower-card-footer mt-auto mb-3 d-flex justify-content-around align-items-center">
                        <button class="btn d-none">
                            <img src="${heartIcon}" alt="heart icon">
                        </button>
                        <h4>${firstProduct.price} SAR</h4>
                        <button class="btn btnAddCart cart-icon">
                            <img src="${cartIcon}" alt="cart icon">
                        </button>
                    </div>
                </div>
            </div>`;

                let products = data.slice(1);
                let bodyProducts = [];
                products.forEach((product) => {
                    let productImages = '';
                    product.images.forEach((el) => {
                        productImages += '_|_' + el['image_path'];
                    });
                    let productImage = product.images.length > 0 ? `<img src="${product.images[0]['image_path']}" alt="product image">` : ' <div class="w-100 h-100 bg-secondary"></div>';
                    bodyProducts.push(`<div class="h-50 col-lg-4 col-12 col-sm-6">
                    <div class="flower-card h-100 w-100 d-flex flex-column" 
                    data-id="${product.id}"
                    data-price="${product.price}"
                    data-name="${$('body').data('locale') == 'en' ? product.name : product['name_ar']}"
                    data-images="${productImages}"
                    data-description="${$('body').data('locale') == 'en' ? product.description : product['description_ar']}">
                        <div class="flower-image-container">
                            <div class="w-100 h-100">
                                ${productImage}
                            </div>
                        </div>
                        <h3 class="text-center">${$('body').data('locale') == 'en' ? product.name : product['name_ar']}</h3>
                        <h4 class="d-lg-none d-block">${product.price} SAR</h4>
                        <div class="flower-card-footer-mobile d-flex d-lg-none justify-content-between align-items-center">
                            <button class="btn">Add to Cart</button>
                            <button class="btn d-none">
                                <img src="${heartIcon}" alt="heart icon">
                            </button>
                        </div>
                        <div class="flower-card-footer mt-auto mb-3 d-none d-lg-flex justify-content-around align-items-center">
                            <button class="btn d-none">
                                <img src="${heartIcon}" alt="heart icon">
                            </button>
                            <h4>255 SAR</h4>
                            <button class="btn btnAddCart cart-icon">
                                <img class="" src="${cartIcon}" alt="cart icon">
                            </button>
                        </div>
                    </div>
                </div>`);
                });
                let bodyElementHtml = $('<div class="body-many col-lg-9 col-12 d-flex flex-wrap"></div>');
                bodyElementHtml.html(bodyProducts);
                // bodyElementHtml.append(bodyProducts);
                // console.log(bodyElementHtml)
                $(catalogSectionBody).empty();
                $(catalogSectionBody).append(firstProductHtml);
                $(catalogSectionBody).append(bodyElementHtml);
            }
            $('#loading-spinner').hide();
        }).catch((error) => {
            console.log(error);
            $('#loading-spinner').hide();
        })
        return true;
    } else {
        // console.log('waiting')
        setTimeout(function () {
            // console.log('trying again')
            getProductsByCategoryLimited(categoryId, limit);
        }, 200);
    }
}
