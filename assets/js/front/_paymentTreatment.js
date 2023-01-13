import $ from "jquery";

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(function () {
    Routing.setRoutingData(routes);

    //pass your public key from tap's dashboard
    var tap = Tapjsli('pk_test_TcC8KBV2fLaxI9HsZm160wd7');
    // var tap = Tapjsli('pk_live_qE6wJKGrzyVBaxHRjFe8io2O');

    var elements = tap.elements({});

    var style = {
        base: {
            color: '#535353',
            lineHeight: '18px',
            fontFamily: 'sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: 'rgba(0, 0, 0, 0.26)',
                fontSize: '15px'
            }
        },
        invalid: {
            color: 'red'
        }
    };
    // input labels/placeholders
    var labels = {
        cardNumber: "Card Number",
        expirationDate: "MM/YY",
        cvv: "CVV",
        cardHolder: "Card Holder Name"
    };
    //payment options
    var paymentOptions = {
        currencyCode: ["SAR"],
        labels: labels,
        TextDirection: 'ltr'
    }
    //create element, pass style and payment options
    var card = elements.create('card', {style: style}, paymentOptions);
    //mount element
    card.mount('#element-container');
    //card change event listener
    card.addEventListener('change', function (event) {
        if (event.loaded) {
            console.log("UI loaded :" + event.loaded);
            console.log("current currency is :" + card.getCurrency())
        }
        var displayError = document.getElementById('error-handler');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    $('#testPay').click(function () {

    })

})
