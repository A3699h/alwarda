import $ from "jquery";

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(function () {
    Routing.setRoutingData(routes);

    $('#authModal .sign-up-part .redirectSignInBtn').on('click', function (e) {
        e.preventDefault();
        $('#authModal .sign-up-part').addClass('d-none');
        $('#authModal .sign-in-part').removeClass('d-none');
    });

    $('#authModal .sign-in-part .redirectSignUpBtn').on('click', function (e) {
        e.preventDefault();
        $('#authModal .sign-in-part').addClass('d-none');
        $('#authModal .sign-up-part').removeClass('d-none');
    });

    $('.main-navbar .navbar-login-btn').on('click', function (e) {
        let loggedIn = Boolean($(this).data('loggedin'));
        if (loggedIn) {
            // TODO : add treatment to redirect to profile
        } else {
            e.preventDefault();
            $('#authModal .sign-up-part').addClass('d-none');
            $('#authModal .sign-in-part').removeClass('d-none');
            $('#authModal').modal('show');
        }
    });

    $('#signInForm').on('submit', function (e) {
        e.preventDefault();
        $('#signInBtn').click();
    });

    $('#signInBtn').on('click', function () {
        $('#loading-spinner').show();
        let formData = new FormData(document.getElementById('signInForm'));
        $.ajax({
            url: Routing.generate('front_login'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
        }).then((res) => {
            $('.sign-in-part span.text-danger').text('');
            window.location.href = Routing.generate('front_profile', {_locale:$('body').data('locale')});
        }).catch((err) => {
            let error = err.responseJSON.message;
            $('.sign-in-part span.text-danger').text(error);
        }).always(() => {
            $('#loading-spinner').hide();
        });
    });


    $('#signUpBtn').on('click', function () {
        let phoneVal = $('#signUp-phone-code').val() + $('#signUp-phone-number').val();
        $('#signUp-phone').val(phoneVal);
        let formData = new FormData(document.getElementById('signUpForm'));
        if (phoneVal == '' || $('#signUp-phone-code').val() == '' || $('#signUp-phone-number').val() == '') {
            $('.sign-up-part span.text-danger').text('The phone number cannot be blank');
            return false;
        }
        $('#loading-spinner').show();
        localStorage.setItem('phoneNumber', phoneVal);
        localStorage.setItem('password', $('#passwordInput').val());
        $.ajax({
            url: Routing.generate('api_register_client'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
        }).then((res) => {
		alert(res);
            $('.sign-in-part').addClass('d-none');
            $('.verification-code').removeClass('d-none')
        }).catch((err) => {
            if (err.status == 400) {
                $('.sign-up-part span.text-danger').text('The phone number is invalid');
            } else {
                // let error = err.responseJSON.message;
                $('.sign-up-part span.text-danger').text('Please check and correct the fields.');
            }
        }).always(() => {
            $('#loading-spinner').hide();
        });
    });

    $('#verifyBtn').on('click', function () {
        $('#loading-spinner').show();
        $.ajax({
            url: Routing.generate('api_verify_user'),
            type: "POST",
            data: JSON.stringify({
                "phone": localStorage.getItem('phoneNumber'),
                "verification_code": $('#verifCodeInput').val()
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            let token = res.token;
            let user = res.user;
            localStorage.setItem('apiToken', token);
            localStorage.setItem('user', JSON.stringify(user));
            let formData = new FormData();
            $('#signInForm input[name="email"]').val(user.email);
            $('#signInForm input[name="password"]').val(localStorage.getItem('password'));
            $('#signInBtn').click();
            localStorage.removeItem('password')
        }).catch((err) => {
            $('.verification-code span.text-danger').text('An error occured, please try again later.');
        }).always(() => {
            $('#loading-spinner').hide();
        });
    })


    $('.eyeBtn').on('mousedown', function (e) {
        e.preventDefault();
        $(this).parent().find('input').attr('type', 'text');
    });

    $('.eyeBtn').on('mouseup', function (e) {
        e.preventDefault();
        $(this).parent().find('input').attr('type', 'password');
    });

    $('.eyeBtn').on('click', function (e) {
        e.preventDefault();
    });


})
