// import '../css/product-form.scss';

import $ from "jquery";

var $collectionHolder;

// setup an "add a tag" link
var $addSubAreaButton = $('<td colspan="5"><button type="button" class="btn mt-3 float-right btn-success add_image_link"><i class="fas fa-plus"></i></button></td>');
var $newLinkTr = $('<tr></tr>').append($addSubAreaButton);

$(document).ready(function () {

    $collectionHolder = $('table.subAreas');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkTr);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find('input').length);

    $addSubAreaButton.on('click', 'button', function (e) {
        // add a new tag form (see next code block)
        addSubAreaForm($collectionHolder, $newLinkTr);
    });

    $(document).on('click', '.delete_subArea_row', function () {
        $(this).parent().parent().fadeOut().remove()
    })
});

function addSubAreaForm($collectionHolder, $newLinkLi) {

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

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<tr></tr>').append(newForm);
    $newLinkLi.before($newFormLi);
}
