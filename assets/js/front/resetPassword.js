import '../../css/front/resetPassword.scss';

$(function () {

    $('.reset-password-section .savePasswordBtn').on('click', function () {
        $(".reset-password-section  form").submit();
    })

    $('#loading-spinner').hide();
})
