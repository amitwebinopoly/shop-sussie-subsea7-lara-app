var Script = function () {

    $.validator.setDefaults({
        submitHandler: function () {
//            $("#register_form").submit();
        }
    });

    $().ready(function () {

        // validate email form on keyup and submit
        $("#checkout-frm").validate({
            rules: {
                checkout_email: {
                    required: true,
                    email: true
                },
                shipping_first_name: {
                    required: true,
                },
                shipping_last_name: {
                    required: true,
                },
                shipping_address: {
                    required: true,
                },
                shipping_city: {
                    required: true,
                },
                shipping_country: {
                    required: true,
                },
                card_number: {
                    required: true,
                },
                expiry: {
                    required: true,
                },
                cvc: {
                    required: true,
                },
                card_name: {
                    required: true,
                },
                phone: {
                    required: true,
                }
            },
            submitHandler: function () {
                $("#checkout-frm").submit();
            }
        });


    });


}();