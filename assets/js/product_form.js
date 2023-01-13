import '../css/product-form.scss';

const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

var $collectionHolder;

// setup an "add a tag" link
var $addImageButton = $('<button type="button" class="btn btn-success add_image_link"><i class="fas fa-plus"></i></button>');
var $newLinkLi = $('<li class="d-flex align-items-center justify-content-center"></li>').append($addImageButton);

$(document).ready(function () {
    Routing.setRoutingData(routes);

    let deleteOldImages = $('<i/>',
        {
            class: 'mdi mdi-delete text-danger',
            click: function (e) {
                let imgSrc = $(this).parent().find('img').attr('src');
                let imgExt = imgSrc.substr(imgSrc.lastIndexOf('.') + 1, imgSrc.length);
                let imgName = imgSrc.substring(imgSrc.lastIndexOf('/') + 1, imgSrc.lastIndexOf(imgExt) - 1);
                $('#loading-spinner').show();
                $.post(
                    Routing.generate('delete_product_image', {imgName: imgName, imgExt: imgExt})
                ).then((res) => {
                    $(this).closest('li').remove();
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
        });

    $('.vich-image').append(deleteOldImages);

    $('.vich-image .custom-file').hide();
    $('.vich-image .form-group').hide();
    // Get the ul that holds the collection of tags
    $collectionHolder = $('ul.products');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find('input').length);

    $addImageButton.on('click', function (e) {
        // add a new tag form (see next code block)
        addImageForm($collectionHolder, $newLinkLi);
    });

    // Update price field
    let $productCost = $('#product_cost');
    let productCost = $productCost.val() == '' ? 0 : $productCost.val();
    let $productBenefit = $('#product_benefit');
    let productBenefit = $productBenefit.val() == '' ? 0 : $productBenefit.val();
    $('#product_calculated_price').html(parseFloat(productCost) + parseFloat(productBenefit));

    $productCost.on('keyup', function (e) {
        productCost = $(this).val();
        $('#product_calculated_price').html(parseFloat(productCost) + parseFloat(productBenefit));
    });
    $productBenefit.on('keyup', function (e) {
        productBenefit = $(this).val();
        $('#product_calculated_price').html(parseFloat(productCost) + parseFloat(productBenefit));
    });
});

function addImageForm($collectionHolder, $newLinkLi) {

    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

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

    let inputId = 'product_images___name___imageFile_file'.replace(/__name__/g, index);
    //$(`${inputId}`).click()
    $(document).on('change', `#${inputId}`, function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(`#img_product_${index}`).remove();
                let divElement = document.createElement('div');

                divElement.setAttribute('id', `img_product_${index}`);

                let imgElement = $('<img>', {
                    id: inputId + '_img',
                    src: e.target.result,
                    width: '150'
                });

                let deleteButton = $('<i/>',
                    {
                        class: 'mdi mdi-delete text-danger',
                        click: function (e) {
                            $(this).closest('li').remove();
                        }
                    });

                divElement.append(imgElement[0]);
                divElement.append(deleteButton[0]);

                $(`#product_images_${index}`).append(divElement);
            }
            reader.readAsDataURL(this.files[0]);
        }
    })


    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    $(`#${inputId}`).closest('fieldset').hide();
    $($newFormLi).find('label').click()
}
