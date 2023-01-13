import '../../css/front/recipientsList.scss';

$(function () {

    if(formInvalid){
        $('#newRecipientModal').modal('show')
    }
    $('.submitBtn').on('click', function () {
        $('#newRecipientModal form').submit();
    })

    $('#loading-spinner').hide();
})
