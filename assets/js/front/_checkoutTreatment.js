import $ from "jquery";

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

// import '../front/_paymentTreatment';

let recorder = null;
let audio;
let recordedBlob = null;
let messageFile;
let selectedMedia;

$(function () {
    Routing.setRoutingData(routes);

    $(document).on('click', '#checkOutModal .step1 .productQuantity .increase', function () {
        let product = $(this).parent().data('product-id');
        let currentCart = JSON.parse(localStorage.getItem('cart'));
        currentCart.forEach((el, index) => {
            if (el.product == product) {
                currentCart[index].quantity += 1;
            }
        });
        localStorage.setItem('cart', JSON.stringify(currentCart));
        refreshCartData();
    });

    $(document).on('click', '#checkOutModal .step1 .productQuantity .decrease', function () {
        let product = $(this).parent().data('product-id');
        let currentCart = JSON.parse(localStorage.getItem('cart'));
        currentCart.forEach((el, index) => {
            if (el.product == product) {
                if (currentCart[index].quantity > 1) {
                    currentCart[index].quantity -= 1;
                }
            }
        });
        localStorage.setItem('cart', JSON.stringify(currentCart));
        refreshCartData();
    });

    $('.notifBtn').on('click', function (e) {
        e.preventDefault();
        let checkoutStep = localStorage.getItem('checkoutStep') ?? '1';
        switch (checkoutStep) {
            case '1':
                $('#checkOutModal .step1').removeClass('d-none');
                $('#checkOutModal .step2').addClass('d-none');
                $('#checkOutModal .step3').addClass('d-none');
                $('#checkOutModal .step4').addClass('d-none');
                break;
            case '2':
                $('#checkOutModal .step1').addClass('d-none');
                $('#checkOutModal .step2').removeClass('d-none');
                $('#checkOutModal .step3').addClass('d-none');
                $('#checkOutModal .step4').addClass('d-none');
                break;
            case '3':
                $('#checkOutModal .step1').addClass('d-none');
                $('#checkOutModal .step2').addClass('d-none');
                $('#checkOutModal .step3').removeClass('d-none');
                $('#checkOutModal .step4').addClass('d-none');
                break;
            case '4':
                $('#checkOutModal .step1').addClass('d-none');
                $('#checkOutModal .step2').addClass('d-none');
                $('#checkOutModal .step3').addClass('d-none');
                $('#checkOutModal .step4').removeClass('d-none');
                break;
        }
        // fetch delivery slots
        $.post(
            Routing.generate('front_api_get_token')
        ).then((res) => {
            localStorage.setItem('token', res);
            $.ajax({
                url: Routing.generate('api_client_slots'),
                type: "GET",
                headers: {
                    "Content-Type": "multipart/form-data",
                    "Authorization": "Bearer " + res
                },
            }).then((res) => {
                displaySlots(res);
            }).catch((err) => {
                console.log('Slots error: ', err)
            })
        }).catch((err) => {
            console.log('Token Error: ', err);
            $('#loading-spinner').hide();
        })

        // fetch and display recipients list
        $('#loading-spinner').show();
        $.get(
            Routing.generate('api_get_recipients_list', {area: localStorage.getItem('deliveryArea')})
        ).then((res) => {
            if (res && res.length > 0) {
                let recipientsHTML = [];
                let firstLoop = true;
                res.forEach((el) => {
                    let checked;
                    if (firstLoop) {
                        checked = 'checked';
                        firstLoop = false;
                    } else {
                        checked = '';
                    }
                    recipientsHTML.push(` <div class="checkoutRecipientsList card-body d-flex align-items-center">
                                            <div class="mr-2">
                                                <input type="radio" name="recipient" value="${el.id}" ${checked}>
                                            </div>
                                            <div>
                                                <h4>${el.recieverName}</h4>
                                                <h5>${el.recieverFullAddress}</h5>
                                                <h6>${el.recieverPhone}</h6>
                                            </div>
                                        </div>`);
                });
                $('#checkoutReicpientsList').html(recipientsHTML);
            }
        }).catch((err) => {
            console.log(err)
        }).always(() => {
            $('#loading-spinner').hide();
            $('#checkOutModal').modal('show');
        });

    })

    $(document).on('click', '#checkOutModal .step1 .cart-item .cart-item-delete', function () {
        let currentCart = JSON.parse(localStorage.getItem('cart'));
        let id = $(this).data('id');
        currentCart.forEach((item, index) => {
            if (item.product == id) {
                if (currentCart.length > 0) {
                    currentCart.splice(index, 1);
                } else {
                    currentCart = [];
                }
            }
        });
        localStorage.setItem('cart', JSON.stringify(currentCart));
        refreshCartNotifications();
        refreshCartData();
    });

    $('#checkOutModal .step1 .confirmBtn').on('click', function () {
        $('#checkOutModal .step1').addClass('d-none')
        $('#checkOutModal .step2').removeClass('d-none')
        localStorage.setItem('checkoutStep', '2');
        // convert cart to order
        let cart = JSON.parse(localStorage.getItem('cart'));
        let user = JSON.parse(localStorage.getItem('user'));
        let order = {};
        order.orderOrigin = 'website';
        order.VAT = '5';
        order.hideSender = false;
        order.messageFrom = user['full_name'];
        order.orderDetails = cart.map((product) => {
            return {
                product: product.product,
                quantity: product.quantity
            };
        });

        localStorage.setItem('order', JSON.stringify(order));
    });
    $('#checkOutModal .step2 .confirmBtn').on('click', function () {
        let order = JSON.parse(localStorage.getItem('order'));
        let selectedRecipient = $('#checkoutReicpientsList input[name="recipient"]:checked');
        order.deliveryAddress = selectedRecipient.val();
        let selectedDeliveryDate = $('#checkOutModal .step2 input[name="deliverydate"]').val();
        if (selectedRecipient.length == 0) {
            $('#checkoutStep2FormError').text('Please select a recipient.');
            return false;
        }
        if (selectedDeliveryDate == '') {
            $('#checkoutStep2FormError').text('Please specify the delivery date.');
            return false;
        }
        if (new Date(selectedDeliveryDate).getTime() <= (new Date()).getTime()) {
            $('#checkoutStep2FormError').text('The delivery date must be later than or equal to today.');
            return false;
        }
        order.deliveryDate = selectedDeliveryDate;
        order.deliverySlot = $('#checkOutModal .step2 select[name="slot"] option:selected').val()
        order.message = $('#checkOutModal .step2 textarea[name="cardDetails"]').val();
        order.messageTo = selectedRecipient.parent().parent().find('h4').text();
        selectedMedia = $('#checkoutUploadType').val();
        switch (selectedMedia) {
            case 'link':
                order.messageLink = $('#checkoutUploadTypeLink').val();
                break;
            case 'video':
                if ($('#checkoutUploadTypeFile')[0].files) {
                    messageFile = $('#checkoutUploadTypeFile')[0].files[0];
                }
                break;
            case 'voice':
                messageFile = new File([recordedBlob], "new_recorded.mp3");
                break;
        }
        localStorage.setItem('order', JSON.stringify(order));
        $('#checkOutModal .step2').addClass('d-none')
        $('#checkOutModal .step3').removeClass('d-none')
        localStorage.setItem('checkoutStep', '3');
        localStorage.setItem('paymentMethod', 'masterCard');
        $('#checkoutStep2FormError').text('');
        refreshCartData()
    });

    $('#discountVoucherInput').on('change', function () {
        $('#loading-spinner').show();
        $.get(
            Routing.generate('front_api_check_voucher', {code: $(this).val()})
        ).then((res) => {
            $('#checkoutModalVoucherError').text('');
            $('#checkoutModalVoucherValid').text(`This voucher gives you ${res}% discount.`);
            $('#checkOutModal .step3 .orderVoucherDiscountValue').text(`${res}%`);
        }).catch((err) => {
            $('#checkoutModalVoucherError').text(err.responseJSON);
        }).always(() => {
            $('#loading-spinner').hide();
        })
    })

    $('#checkOutModal .step3 .confirmBtn').on('click', function () {
        let order = JSON.parse(localStorage.getItem('order'));
        let discountVoucher = $('#discountVoucherInput').val();
        if (discountVoucher != '') {
            order.discountVoucher = discountVoucher;
        }
        // create the form data
        let formData = convertOrderToFormData(order);
        // console.log(formData.get('orderDetails'))
        // Send data to backend
        $('#loading-spinner').show();
        $.post(
            Routing.generate('front_api_get_token')
        ).then((res) => {
            localStorage.setItem('token', res);
            $.ajax({
                url: Routing.generate('api_client_add_order'),
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + res
                },
                processData: false,
                contentType: false,
                data: formData
            }).then((res) => {
                let orderId = res.id;
                // Add payment logic here
                tap.createToken(card).then(function (result) {
                    // console.log(result);
                    if (result.error) {
                        // Inform the user if there was an error
                        var errorElement = document.getElementById('error-handler');
                        errorElement.textContent = result.error.message;
                        return false;
                    } else {
                        // Send the token to the server
                        let token = result.id;
                        tap.createToken(card).then(function (result) {
                            // console.log(result);
                            if (result.error) {
                                // Inform the user if there was an error
                                var errorElement = document.getElementById('error-handler');
                                errorElement.textContent = result.error.message;
                                return false;
                            } else {
                                // Send the token to the server
                                $.post({
                                    url: Routing.generate('front_api_set_payment_auth_token', {id: orderId}),
                                    data: {
                                        token: token
                                    }
                                }).then((res) => {
                                    if (res) {
                                        let response = JSON.parse(res.body.contents);
                                        let status = response.response.code;
                                        if (status == 100) {
                                            let transactionUrl = response.transaction.url;
                                            window.location.href = transactionUrl;
                                        } else {
                                            // TODO display error message before submit
                                        }
                                    }
                                }).catch((err) => {
                                    console.log(err)
                                    $('#loading-spinner').hide();
                                })
                            }
                        });


                    }
                });
            }).catch((err) => {
                console.log('Order Error: ' + err)
            }).always(() => {
                $('#loading-spinner').hide();
            })
        }).catch((err) => {
            console.log('Token Error: ' + err);
            $('#loading-spinner').hide();
        })

    });
    $('#checkOutModal .step4 .confirmBtn').on('click', function () {

        $('#loading-spinner').show();
        let orderId = $(this).data('order-id');
        $.post(
            Routing.generate('front_api_get_token')
        ).then((res) => {
            localStorage.setItem('token', res);
            $.ajax({
                url: Routing.generate('front_api_last_order'),
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + res
                },
            }).then((res) => {
                // populate and display model
                let trackItemsHtml = [];
                res['order_details'].forEach((detail) => {
                    trackItemsHtml.push(`<div class="d-flex justify-content-center align-content-between mt-3">
                        <div class="col-3">
                            <img class="w-100" src="${detail.product.images[0]['image_path']}" alt="product image">
                        </div>
                        <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                            <h5>${detail.product.name}</h5>
                            <h6 class="mt-3">${detail.product.price} SAR</h6>
                        </div>
                        <div class="col-1 d-flex justify-content-center align-items-center">
                            <span>${detail.quantity}</span>
                        </div>
                    </div>`);
                });
                $('#trackOrderModal .order-sommary').html(trackItemsHtml);
                let address = res['delivery_address']['reciever_full_address'];
                let totalPrice = res['total_price'] + ' SAR';
                let recipientName = res['message_to'];
                let orderRef = res.reference;
                let deliveryTime = res['delivery_date'] + ' : ' + res['delivery_slot']['delivery_at'] + ' - ' + res['delivery_slot']['delivery_to'];
                $('#trackOrderModalAddress').text(address);
                $('#trackOrderTotalPrice').text(totalPrice);
                $('#trackOrderModalRecipientName').text(recipientName);
                $('#trackOrderModalOrderRef').text(orderRef);
                $('#trackOrderModalDeliveryTime').text(deliveryTime);

                let orderStep = parseInt(res['custom_status_code']);
                $('#trackOrderModal .order-status > span').each(function (index) {
                    let thisStep = parseInt($(this).data('step'));
                    if (orderStep >= thisStep) {
                        $(this).addClass('validated');
                        let image = $(this).find('img');
                        $(image).attr('src', $(image).data('validated-icon'));
                    } else {
                        $(this).removeClass('validated');
                        let image = $(this).find('img');
                        $(image).attr('src', $(image).data('icon'));
                    }
                });
                $('#trackOrderModal .order-status i').each(function (index) {
                    let thisStep = parseInt($(this).data('step'));
                    if (orderStep >= thisStep) {
                        $(this).addClass('validated');
                    } else {
                        $(this).removeClass('validated');
                    }
                });

                $('#checkOutModal').modal('hide');
                setTimeout(function () {
                    $('#trackOrderModal').modal('show');
                }, 500)
            }).catch((err) => {
                console.log('Order Error: ' + err)
            }).always(() => {
                $('#loading-spinner').hide();
            })
        }).catch((err) => {
            console.log('Token Error: ' + err);
            $('#loading-spinner').hide();
        })

    });
    $('#checkOutModal .step2 .backBtn').on('click', function () {
        $('#checkOutModal .step1').removeClass('d-none')
        $('#checkOutModal .step2').addClass('d-none')
        localStorage.setItem('checkoutStep', '1');
    });
    $('#checkOutModal .step3 .backBtn').on('click', function () {
        $('#checkOutModal .step2').removeClass('d-none')
        $('#checkOutModal .step3').addClass('d-none')
        localStorage.setItem('checkoutStep', '2');
    });

    $('#checkOutModal .addLinkBtn').on('click', function () {
        $('#checkoutUploadType').val('link');
        $('#checkoutUploadTypeLink').removeClass('d-none');
        $('#checkoutUploadTypeFile').addClass('d-none');
        $('#checkoutUploadTypeVoice').addClass('d-none');
        $('#checkoutUploadTypeVoicePreview').addClass('d-none');
    });

    $('#checkOutModal .addVideoBtn').on('click', function () {
        $('#checkoutUploadType').val('video');
        $('#checkoutUploadTypeLink').addClass('d-none');
        $('#checkoutUploadTypeFile').removeClass('d-none');
        $('#checkoutUploadTypeVoice').addClass('d-none');
        $('#checkoutUploadTypeVoicePreview').addClass('d-none');
    });

    $('#checkOutModal .addVoiceBtn').on('click', function () {
        $('#checkoutUploadType').val('voice');
        $('#checkoutUploadTypeLink').addClass('d-none');
        $('#checkoutUploadTypeFile').addClass('d-none');
        $('#checkoutUploadTypeVoice').removeClass('d-none');
        $('#checkoutUploadTypeVoicePreview').removeClass('d-none');
    });

    $('#checkOutModal .recordBtn').on('click', function () {
        let text = $(this).text();
        if (text == 'Start Recording') {
            handleAction('start');
            $(this).text('Stop Recording');
            $(this).siblings('span').text(`Recording`)
        } else {
            handleAction('stop');
            $(this).text('Start Recording');
            $(this).siblings('span').html(` <button class="btn recordBtn text-success" >Play Recorded Audio</button>`)
        }
    });
    $(document).on('click', '#checkoutUploadTypeVoicePreview button', function () {
        handleAction('play')
    })

    $('#checkoutPaymentMethodSelect a').on('click', function (e) {
        e.preventDefault();
        $('#checkoutPaymentMethod button span').text($(this).text());
        localStorage.setItem('paymentMethod', $(this).data('value'));
    })

    // tap form treatment

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

    refreshCartNotifications();
})

