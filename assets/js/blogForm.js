import '../css/blogForm.scss';

$(function () {

    $(document).on('change', 'input[type=file]', function () {
        let id = $(this).attr('id');
        let filename = $(this).val();
        const idx = filename.lastIndexOf("\\");
        filename = filename.substr(idx + 1);
        $(`label[for=\'${id}\']`).html(filename);
        let input = $(this);
        if (!$(this).hasClass('vich-no-preview')) {
            let previewId = input.attr('id') + '_preview'
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(`#${previewId}`).remove();
                    if ($(input).parent().parent().find('a img')) {
                        $(input).parent().parent().find('a').remove()
                    }
                    input.closest('fieldset').after(`<div class=" form-group d-flex justify-content-center" id="${previewId}"><img class="w-50" src="${e.target.result}"/></div>`);
                }
                reader.readAsDataURL(this.files[0]);
            }
        }
    });


    $('.add-another-collection-widget').click(function (e) {
        var list = $($(this).attr('data-list-selector'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = `<button type="button" class="deleteSectionBtn float-right btn btn-danger btn-sm mb-2" style="position: absolute;top: -5%;right: 0;">X</button>` + newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list);
    });

    $(document).on('click', '.deleteSectionBtn', function (e) {
        e.preventDefault();
        $(this).parent().remove();
    })

    $('#article-sections-list legend').hide()
    $('#article-sections-list >fieldset').prepend(`<button type="button" class="deleteSectionBtn float-right btn btn-danger btn-sm mb-2">X</button>`)

});