function convertOrderToFormData(order) {
    let formData = new FormData();
    for (let param in order) {
        if (typeof order[param] == 'object') {
            for (let i = 0; i < order[param].length; i++) {
                for (let key in order[param][i]) {
                    formData.append(`${param}[${i}][${key}]`, order[param][i][key])
                }
            }
        } else {
            formData.append(param, order[param]);
        }
    }
    if (selectedMedia == 'audio' || selectedMedia == 'voice') {
        formData.append('messageFile', messageFile);
    }
    return formData;
}

function displaySlots(res) {
    let slotsHTML = [];
    res.forEach((el) => {
        let title = el['delivery_at'] + ' - ' + el['delivery_to'];
        slotsHTML.push(`<option value="${el.id}">${title}</option>`);
    });
    $('#checkoutDeliverySlots').html(slotsHTML);
}

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

        // update the summary data
        $('#checkOutModal .step3 .orderSubTotalPrice').text(totalPrice + ' SAR');
        $('#checkOutModal .step3 .orderTaxPrice').text(parseFloat(taxValue) + ' SAR');
        $('#checkOutModal .step3 .orderDeliveryPrice').text(deliveryPrice > 0 ? (deliveryPrice + ' SAR') : 'Free');
        $('#checkOutModal .step3 .orderVoucherDiscountValue').text();
        $('#checkOutModal .step3 .orderTotalPrice').text((totalPrice + taxValue + deliveryPrice) + ' SAR');
    } else {
        $('#checkOutModal .step1 .modal-body').html('');
        $('#checkOutModal .step1 .modal-footer-container').hide();
    }
    $('#checkOutModal .step1 .itemsCount').text(items);
}

function refreshCartNotifications() {
    let currentCart = JSON.parse(localStorage.getItem('cart'));
    let items = currentCart ? currentCart.length : 0;
    $('.notifications').text(items);
    $('.notifications-mobile').text(items);
    $('.notifications').removeClass('d-none');
    $('.notifications-mobile').removeClass('d-none');
    if (!currentCart || items == 0) {
        $('.notifications').addClass('d-none');
        $('.notifications-mobile').addClass('d-none');
    }
}

const recordAudio = () =>
    new Promise(async resolve => {
        const stream = await navigator.mediaDevices.getUserMedia({audio: true});
        const mediaRecorder = new MediaRecorder(stream);
        const audioChunks = [];

        mediaRecorder.addEventListener("dataavailable", event => {
            audioChunks.push(event.data);
        });

        const start = () => mediaRecorder.start();

        const stop = () =>
            new Promise(resolve => {
                mediaRecorder.addEventListener("stop", () => {
                    const audioBlob = new Blob(audioChunks);
                    const audioUrl = URL.createObjectURL(audioBlob);
                    const audio = new Audio(audioUrl);
                    const play = () => audio.play();
                    recordedBlob = audioBlob;
                    resolve({audioBlob, audioUrl, play});
                });

                mediaRecorder.stop();
            });

        resolve({start, stop});
    });

const handleAction = async (action) => {
    if (action == 'start') {
        recorder = await recordAudio();
        recorder.start();
    } else if (action == 'stop') {
        audio = await recorder.stop();
    } else if (action == 'play') {
        audio.play();
    }
};
//
