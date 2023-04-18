<script>
    $('.Polaris-Banner--hasDismiss').fadeOut(2000, function() {
        $('.Polaris-Banner--hasDismiss').remove();
    });
</script>
<textarea style="display: none;" id="item_arr_for_calc"><?= (isset($item_arr_for_calc) && !empty($item_arr_for_calc))?json_encode($item_arr_for_calc, 1):"{}" ?></textarea>

<link rel="stylesheet" href="{{$checkout_assets_url}}/inex-modal/inex-modal.css" />
<script src="{{$checkout_assets_url}}/inex-modal/inex-modal.js" crossorigin="anonymous"></script>
<script src="{{$checkout_assets_url}}/js/jquery.mask.min.js" crossorigin="anonymous"></script>

<?php
$enable_address_autocompletion = isset($cs_data->cs_enable_address_autocompletion)?$cs_data->cs_enable_address_autocompletion:"";
$google_api_key = isset($cs_data->cs_google_api_key)?$cs_data->cs_google_api_key:"";
if ($enable_address_autocompletion == true && !empty($google_api_key)) { ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&libraries=places&v=weekly">
</script>
<script>
    function getCountryValByCountryName(countryName, countryElem) {
        if (countryElem) {
            let countryOptions = countryElem.options;
            let country = '';
            if (countryOptions) {
                countryOptions = Array.prototype.slice.call(countryOptions);
                if (countryOptions.length > 0) {
                    country = countryOptions.filter((country) => {
                        return country.text === countryName;
                    })
                }
            }
            if (country.length > 0) {
                return country[0].value;
            }
        }
    }

    function autoCompleteAddress(lookup, address_line1_id) {
        let placeSearch, autocomplete;
        let componentForm = {
            street_number: 'short_name',
            route: 'long_name',
            locality: 'long_name',
            neighborhood: 'long_name',
            administrative_area_level_1: 'short_name',
            country: 'long_name',
            postal_code: 'short_name'

        };

        document.getElementById(address_line1_id).onFocus = function(e) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    let geolocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    let circle = new google.maps.Circle({
                        center: geolocation,
                        radius: position.coords.accuracy
                    });
                    autocomplete.setBounds(circle.getBounds());
                });
            }
        };
        autocomplete = new google.maps.places.Autocomplete(document.getElementById(address_line1_id), {
            types: ['geocode'],
            componentRestrictions: {country: "us"}
        });
        const myIntervalPlace = setInterval(function(){
            if(document.getElementById(address_line1_id).getAttribute('placeholder') != ""){
                document.getElementById(address_line1_id).setAttribute('placeholder',"");
                clearInterval(myIntervalPlace);
            }
        }, 1000);

        // Set the data fields to return when the user selects a place.
        autocomplete.setFields(['address_components', 'geometry', 'icon', 'name']);
        autocomplete.addListener('place_changed', function(e) {
            let place = autocomplete.getPlace();

            let address = '',
                    address2 = '',
                    city = '',
                    state = '',
                    country = '',
                    zip = '';

            for (let component in componentForm) {
                lookup[component].value = '';
            }

            if (typeof place === 'object') {
                if (place.address_components) {
                    let fullAddress = '';
                    for (var i = 0; i < place.address_components.length; i++) {
                        let addressType = place.address_components[i].types[0];
                        let val = place.address_components[i][componentForm[addressType]];

                        if (componentForm[addressType]) {
                            switch (addressType) {
                                case 'street_number':
                                    fullAddress = val + fullAddress;
                                    break;
                                case 'route':
                                    fullAddress = fullAddress + ' ';
                                    fullAddress = fullAddress + val;
                                    break;
                                case 'neighborhood':
                                    lookup.neighborhood.value = val;
                                    city = val;
                                    break;
                                case 'locality':
                                    lookup.locality.value = val;
                                    city = val;
                                    break;

                                case 'administrative_area_level_1':
                                    lookup.administrative_area_level_1.value = val;
                                    state = val;
                                    break;
                                    <?php if (!isset($_POST['cust_def_addr']['country_code']) || empty($_POST['cust_def_addr']['country_code'])) { ?>
                                case 'country':
                                    lookup.country.value = (val.length > 2 ? getCountryValByCountryName(val, lookup
                                            .country) : val);
                                    break;
                                    <?php } ?>
                                case 'postal_code':
                                    lookup.postal_code.value = val;
                                    zip = val;
                                    break;

                            }
                        }
                    }
                    address = fullAddress;
                    lookup.fullAddress.value = fullAddress;

                } else if (place.name) {
                    if (place.name != '') {
                        let fullAddress = place.name.split(', ');
                        if (fullAddress[3]) {
                            fullAddress[3] = (fullAddress[3].length > 2 ? getCountryValByCountryName(fullAddress[
                                    3], lookup.country) : fullAddress[3]);
                        }
                        lookup.fullAddress.value = fullAddress[0] ? fullAddress[0] : '';
                        lookup.neighborhood.value = fullAddress[1] ? fullAddress[1] : '';
                        lookup.locality.value = fullAddress[1] ? fullAddress[1] : '';
                        lookup.administrative_area_level_1.value = fullAddress[2] ? fullAddress[2] : '';
                        lookup.country.value = fullAddress[3] ? fullAddress[3] : '';
                    }
                }
            }

            $("#shipping_address, #shipping_address2, #shipping_city, #shipping_pincode").blur();
        });
    }
    $(document).ready(function() {
        let shippingLookup = {
            "street_number": document.getElementById('shipping_address'),
            "route": document.getElementById('shipping_address2'),
            "fullAddress": document.getElementById('shipping_address'),
            "locality": document.getElementById('shipping_city'),
            "neighborhood": document.getElementById('shipping_city'),
            "administrative_area_level_1": document.getElementById('shipping_state'),
            "country": document.getElementById('shipping_country'),
            "postal_code": document.getElementById('shipping_pincode')
        };

        autoCompleteAddress(shippingLookup, 'shipping_address');
        let billingLookup = {
            "street_number": document.getElementById('billing_address1'),
            "route": document.getElementById('billing_address2'),
            "fullAddress": document.getElementById('billing_address1'),
            "locality": document.getElementById('billing_city'),
            "neighborhood": document.getElementById('billing_city'),
            "administrative_area_level_1": document.getElementById('billing_state'),
            "country": document.getElementById('billing_country'),
            "postal_code": document.getElementById('billing_pincode')
        };
        autoCompleteAddress(billingLookup, 'billing_address1');
    });
</script>
<?php } ?>

<script type="text/javascript">
    var vendor_shipping_error_msg = 'Please update your state. We do not ship to the state provided.';

    $(document).ready(function() {
        $xhr = '';
        google_ana_page_load(1, '');
        fillStateDropdown();
        fillBillingStateDropdown('');

        <?php if (isset($ac_data->ac_step) && $ac_data->ac_step=='SHIP_METHOD') { ?>
        $("#continue_to_ship_method_btn").click();
        <?php } elseif (isset($ac_data->ac_step) && $ac_data->ac_step=='PAY_INFO') { ?>
        $("#continue_to_ship_method_btn").click();
        setShippingAddress();
        var interval = setInterval(function() {
            //every 2 second, we are checking that shipping methods are set or not, when it will set, we trigger pay-button click
            goto_payment_section(interval);
        }, 2000);
        <?php } else { ?>
        createFirstCheckout();
        <?php }?>

    });

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    $("#continue_to_ship_method_btn").click(async function(event) {
        if (validateCustomerInfo()) {
            if($(window).width() < 992){
                if ($("#sidebar_section").is(':visible')) {
                    $('#sidebar_section').hide();
                    $("#show_summary_text").css('display','table-cell');
                    $("#hide_summary_text").hide();
                }
            }

            $("#continue_to_ship_method_btn").attr('disabled', true);
            calculate_prices('move_to_step2');
        }
    });

    function goto_payment_section(interval) {
        if ($('.shipping_radio').length > 0) {

            $("#cont_to_pay_method").removeAttr('disabled');
            $("#cont_to_pay_method").html('Continue to payment method'); // nik 05/25/2021
            $("#cont_to_pay_method").click();
            clearInterval(interval);
        }
    }

    function fillStateDropdown(country = '') {
        country = (country != '') ? country : $('#shipping_country').val();
        var $countryOption = $('#shipping_country option[value="' + country + '"]');
        var states = $countryOption.data('provinces');
        var countryTax = $countryOption.data('tax');
        var state = '';
        if (states != '') {
            $('#shipping_state').find('option').remove();
            //$('#shipping_state_div').show();
            //$('#shipping_pincode_div').show();
            $('#shipping_state').attr('disabled', false);
            $('#shipping_pincode').attr('disabled', false);
            //$('#shipping_country_div').addClass('col-3');

            var default_state = '<?php echo isset($_POST['cust_def_addr']['province_code'])?$_POST['cust_def_addr']['province_code']:"" ?>';
            $.each(states, function(i, state) {
                var $myopt = $("<option></option>");
                $myopt.attr("value", state.code);
                if (default_state == state.code || default_state == state.name) {
                    $myopt.attr("selected", "selected");
                }
                $myopt.attr("data-tax", state.tax);
                $myopt.attr("data-taxname", state.tax_name);
                $myopt.attr("data-taxpercent", state.tax_percentage);
                $myopt.attr("data-taxtype", state.tax_type);
                $myopt.text(state.name);
                $('#shipping_state').append($myopt);
                if (i == 0) {
                    state = state.code;
                }
            });
        } else {
            $('#shipping_state').html('');
            //$('#shipping_state_div').hide();
            $('#shipping_state').attr('disabled', true);
            //$('#shipping_pincode_div').hide();
            //$('#shipping_pincode').attr('disabled', true);
            //$('#shipping_country_div').removeClass('col-3');
        }
    }

    function validateEmail(mail) {
        //var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,10})+$/;
        var mailformat = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/;
        if (mail.match(mailformat)) {
            return true;
        } else {
            return false;
        }
    }

    function validateCustomerInfo() {
        var errCnt = 0;
        $('.form-group .error-msg').remove();
        $('.form-group').removeClass('field--error');

        var $email = $('#checkout_email');
        var $phone = $('#phone');

        if ($email.val() == "") {
            $('<p class="error-msg">Please enter an email</p>').insertAfter($email.parent('label'));
            $email.parents('.form-group').addClass('field--error');
            errCnt++;
        } else if (!validateEmail($email.val())) {
            $('<p class="error-msg">Please enter a valid email</p>').insertAfter($email.parent('label'));
            $email.parents('.form-group').addClass('field--error');
            errCnt++;
        }

        <?php if (isset($cs_data->cs_ship_phone_require) && $cs_data->cs_ship_phone_require=='required') { ?>
        if ($phone.val() == "") {
            $('<p class="error-msg">Please enter a valid phone number</p>').insertAfter($phone.parent('label'));
            $phone.parents('.form-group').addClass('field--error');
            errCnt++;
        }
        <?php }?>

        var $sfname = $('#shipping_first_name');
        var $slname = $('#shipping_last_name');
        var $saddress = $('#shipping_address');
        var $saddress2 = $('#shipping_address2');
        var $scity = $('#shipping_city');
        var $scountry = $('#shipping_country');
        var $sstate = $('#shipping_state');
        var $szip = $('#shipping_pincode');
        <?php if (isset($cs_data->cs_first_name_require) && $cs_data->cs_first_name_require=='required') { ?>
        if ($sfname.val() == "") {
            $('<p class="error-msg">Please enter your first name</p>').insertAfter($sfname.parent('label'));
            $sfname.parents('.form-group').addClass('field--error');
            errCnt++;
        }
        <?php }?>

        if ($slname.val() == "") {
            $('<p class="error-msg">Please enter your last name</p>').insertAfter($slname.parent('label'));
            $slname.parents('.form-group').addClass('field--error');
            errCnt++;
        }

        if ($saddress.val() == "") {
            $('<p class="error-msg">Please enter an address</p>').insertAfter($saddress.parent('label'));
            $saddress.parents('.form-group').addClass('field--error');
            errCnt++;
        }

        <?php if (isset($cs_data->cs_address2_require) && $cs_data->cs_address2_require=='required') { ?>
        if ($saddress2.val() == "") {
            $('<p class="error-msg">Please enter an Apartment, suite, etc</p>').insertAfter($saddress2.parent('label'));
            $saddress2.parents('.form-group').addClass('field--error');
            errCnt++;
        }
        <?php }?>

        if ($scity.val() == "") {
            $('<p class="error-msg">Please enter a city</p>').insertAfter($scity.parent('label'));
            $scity.parents('.form-group').addClass('field--error');
            errCnt++;
        }

        if ($scountry.val() == "") {
            $('<p class="error-msg">Please select a country</p>').insertAfter($scountry.parent('label'));
            $scountry.parents('.form-group').addClass('field--error');
            errCnt++;
        }

        if ($('#shipping_state option').length > 0) {
            if ($sstate.val() == "") {
                $('<p class="error-msg">Please select a state</p>').insertAfter($sstate.parent('label'));
                $sstate.parents('.form-group').addClass('field--error');
                errCnt++;
            }
        }
        if ($szip.val() == "") {
            $('<p class="error-msg">Please enter a ZIP code</p>').insertAfter($szip.parent('label'));
            $szip.parents('.form-group').addClass('field--error');
            errCnt++;
        }


        if (errCnt == 0) {
            return true;
        }
    }

    var decodeEntities = (function() {
        // this prevents any overhead from creating the object each time
        var element = document.createElement('div');

        function decodeHTMLEntities(str) {
            if (str && typeof str === 'string') {
                // strip script/html tags
                str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
                str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
                element.innerHTML = str;
                str = element.textContent;
                element.textContent = '';
            }

            return str;
        }
        return decodeHTMLEntities;
    })();

    $("#cont_to_pay_method").click(function() {
        if (validateCustomerInfo()) {
            google_ana_page_load(3, '');
            openCity(document.getElementById('cont_to_pay_method'), 'tab-3');
            store_abondoned_step_2();
            document.getElementById('tab-3-open').click();
        }
    });

    function setShippingAddress() {
        var fname = $('#shipping_first_name').val();
        var lname = $('#shipping_last_name').val();
        var shipping_address = $('#shipping_address').val();
        var shipping_address2 = $('#shipping_address2').val();
        var shipping_city = $('#shipping_city').val();
        var country = $('#shipping_country').val();
        var shipping_state = $('#shipping_state').val() == null ? '' : $('#shipping_state').val();
        var shipping_pincode = $('#shipping_pincode').val();
        var $countryOption = $('#shipping_country option[value="' + country + '"]');
        var shipping_country = $countryOption.data('name');
        var shipaddress = '';
        if (shipping_address != '') {
            shipaddress = shipping_address + ', ';
        }
        if (shipping_address2 != '') {
            shipaddress += shipping_address2 + ', ';
        }
        if (shipping_city != '') {
            shipaddress += ' ' + shipping_city;
        }
        if (shipping_state != '') {
            shipaddress += ' ' + shipping_state;
        }
        if (shipping_pincode != '') {
            shipaddress += ' ' + shipping_pincode;
        }
        if (shipping_country != '') {
            shipaddress += ', ' + shipping_country;
        }
        $('.shipping_address_div').html(shipaddress);

        $('.shipping_email_div').html($('#checkout_email').val());
    }
    $('#tab-2-open').click(function() {
        if (validateCustomerInfo()) {
            setShippingAddress();
            setShippingAddToBill();
            $('.shipping_radio').trigger('change');
        }
    });
    $('#tab-3-open').click(function() {
        if (validateCustomerInfo()) {
            setShippingAddress();
            setShippingAddToBill();
            $('.shipping_radio').trigger('change');
        }
    });
    $('#shipping_country').change(function() {
        var country = $(this).val();
        fillStateDropdown(country);
    });

    function fillBillingStateDropdown(country = '') {
        country = (country != '') ? country : $('#billing_country').val();
        var $countryOption = $('#billing_country option[value="' + country + '"]');
        var states = $countryOption.data('provinces');
        var countryTax = $countryOption.data('tax');
        var state = '';
        if (states != '') {
            $('#billing_state').find('option').remove();
            $('#billing_state_div').show();
            $('#billing_pincode_div').show();
            $('#billing_state').attr('disabled', false);
            $('#billing_pincode').attr('disabled', false);
            //$('#billing_country_div').addClass('col-3');
            $.each(states, function(i, state) {
                var $myopt = $('<option></option>');
                $myopt.attr('value', state.code);
                $myopt.attr('data-tax', state.tax);
                $myopt.attr('data-taxname', state.tax_name);
                $myopt.attr('data-taxpercent', state.tax_percentage);
                $myopt.attr('data-taxtype', state.tax_type);
                $myopt.text(state.name);
                $('#billing_state').append($myopt);
                if (i == 0) {
                    state = state.code;
                }
            });
        } else {
            $('#billing_state_div').hide();
            $('#billing_pincode_div').hide();
            $('#billing_state').attr('disabled', true);
            $('#billing_pincode').attr('disabled', true);
            //$('#billing_country_div').removeClass('col-3');
        }
    }

    $('#billing_country').change(function() {
        var country = $(this).val();
        fillBillingStateDropdown(country);
    });

    //calculate shipping
    $(document).on('change', '.shipping_radio', function() {
        var $shipping = $('input[name=shipping_charge]:checked', '#checkout-frm');
        var shipping_lines = {};
        shipping_lines["id"] = $shipping.data('id');
        shipping_lines["title"] = $shipping.data('name');
        shipping_lines["price"] = $shipping.data('price');
        shipping_lines["code"] = $shipping.data('name');
        shipping_lines["source"] = 'shopify';
        var shipping_linesjson = JSON.stringify(shipping_lines);
        $('#shipping_lines').val(shipping_linesjson);
        calculate_prices('on_ship_change');
    });

    function openCity(event, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(cityName).style.display = "block";
        //event.currentTarget.className += " active";

        if (cityName == 'tab-1') {
            <?php if (isset($sv_data[0]['sv_shipping_content_display_in_step_1']) && $sv_data[0]['sv_shipping_content_display_in_step_1']=='Yes') { ?>
            $("#main_section_shipping_content").show();
            <?php } else { ?>
            $("#main_section_shipping_content").hide();
            <?php }?>

            <?php if (isset($sv_data[0]['sv_additional_content_section_display_in_step_1']) && $sv_data[0]['sv_additional_content_section_display_in_step_1']=='Yes') { ?>
            $("#main_section_additional_content_section").show();
            <?php } else { ?>
            $("#main_section_additional_content_section").hide();
            <?php }?>
        } else if (cityName == 'tab-2') {
            <?php if (isset($sv_data[0]['sv_shipping_content_display_in_step_2']) && $sv_data[0]['sv_shipping_content_display_in_step_2']=='Yes') { ?>
            $("#main_section_shipping_content").show();
            <?php } else { ?>
            $("#main_section_shipping_content").hide();
            <?php }?>

            <?php if (isset($sv_data[0]['sv_additional_content_section_display_in_step_2']) && $sv_data[0]['sv_additional_content_section_display_in_step_2']=='Yes') { ?>
            $("#main_section_additional_content_section").show();
            <?php } else { ?>
            $("#main_section_additional_content_section").hide();
            <?php }?>
        } else if (cityName == 'tab-3') {
            <?php if (isset($sv_data[0]['sv_shipping_content_display_in_step_3']) && $sv_data[0]['sv_shipping_content_display_in_step_3']=='Yes') { ?>
            $("#main_section_shipping_content").show();
            <?php } else { ?>
            $("#main_section_shipping_content").hide();
            <?php }?>

            <?php if (isset($sv_data[0]['sv_additional_content_section_display_in_step_3']) && $sv_data[0]['sv_additional_content_section_display_in_step_3']=='Yes') { ?>
            $("#main_section_additional_content_section").show();
            <?php } else { ?>
            $("#main_section_additional_content_section").hide();
            <?php }?>
        }
    }

    function setShippingAddToBill() {
        if ($("#billing-1").is(':checked')) {
            $('#billing_first_name').val($('#shipping_first_name').val());
            $('#billing_last_name').val($('#shipping_last_name').val());
            $('#billing_phone').val($('#phone').val());
            $('#billing_company').val($('#shipping_company').val());
            $('#billing_address1').val($('#shipping_address').val());
            $('#billing_address2').val($('#shipping_address2').val());
            $('#billing_city').val($('#shipping_city').val());
            $('#billing_country').val($('#shipping_country').val()).trigger('change');
            $('#billing_state').val($('#shipping_state').val() == null ? '' : $('#shipping_state').val());
            $('#billing_pincode').val($('#shipping_pincode').val());
        }
    }

    if (document.getElementById("defaultOpen")) {
        document.getElementById("defaultOpen").click();
    }

    $('.billingadd').change(function(e) {
        var value = $(this).val();
        if (value == 'bill') {
            $('.billing-address2 input').val('');
            $('.billing-address2').slideDown('slow');
        } else {
            $('.billing-address2').slideUp();
            setShippingAddToBill();
        }
        e.stopPropagation();
        e.preventDefault();
    });
    $(".order-summary-toggle").on('click', function(e) {
        $('#sidebar_section').toggle();
        if ($("#sidebar_section").is(':visible')) {
            $("#show_summary_text").hide();
            $("#hide_summary_text").css('display','table-cell');
        }else{
            $("#show_summary_text").css('display','table-cell');
            $("#hide_summary_text").hide();
        }
        e.stopPropagation();
        e.preventDefault();
    });

    $("#discount_code, #discount_code_mobile").on('keydown keypress keyup focusout focus blur change', function(e) {
        //var value = $(this).val();
        if ($(this).val().length > 0) {
            $('#discount_code_btn').removeClass('btn--disabled').attr('disabled', false);
            $('#discount_code_btn_mobile').removeClass('btn--disabled').attr('disabled', false);
        } else {
            $('#discount_code_btn').addClass('btn--disabled').attr('disabled', true);
            $('#discount_code_btn_mobile').addClass('btn--disabled').attr('disabled', true);
        }
    });
    $("#giftcard_code").on('keydown keypress keyup focusout focus blur change', function(e) {
        //var value = $(this).val();
        if ($(this).val().length > 0) {
            $('#giftcard_code_btn').removeClass('btn--disabled').attr('disabled', false);
        } else {
            $('#giftcard_code_btn').addClass('btn--disabled').attr('disabled', true);
        }
    });
    $("#discount_code_btn").click(function(e) {
        var discount_code = $('#discount_code').val().toLowerCase();
        var url = '/{{$proxy_path}}/api/apply_discount_in_checkout';
        var shop = $('#shop_url').val();
        var checkout_id = $('#checkout_id').val();
        if (discount_code != '') {
            $("#discount_code_btn").attr('disabled', true);
            $("#discount_code_btn span").hide();
            $("#discount_code_btn i").show();
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    discount_code: discount_code,
                    shop: shop,
                    checkout_id: checkout_id
                },
                success: function(data) {
                    $("#discount_code_btn").removeAttr('disabled');
                    $("#discount_code_btn span").show();
                    $("#discount_code_btn i").hide();

                    if (data.success == 'false') {
                        $('#discount-msg').html(data.message).show();
                        $('#discount-msg-mobile').html(data.message).show();
                    } else {
                        $('#discount-msg').html('').hide();
                        $('#discount-msg-mobile').html('').hide();
                        get_checkout_data();
                    }
                }
            });
        }
    });
    $("#discount_code_btn_mobile").click(function(e) {
        var discount_code = $('#discount_code_mobile').val().toLowerCase();
        var url = '/{{$proxy_path}}/api/apply_discount_in_checkout';
        var shop = $('#shop_url').val();
        var checkout_id = $('#checkout_id').val();
        if (discount_code != '') {
            $("#discount_code_btn_mobile").attr('disabled', true);
            $("#discount_code_btn_mobile span").hide();
            $("#discount_code_btn_mobile i").show();
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    discount_code: discount_code,
                    shop: shop,
                    checkout_id: checkout_id
                },
                success: function(data) {
                    $("#discount_code_btn_mobile").removeAttr('disabled');
                    $("#discount_code_btn_mobile span").show();
                    $("#discount_code_btn_mobile i").hide();

                    if (data.success == 'false') {
                        $('#discount-msg').html(data.message).show();
                        $('#discount-msg-mobile').html(data.message).show();
                    } else {
                        $('#discount-msg').html('').hide();
                        $('#discount-msg-mobile').html('').hide();
                        get_checkout_data();
                    }
                }
            });
        }
    });
    $('#remove-discount, #remove-discount-tag, #remove-discount-tag-mobile').click(function() {
        $('#discount_code').val('').removeAttr('readonly');
        $('#discount_code_mobile').val('').removeAttr('readonly');
        $('#cart_discount_div').text('');
        $('#applied-reduction-code').text('');
        $('#reduction_code_text').html('');
        $('#reduction_code_text_mobile').html('');
        $('#reduction_code_div').hide();
        $('#reduction_code_div_mobile').hide();

        $("#main_div_discount_code").show();
        $('#cart_discount_tr').hide();
        var url = '/{{$proxy_path}}/api/remove_discount_in_checkout';
        var shop = $('#shop_url').val();
        var checkout_id = $("#checkout_id").val().trim();
        if (checkout_id != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    checkout_id: checkout_id,
                    shop: shop
                },
                success: function(result) {
                    var obj = result;
                    if (obj.success == 'true') {
                        get_checkout_data();
                    }
                }
            });
        }
    });

    $("#giftcard_code_btn").click(function(e) {
        var giftcard_code = $('#giftcard_code').val().toLowerCase();
        var url = SITEURL + '/controller.php';
        var shop = $('#shop_url').val();
        var checkout_id = $('#checkout_id').val();
        if (giftcard_code != '') {
            $("#giftcard_code_btn").attr('disabled', true);
            $("#giftcard_code_btn span").hide();
            $("#giftcard_code_btn i").show();
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    giftcard_code: giftcard_code,
                    shop: shop,
                    checkout_id: checkout_id,
                    action: 'apply_giftcard_in_checkout'
                },
                success: function(data) {
                    $("#giftcard_code_btn").removeAttr('disabled');
                    $("#giftcard_code_btn span").show();
                    $("#giftcard_code_btn i").hide();
                    if (data.success == 'false') {
                        $('#giftcard-msg').html(data.message).show();
                    } else {
                        $('#giftcard-msg').html('').hide();
                        $("#main_div_discount_code").hide();
                        get_checkout_data();
                    }
                }
            });
        }
    });
    $('#remove-giftcard').click(function() {
        $('#giftcard_code').val('').removeAttr('readonly');
        $('#cart_giftcard_div').text('');
        $('#applied-giftcard-code').text('');
        $("#main_div_discount_code").show();
        $('#cart_giftcard_tr').hide();

        var url = SITEURL + '/controller.php';
        var shop = $('#shop_url').val();
        var giftcard_id = $("#remove-giftcard").data('giftcard_id');
        var checkout_id = $("#checkout_id").val().trim();
        if (checkout_id != '' && giftcard_id != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    checkout_id: checkout_id,
                    giftcard_id: giftcard_id,
                    shop: shop,
                    action: 'remove_giftcard_in_checkout'
                },
                success: function(result) {
                    var obj = result;
                    if (obj.success == 'true') {
                        $("#remove-giftcard").data('giftcard_id', '').attr('data-giftcard_id', '');
                        get_checkout_data();
                    }
                }
            });
        }
    });

    $('.uni-form-input input').on('keydown keypress keyup focusout focus blur change', function(e) {
        var value = $(this).val();
        if ($(this).val().length > 0) {
            $(this).attr('data-empty', 'false');
        } else {
            $(this).attr('data-empty', 'true');
        }
    });

    function createFirstCheckout() {
        var cart = $('input[name="cart"]').val().trim();
        var note = $('input[name=note]').val().trim();

        var cartItems = $("#item_arr_for_calc").val();

        var url = '/{{$proxy_path}}/api/get_data_first_checkout';
        var shop = $('#shop_url').val();
        var checkout_email = $('#checkout_email').val();

        var discount_code = '';
        <?php if (isset($need_to_display_promocode_section) && $need_to_display_promocode_section == 'Yes') { ?>
        discount_code = getCookie('discount_code');
        <?php }?>

        $("#cont_to_pay_method").attr('disabled', true);
        $("#cont_to_pay_method").html('Please wait...');
        $("#shpping_rates_loader").show();
        $("#shpping_rates_div").hide();
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: {
                cartItems: cartItems,
                cart: cart,
                shop: shop,
                checkout_email: checkout_email,
                discount_code: discount_code
            },
            success: function(result) {
                $("#cont_to_pay_method").removeAttr('disabled');
                $("#cont_to_pay_method").html('Continue to payment'); // nik 05/25/2021
                $("#shpping_rates_loader").hide();
                $("#shpping_rates_div").show();

                var obj = result;
                if (obj.success == 'true') {
                    $("#vendor_shipping_error_div").hide();
                    if (obj.checkout_id != undefined && obj.checkout_id != '') {
                        $('#checkout_id').val(obj.checkout_id);
                    }
                    if (discount_code != '') {
                        document.cookie = "discount_code=;path=/;";
                    }
                    if ($("#shipping_pincode").val() != '') {
                        calculate_prices();
                    } else {
                        get_checkout_data();
                    }
                } else {
                    $("#vendor_shipping_error_div").show();
                    $("#vendor_shipping_error_msg").html(decodeEntities(obj.message));
                    $('html, body').animate({
                        scrollTop: $("#vendor_shipping_error_div").offset().top
                    }, 500);
                }
            }
        });
    }

    async function calculate_prices(trigger_mode='') {
        var shipping_email = $('#checkout_email').val().trim();
        var shipping_phone = $('#phone').val().trim();
        var shipping_first_name = $('#shipping_first_name').val().trim();
        var shipping_last_name = $('#shipping_last_name').val().trim();
        var shipping_address = $('#shipping_address').val().trim();
        var shipping_address2 = $('#shipping_address2').val().trim();
        var shipping_city = $('#shipping_city').val().trim();
        var shipping_country = $("#shipping_country").val();
        var shipping_state = $("#shipping_state").val();
        var shipping_pincode = $("#shipping_pincode").val();

        var billing_first_name = $('#billing_first_name').val().trim();
        var billing_last_name = $('#billing_last_name').val().trim();
        var billing_company = $('#billing_company').val().trim();
        var billing_phone = $('#billing_phone').val().trim();
        var billing_address = $('#billing_address1').val().trim();
        var billing_address2 = $('#billing_address2').val().trim();
        var billing_city = $('#billing_city').val().trim();
        var billing_country = $("#billing_country").val();
        var billing_state = $("#billing_state").val();
        var billing_pincode = $("#billing_pincode").val();

        var customer_id = $('#customer_id').val().trim();
        var checkout_id = $('#checkout_id').val().trim();
        var sms_multi_shipping_lines = $('#sms_multi_shipping_lines').val().trim();
        var cart = $('input[name="cart"]').val().trim();

        var cartItems = $("#item_arr_for_calc").val();

        var shipping_price = '';
        var shipping_title = '';
        var shipping_handle = '';
        if ($(".shipping_radio").length > 0) {
            shipping_price = $(".shipping_radio:checked").data('price');
            shipping_title = $(".shipping_radio:checked").data('name');
            shipping_handle = $(".shipping_radio:checked").data('id');
        }

        var discount_code = '';
        <?php if (isset($need_to_display_promocode_section) && $need_to_display_promocode_section == 'Yes') { ?>
        discount_code = getCookie('discount_code');
        <?php }?>

        var url = '/{{$proxy_path}}/api/grab_data_from_checkout';
        var shop = $('#shop_url').val();

        if (shipping_email != '') {
            if ($xhr != undefined && $xhr != '') {
                //this is cancel multiple ajax call and consider only last ajax call
                $xhr.abort();
            }
            $("#cont_to_pay_method").attr('disabled', true);
            //$("#cont_to_pay_method").html('Please wait...');

            if(trigger_mode=='on_ship_change'){
                $("#shpping_rates_div").show().css('pointer-events','none');
                $("#shpping_rates_loader").hide();
            }else{
                $("#shpping_rates_loader").show();
                $("#shpping_rates_div").hide();
            }

            $xhr = $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    shipping_email: shipping_email,
                    shipping_phone: shipping_phone,
                    shipping_first_name: shipping_first_name,
                    shipping_last_name: shipping_last_name,
                    shipping_address: shipping_address,
                    shipping_address2: shipping_address2,
                    shipping_city: shipping_city,
                    shipping_country: shipping_country,
                    shipping_state: shipping_state,
                    shipping_pincode: shipping_pincode,

                    billing_first_name: billing_first_name,
                    billing_last_name: billing_last_name,
                    billing_phone: billing_phone,
                    billing_company: billing_company,
                    billing_address: billing_address,
                    billing_address2: billing_address2,
                    billing_city: billing_city,
                    billing_country: billing_country,
                    billing_state: billing_state,
                    billing_pincode: billing_pincode,

                    cartItems: cartItems,
                    shipping_price: shipping_price,
                    shipping_title: shipping_title,
                    shipping_handle: shipping_handle,
                    discount_code: discount_code,
                    customer_id: customer_id,
                    checkout_id: checkout_id,
                    sms_multi_shipping_lines: sms_multi_shipping_lines,
                    cart: cart,
                    shop: shop
                },
                success: function(result) {
                    $("#cont_to_pay_method").removeAttr('disabled');
                    $("#cont_to_pay_method").html('Continue to payment'); // nik 05/25/2021
                    $("#shpping_rates_loader").hide();
                    $("#shpping_rates_div").show();
                    var obj = result;
                    if (obj.success == 'true') {
                        if(trigger_mode=='move_to_step2'){
                            $("#continue_to_ship_method_btn").removeAttr('disabled');
                            google_ana_page_load(2, '');
                            store_abondoned_step_1();
                            openCity(event, 'tab-2');
                            if (document.getElementById('main_div_discount_code')) {
                                document.getElementById('main_div_discount_code').style.display = 'block';
                            }
                            document.getElementById('tab-2-open').click();
                            create_postscript_subscription();
                        }
                        $("#vendor_shipping_error_div").hide();
                        if (discount_code != '') {
                            document.cookie = "discount_code=;path=/;";
                        }
                        if (obj.checkout_id != undefined && obj.checkout_id != '') {
                            $('#checkout_id').val(obj.checkout_id);
                        }
                        get_checkout_data(trigger_mode);
                    } else {
                        $("#vendor_shipping_error_div").show();
                        $("#vendor_shipping_error_msg").html(decodeEntities(obj.message));
                        $('html, body').animate({
                            scrollTop: $("#vendor_shipping_error_div").offset().top
                        }, 500);
                        document.getElementById('defaultOpen').click();
                    }
                }
            });
        }
    }

    function get_checkout_data(trigger_mode='') {
        var cartItems = $("#item_arr_for_calc").val();
        var currency_symbol = '<?= $shop_currency_symbol ?>';

        var checkout_id = $('#checkout_id').val().trim();
        var url = '/{{$proxy_path}}/api/get_checkout_data';
        var shop = $('#shop_url').val();
        if (checkout_id != "") {
            $("#cont_to_pay_method").attr('disabled', true);
            //$("#cont_to_pay_method").html('Please wait...'); // nik 05/25/2021

            if(trigger_mode=='on_ship_change'){
                $("#shpping_rates_loader").hide();
                $("#shpping_rates_div").show().css('pointer-events','none');
            }else{
                $("#shpping_rates_loader").show();
                $("#shpping_rates_div").hide();
            }
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {
                    checkout_id: checkout_id,
                    shop: shop,
                    allow_free_shipping: '{{@$_POST['allow_free_shipping']}}',
                },
                success: function(result) {
                    $("#cont_to_pay_method").removeAttr('disabled');
                    $("#cont_to_pay_method").html('Continue to payment'); // nik 05/25/2021

                    if(trigger_mode=='on_ship_change'){
                        $("#shpping_rates_loader").hide();
                        $("#shpping_rates_div").show().css('pointer-events','unset');
                    }else{
                        $("#shpping_rates_loader").hide();
                        $("#shpping_rates_div").show();
                    }

                    var obj = result;
                    if (obj.success == 'true') {

                        //if requires_shipping is true, but there is no shipping_rates. then don't allow to checkout
                        $("#vendor_shipping_error_div").hide();
                        if (obj.DATA != undefined && obj.DATA.checkout_data != undefined && obj.DATA.checkout_data.requires_shipping == true){
                            if (obj.DATA.availableShippingRates == undefined
                                    || (obj.DATA.availableShippingRates != undefined && obj.DATA.availableShippingRates.shippingRates == undefined)
                                    || (obj.DATA.availableShippingRates != undefined && obj.DATA.availableShippingRates.shippingRates.length == 0)) {
                                $("#vendor_shipping_error_div").show();
                                $("#vendor_shipping_error_msg").html(vendor_shipping_error_msg);
                                $("#defaultOpen").click();
                            }
                        }

                        //display all available shipping methods
                        setShippingMethods(obj);
                        setProductSummery(obj);

                        var cart_total = 0;

                        //calculate item price and subtotal
                        var cart_sub_total = 0;
                        var cartItemsArr = JSON.parse(cartItems);
                        if (cartItemsArr.length > 0) {
                            $(cartItemsArr).each(function(k, v) {
                                var item_price_doller = parseFloat(v.price / 100);
                                if (v.square_discount != undefined && v.square_discount.discount !=
                                        undefined && v.square_discount.discount > 0) {
                                    if (v.square_discount.cal_type == '%') {
                                        item_price_doller = item_price_doller - (item_price_doller * v
                                                .square_discount.discount / 100);
                                    } else if (v.square_discount.cal_type == '-') {
                                        item_price_doller = item_price_doller - v.square_discount
                                                .discount;
                                    }
                                } else {
                                    if (v.subscription_discount != undefined && v.subscription_discount
                                                    .discount != undefined && v.subscription_discount.discount > 0
                                    ) {
                                        if (v.subscription_discount.cal_type == '%') {
                                            item_price_doller = item_price_doller - (
                                            item_price_doller * v.subscription_discount
                                                    .discount / 100);
                                        } else if (v.subscription_discount.cal_type == '-') {
                                            item_price_doller = item_price_doller - v
                                                    .subscription_discount.discount;
                                        }
                                    }
                                }
                                var item_sub_total = item_price_doller * v.quantity;
                                cart_sub_total += parseFloat(item_sub_total);
                                //$("#item_sub_total_"+ v.variant_id).html(currency_symbol + (item_sub_total).toFixed(2));
                            });
                        }
                        $("#cart_subtotal").html(currency_symbol + (cart_sub_total).toFixed(2));
                        cart_total += parseFloat(cart_sub_total);

                        //calculate checkout discount
                        var is_shipping_free = 'No';
                        var discount_price = 0;
                        $('.applied-reduction-code').hide();
                        $('#reduction_code_text').html('');
                        $('#reduction_code_text_mobile').html('');
                        $('#reduction_code_div').hide();
                        $('#reduction_code_div_mobile').hide();
                        if (obj.DATA != undefined && obj.DATA.checkout_data != undefined && obj.DATA
                                        .checkout_data.applied_discount != undefined && obj.DATA.checkout_data
                                        .applied_discount.applicable == true) {
                            $('#discount_code').val('').attr('readonly', 1);
                            $('#discount_code_mobile').val('').attr('readonly', 1);
                            $('#cart_discount_tr').show();
                            $('#discount_code_btn').addClass('btn--disabled').attr('disabled', true);
                            $('#discount_code_btn_mobile').addClass('btn--disabled').attr('disabled', true);

                            discount_price = parseFloat(obj.DATA.checkout_data.applied_discount.amount);
                            $('#cart_discount_div').html('- ' + currency_symbol + discount_price.toFixed(2));
                            $('#applied-reduction-code').html(obj.DATA.checkout_data.applied_discount.title);
                            $('.applied-reduction-code').show();
                            $('#reduction_code_text').html(obj.DATA.checkout_data.applied_discount.title);
                            $('#reduction_code_text_mobile').html(obj.DATA.checkout_data.applied_discount.title);
                            $('#reduction_code_div').show();
                            $('#reduction_code_div_mobile').show();
                            //$("#main_div_discount_code").hide();
                        } else if (obj.DATA != undefined && obj.DATA.checkout_data != undefined && obj.DATA
                                        .checkout_data.applied_discount != undefined && obj.DATA.checkout_data
                                        .applied_discount.applicable == false) {
                            $('#discount-msg').html(obj.DATA.checkout_data.applied_discount.non_applicable_reason).show();
                            $('#discount-msg-mobile').html(obj.DATA.checkout_data.applied_discount.non_applicable_reason).show();
                            $('#main_div_discount_code').show();
                        } else {
                            if (obj.DATA != undefined && obj.DATA.checkout_data != undefined && obj.DATA
                                            .checkout_data.line_items != undefined && obj.DATA.checkout_data.line_items
                                            .length > 0) {
                                $(obj.DATA.checkout_data.line_items).each(function(k_item, v_item) {
                                    if (v_item.applied_discounts != undefined && v_item
                                                    .applied_discounts[0] != undefined) {
                                        $('#discount_code').val('').attr('readonly', 1);
                                        $('#discount_code_mobile').val('').attr('readonly', 1);
                                        $('#cart_discount_tr').show();
                                        $('#discount_code_btn').addClass('btn--disabled').attr('disabled', true);
                                        $('#discount_code_btn_mobile').addClass('btn--disabled').attr('disabled', true);

                                        discount_price = parseFloat(v_item.applied_discounts[0]
                                                .amount);
                                        $('#cart_discount_div').html('- ' + currency_symbol +
                                        discount_price.toFixed(2));
                                        $('#applied-reduction-code').html(v_item.applied_discounts[0].description);
                                        $('.applied-reduction-code').show();

                                        $('#reduction_code_text').html(v_item.applied_discounts[0].description);
                                        $('#reduction_code_text_mobile').html(v_item.applied_discounts[0].description);
                                        $('#reduction_code_div').show();
                                        $('#reduction_code_div_mobile').show();
                                        //$("#main_div_discount_code").hide();
                                    }
                                });
                            }
                        }

                        cart_total -= parseFloat(discount_price);

                        //calculate shipping
                        var shipping_price = 0;
                        if ($("#sms_multi_shipping_lines").val() != '') {
                            let sms_multi_shipping_lines = JSON.parse($("#sms_multi_shipping_lines").val());
                            $.each(sms_multi_shipping_lines, function(k, sms) {
                                shipping_price += parseFloat(sms.price);
                            });
                            $('#cart_shipping').html(currency_symbol + shipping_price.toFixed(2));
                        } else if (is_shipping_free == 'Yes') {
                            $('#cart_shipping').html('Free');
                            $('#shipping_lines').val(
                                    '{"title":"Free","price":0,"code":"Free","source":"shopify"}');
                        } else {
                            if ($(".shipping_radio").length > 0) {
                                var shipping = $(".shipping_radio:checked");
                                shipping_price = shipping.data('price');

                                $('.shipping_method_div').html(shipping.data('name') + ' - <strong>' + currency_symbol + shipping_price+'</strong>');
                                $('#cart_shipping').html(currency_symbol + (parseFloat(shipping_price)).toFixed(2) );

                                var shipping_lines = {};
                                shipping_lines["id"] = shipping.data('id');
                                shipping_lines["title"] = shipping.data('name');
                                shipping_lines["price"] = shipping.data('price');
                                shipping_lines["code"] = shipping.data('name');
                                shipping_lines["source"] = 'shopify';
                                var shipping_linesjson = JSON.stringify(shipping_lines);
                                $('#shipping_lines').val(shipping_linesjson);
                            }
                        }
                        cart_total += parseFloat(shipping_price);

                        //calculate tax
                        var need_to_calculate_tax = '<?= $need_to_calculate_tax ?>';
                        var tax_lines = [];
                        var total_tax_amount = 0;
                        if ($("#sms_multi_tax_lines").val() != '') {
                            let sms_multi_tax_lines = JSON.parse($("#sms_multi_tax_lines").val());
                            $.each(sms_multi_tax_lines, function(k, sms) {
                                total_tax_amount += parseFloat(sms.price);
                            });
                            if (need_to_calculate_tax == 'Yes') {
                                cart_total += parseFloat(total_tax_amount);
                                $('#cart_taxes').html(currency_symbol + parseFloat(total_tax_amount).toFixed(
                                        2));
                            } else {
                                $('#cart_taxes').html('(Included) ' + currency_symbol + parseFloat(total_tax_amount).toFixed(2));
                            }
                        } else if (obj.DATA != undefined && obj.DATA.taxLines != undefined && obj.DATA.taxLines
                                        .length > 0) {
                            $(obj.DATA.taxLines).each(function(tl_k, tl_v) {
                                var tax_rate = tl_v.rate;
                                var tax_title = tl_v.title;

                                <?php if (isset($cust_ws_tag) && !empty($cust_ws_tag)) { ?>
                                // here we can get direct price in tax_line, but if square wholesale discount apply then we need to change tax-price based on product new price
                                var tax_price = (parseFloat(cart_total) * parseFloat(tax_rate)).toFixed(2);
                                <?php } else { ?>
                                var tax_price = parseFloat(tl_v.price);
                                <?php }?>
                                total_tax_amount += parseFloat(tax_price);

                                var tmp_tl = {};
                                tmp_tl["title"] = tax_title;
                                tmp_tl["price"] = tax_price;
                                tmp_tl["rate"] = tax_rate;
                                tax_lines.push(tmp_tl);
                            });
                            $('#tax_lines').val(JSON.stringify(tax_lines));
                            if (need_to_calculate_tax == 'Yes') {
                                cart_total += parseFloat(total_tax_amount);
                                $('#cart_taxes').html(currency_symbol + parseFloat(total_tax_amount).toFixed(2));
                            } else {
                                $('#cart_taxes').html('(Included) ' + currency_symbol + parseFloat(total_tax_amount).toFixed(2));
                            }
                        } else {
                            $('#cart_taxes').html('-');
                            $('#tax_lines').val('');
                        }
                        $("#cart_taxes_tr").show();

                        //calculate giftcard
                        var giftcard_price = 0;
                        $('.applied-giftcard-code-div').hide();
                        if (obj.DATA != undefined && obj.DATA.appliedGiftCards != undefined && obj.DATA
                                        .appliedGiftCards[0] != undefined) {
                            var giftcard_id = obj.DATA.appliedGiftCards[0].id;
                            $('#giftcard_code').val('').attr('readonly', 1);
                            $('#cart_giftcard_tr').show();
                            $('#giftcard_code_btn').addClass('btn--disabled').attr('disabled', true);

                            giftcard_price = parseFloat(obj.DATA.appliedGiftCards[0].presentmentAmountUsed
                                    .amount);
                            $('#cart_giftcard_div').html('- ' + currency_symbol + giftcard_price.toFixed(2));
                            $('#applied-giftcard-code').html('**** ' + obj.DATA.appliedGiftCards[0]
                                    .lastCharacters);
                            $("#remove-giftcard").data('giftcard_id', giftcard_id).attr('data-giftcard_id',
                                    giftcard_id);
                            $('.applied-giftcard-code-div').show();
                            $("#main_div_discount_code").hide();
                        }
                        cart_total -= parseFloat(giftcard_price);

                        //set cart-total
                        $("#cart_total_price").html(currency_symbol + cart_total.toFixed(2));
                        $("#mobile_total_final_price").html(currency_symbol + cart_total.toFixed(2));

                        if(parseFloat(discount_price) > 0){
                            $("#mobile_total_strike_price").html('<s>'+currency_symbol + (cart_total+parseFloat(discount_price)).toFixed(2)+'</s>').show();
                        }else{
                            $("#mobile_total_strike_price").hide();
                        }

                    }
                }
            });
        }
    }

    function setShippingMethods(obj) {
        var sphtml = '';
        if (obj.DATA != undefined && obj.DATA.availableShippingRates != undefined && obj.DATA.availableShippingRates
                        .shippingRates != undefined) {
            if (obj.DATA.availableShippingRates.shippingRates.length > 0) {
                var cnt = 0;
                var default_shipping_method = '';
                if ($(".shipping_radio").length > 0) {
                    default_shipping_method = $(".shipping_radio:checked").data('id');
                }
                $(obj.DATA.availableShippingRates.shippingRates).each(function(k, v) {
                    var checked = '';
                    if (default_shipping_method == v.handle) {
                        checked = 'checked';
                    } else if (cnt == 0) {
                        checked = 'checked';
                    }
                    sphtml += '<div class="radio-box">';
                    sphtml += '<div class="radio__input">';
                    sphtml += '<input data-name="' + v.title +
                    '" class="shipping_radio form-input-radio" type="radio" id="standard-shipping' + v.handle + '" data-id="' +
                    v.handle + '" data-price="' + parseFloat(v.priceV2.amount).toFixed(2) + '" data-name="' + v
                            .title + '" name="shipping_charge" value="' + v.priceV2.amount + '" required="required" ' +
                    checked + '>';
                    sphtml += '</div>';
                    sphtml += '<label class="radio__label" for="standard-shipping' + v.handle + '">';
                    sphtml += '<span class="radio__label__primary">' + v.title + '</span>';
                    sphtml += '<span class="radio__label__accessory"><?=$shop_currency_symbol?>' + parseFloat(v.priceV2.amount).toFixed(2) + '</span>';

                    <?php if (isset($shipping_method_desc) && !empty($shipping_method_desc)) { ?>
                    sphtml += '<br><span class="radio__label__secondary"><?=$shipping_method_desc?></span>';
                    <?php }?>

                    sphtml += '</label>';
                    sphtml += '</div>';
                    cnt++;
                });
            } else {
                sphtml += '<div class="radio-box">';
                sphtml += '<div class="radio__input">&nbsp;</div>';
                sphtml += '<label class="radio__label" for="no-shipping">';
                sphtml += '<span class="radio__label__primary">No shipping found</span>';
                sphtml += '</label>';
                sphtml += '</div>';
            }
        } else {
            sphtml += '<div class="radio-box">';
            sphtml += '<div class="radio__input">&nbsp;</div>';
            sphtml += '<label class="radio__label" for="no-shipping">';
            sphtml += '<span class="radio__label__primary">No shipping found</span>';
            //sphtml += '<span class="radio__label__accessory">&nbsp;</span>';
            //sphtml += '<div align="center"><img src="assets/images/loader.gif"></div>';
            sphtml += '</label>';
            sphtml += '</div>';
        }
        $("#shpping_rates_list").html(sphtml);

        $("#cont_to_pay_method").removeAttr('disabled');
        $("#cont_to_pay_method").html('Continue to payment'); // nik 05/25/2021
        $('#cont_to_pay_method').css('pointer-events', '');


        if ($(".shipping_radio").length > 0 && obj.DATA != undefined && obj.DATA.availableShippingRates != undefined && obj
                        .DATA.availableShippingRates.shippingRates != undefined && obj.DATA.availableShippingRates.shippingRates
                        .length > 0) {
            //this code is for suppose in last step, if we apply promocode and then shipping-rates change and our selected shipping-method will gone, then this code check and apply latest shipping-method
            var checked_sr_exist = 'No';
            $(obj.DATA.availableShippingRates.shippingRates).each(function(k, v) {
                if (obj.DATA.checkout_data != null && obj.DATA.checkout_data.shipping_line != null) {
                    if (v.handle == obj.DATA.checkout_data.shipping_line.handle || decodeURIComponent(v.handle) ==
                            obj.DATA.checkout_data.shipping_line.handle) {
                        checked_sr_exist = 'Yes';
                    }
                }
            });
            if (checked_sr_exist == 'No') {
                if (obj.DATA.checkout_data.shipping_line != null) {
                    // here a==null is not working properly, so we put reverse condition
                } else {
                    if ($(".shipping_radio:checked").data('id') == 'local-pickup-0.00') {
                        //when there is "local-pickup", then there is no option coming from shopify, so no need to trigger again. local pickup is coming from app.
                    } else if ($(".shipping_radio:checked").data('id') == 'free-shipping-0.00') {
                        //when there is "free-shipping", then there is no option coming from shopify, so no need to trigger again. free shipping is coming from app.
                    } else {
                        $('.shipping_radio').trigger('change');
                    }
                }
            }
        }
    }

    function setProductSummery(obj) {
        var tblHtml = '';
        var cartItems = $("#item_arr_for_calc").val();
        var currency_symbol = '<?=$shop_currency_symbol?>';

        if (obj.DATA != undefined && obj.DATA.line_items_fe != undefined && obj.DATA.line_items_fe.length > 0) {
            var indx = 0;
            var cartItemsArr = JSON.parse(cartItems);
            $(obj.DATA.line_items_fe).each(function(k, v) {
                if (cartItemsArr.length > 0) {
                    var fe_square_discount = [];
                    var fe_subscription_discount = [];
                    $(cartItemsArr).each(function(k1, v1) {
                        if (v1.id == v.variant_id) {
                            if (v1.square_discount != undefined && v1.square_discount.discount !=
                                    undefined && v1.square_discount.discount > 0) {
                                fe_square_discount = v1.square_discount;
                            } else if (v1.subscription_discount != undefined && v1.subscription_discount
                                            .discount != undefined && v1.subscription_discount.discount > 0) {
                                fe_subscription_discount = v1.subscription_discount;
                            }
                        }
                    });
                    if (fe_square_discount.discount != undefined && fe_square_discount.discount > 0) {
                        if (fe_square_discount.cal_type == '%') {
                            v.price = v.price - (v.price * fe_square_discount.discount / 100);
                        } else if (fe_square_discount.cal_type == '-') {
                            v.price = v.price - fe_square_discount.discount;
                        }
                    } else if (fe_subscription_discount.discount != undefined && fe_subscription_discount
                                    .discount > 0) {
                        if (fe_subscription_discount.cal_type == '%') {
                            v.price = v.price - (v.price * fe_subscription_discount.discount / 100);
                        } else if (fe_subscription_discount.cal_type == '-') {
                            v.price = v.price - fe_subscription_discount.discount;
                        }
                    }
                }
                tblHtml += '<tr class="product" id="product-' + indx + '">';

                tblHtml += '<td class="product__image">';
                tblHtml += '<div class="product-thumbnail">';
                tblHtml += '<div class="product-thumbnail__wrapper">';
                tblHtml += '<img alt="" src="' + v.image_url + '">';
                tblHtml += '</div>';
                tblHtml += '<span class="quantity">' + v.quantity + '</span>';
                tblHtml += '</div>';
                tblHtml += '</td>';

                tblHtml += '<td class="product__description">';
                tblHtml += '<h6>' + v.title;
                tblHtml += '<small>';
                if (v.variant_title != '') {
                    tblHtml += '<br>' + v.variant_title;
                }
                if (v.properties != undefined && v.properties._is_subs != undefined && v.properties._is_subs ==
                        'true' && v.properties._interval != undefined && v.properties._interval != '') {
                    tblHtml += '<br>Subscription: ' + v.properties._interval.toLowerCase() + '(s)';
                }
                tblHtml += '</small>';
                tblHtml += '</h6>';

                if (v.properties != undefined && Object.keys(v.properties).length > 0) {
                    $.each(v.properties, function(prop_k, prop_v) {
                        if (prop_k.substring(0, 1) != "_") {
                            tblHtml += '<span>'+prop_k+'&nbsp;:&nbsp;'+prop_v+'</span><br>';
                        }
                    });
                }

                tblHtml += '</td>';

                tblHtml += '<td class="product__price">';
                if (v.discount_amount > 0) {
                    tblHtml += '<span><strike>' + currency_symbol + (parseFloat(v.price) * parseFloat(v.quantity))
                            .toFixed(2) + '</strike> <br>' + currency_symbol + ((parseFloat(v.price) * parseFloat(v
                            .quantity)) - parseFloat(v.discount_amount)).toFixed(2) + '</span>';
                } else {
                    if(parseFloat(v.price) > 0){
                        tblHtml += '<span>' + currency_symbol + (parseFloat(v.price) * parseFloat(v.quantity)).toFixed(2) + '</span>';
                    }else{
                        tblHtml += '<span>Free</span>';
                    }
                }
                tblHtml += '</td>';

                tblHtml += '</tr>';

                indx++;
            });
            $("#product_summery_table tbody").html(tblHtml);
        }
    }

</script>

<script type="text/javascript">
    $('#complete-order-btn').click(async function(e) {
        if (validateCustomerInfo() && validateBillingInfo()) {
            $('#complete-order-btn').attr('disabled', true);
            $('#complete-order-btn span').hide();
            $('#complete-order-btn i').show();

            submitForm();
        }
    });

    function submitForm() {
        var url = SITEURL + '/' + $("#checkout-frm").attr('action');


        $('#verify_age_complete_order span').hide();
        $('#verify_age_complete_order i').show();
        $('#verify_age_complete_order').attr('disabled', true);

        $("#payment_error_div").hide();
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: $("#checkout-frm").serialize(),
            success: function(data) {
                if (data.type == 'success') {
                    $.ajax({
                        type: "POST",
                        url: '/cart/clear.js',
                        dataType: 'json',
                        success: function() {}
                    });
                    setTimeout(function() {
                        location.href = data.thankyou_url;
                    }, 2000);
                    google_ana_purchase_done(data.shop_order_id,
                            '<?=$shop?>',
                            data.shop_order_total_price, data.shop_order_total_tax, data
                                    .shop_order_total_shipping, data.shop_order_promocode);
                } else {
                    $("#payment_error_msg").html(decodeEntities(data.message));
                    $("#payment_error_div").show();
                    $('html, body').animate({
                        scrollTop: $("#payment_error_div").offset().top
                    }, 500);

                    $('#complete-order-btn i').hide();
                    $('#complete-order-btn span').show();
                    $('#complete-order-btn').attr('disabled', false);
                    $('#verify_age_complete_order i').hide();
                    $('#verify_age_complete_order span').show();
                    $('#verify_age_complete_order').attr('disabled', false);
                }
            }
        });
    }

    function store_abondoned_step_1() {
        var ac_token = $("#ac_token").val();
        var customer_id = $('#customer_id').val().trim();
        var customer_email = $('#checkout_email').val().trim();
        var customer_phone = $('#phone').val().trim();

        var shipping_first_name = $('#shipping_first_name').val().trim();
        var shipping_last_name = $('#shipping_last_name').val().trim();
        var shipping_address = $('#shipping_address').val().trim();
        var shipping_address2 = $('#shipping_address2').val().trim();
        var shipping_city = $('#shipping_city').val().trim();
        var shipping_pincode = $('#shipping_pincode').val().trim();
        var shipping_state = $("#shipping_state option:selected").val();
        var shipping_country = $("#shipping_country option:selected").val();
        var shipping_lines = $('#shipping_lines').val().trim();
        var allow_klaviyo_tracking = "No";
        <?php if (isset($cs_data->cs_email_marketing_status) && $cs_data->cs_email_marketing_status=='enable' && isset($cs_data->cs_klaviyo_status) && $cs_data->cs_klaviyo_status=='enable') { ?>
        if ($("#email_marketing_subscribe").length > 0 && $("#email_marketing_subscribe").is(':checked')) {
            allow_klaviyo_tracking = "Yes";
        }
        <?php } ?>

        var cart = $('input[name="cart"]').val().trim();
        var shop = $('#shop_url').val();
        var url = '/{{$proxy_path}}/api/store_abondoned_steps';
        $.ajax({
            type: "POST",
            url: url,
            data: {
                ac_token: ac_token,
                customer_id: customer_id,
                customer_email: customer_email,
                customer_phone: customer_phone,
                shipping_first_name: shipping_first_name,
                shipping_last_name: shipping_last_name,
                shipping_address: shipping_address,
                shipping_address2: shipping_address2,
                shipping_city: shipping_city,
                shipping_pincode: shipping_pincode,
                shipping_state: shipping_state,
                shipping_country: shipping_country,
                shipping_lines: shipping_lines,
                allow_klaviyo_tracking: allow_klaviyo_tracking,
                cart: cart,
                shop: shop,
                step: 'SHIP_METHOD',
            },
            success: function(result) {
                var obj = JSON.parse(result);
                if (obj.success == 'true') {
                    $("#ac_token").val(obj.ac_token);
                }
            }
        });

    }

    function store_abondoned_step_2() {
        var ac_token = $("#ac_token").val();
        var customer_id = $('#customer_id').val().trim();
        var customer_email = $('#checkout_email').val().trim();
        var customer_phone = $('#phone').val().trim();

        var shipping_first_name = $('#shipping_first_name').val().trim();
        var shipping_last_name = $('#shipping_last_name').val().trim();
        var shipping_address = $('#shipping_address').val().trim();
        var shipping_address2 = $('#shipping_address2').val().trim();
        var shipping_city = $('#shipping_city').val().trim();
        var shipping_pincode = $('#shipping_pincode').val().trim();
        var shipping_state = $("#shipping_state option:selected").val();
        var shipping_country = $("#shipping_country option:selected").val();
        var shipping_lines = $('#shipping_lines').val().trim();

        var cart = $('input[name="cart"]').val().trim();
        var shop = $('#shop_url').val();
        var url = '/{{$proxy_path}}/api/store_abondoned_steps';
        $.ajax({
            type: "POST",
            url: url,
            data: {
                ac_token: ac_token,
                customer_id: customer_id,
                customer_email: customer_email,
                customer_phone: customer_phone,
                shipping_first_name: shipping_first_name,
                shipping_last_name: shipping_last_name,
                shipping_address: shipping_address,
                shipping_address2: shipping_address2,
                shipping_city: shipping_city,
                shipping_pincode: shipping_pincode,
                shipping_state: shipping_state,
                shipping_country: shipping_country,
                shipping_lines: shipping_lines,
                cart: cart,
                shop: shop,
                step: 'PAY_INFO'
            },
            success: function(result) {
                var obj = JSON.parse(result);
                if (obj.success == 'true') {
                    $("#ac_token").val(obj.ac_token);
                }
            }
        });
    }

    function create_postscript_subscription() {
        <?php if (isset($cs_data->cs_postscript_subscribe_status) && $cs_data->cs_postscript_subscribe_status=='enable' && isset($cs_data->cs_email_marketing_status) && $cs_data->cs_email_marketing_status=='enable') { ?>
        if ($("#postscript_subscribe").length > 0 && $("#postscript_subscribe").is(':checked') && $("#phone").val()
                        .trim() != '') {
            var customer_phone = $('#phone').val().trim();
            var shop = $('#shop_url').val();
            $.ajax({
                type: "POST",
                url: SITEURL + '/controller.php',
                data: {
                    customer_phone: customer_phone,
                    shop: shop,
                    action: 'create_postscript_subscription'
                },
                success: function(result) {
                    /*var obj = JSON.parse(result);
                     if(obj.success=='true'){

                     }*/
                }
            });
        }
        <?php }?>
    }

    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
    $("#shipping_state, #shipping_country").change(function() {
        delay(function(){
            $("#continue_to_ship_method_btn").removeAttr('disabled');
        }, 500 );
    });
    $("#checkout_email, #shipping_first_name, #shipping_last_name, #shipping_company, #shipping_address, #shipping_address2, #shipping_city, #shipping_pincode, #phone").focus(function() {
        delay(function(){
            $("#continue_to_ship_method_btn").removeAttr('disabled');
        }, 500 );
    });

    <?php if (isset($payCred->pay_is_enable_pincode_limit) && $payCred->pay_is_enable_pincode_limit=='Yes') { ?>
    $("#shipping_pincode").click(function() {
        $("#continue_to_ship_method_btn").attr('disabled', true);
    });
    $("#shipping_pincode").focus(function() {
        $("#continue_to_ship_method_btn").attr('disabled', true);
    });
    $("#shipping_pincode").blur(function() {
        check_postalcode_allow();
    });

    function check_postalcode_allow() {
        var shipping_pincode = $("#shipping_pincode").val().trim();
        var shop = $('#shop_url').val();
        if (shipping_pincode != '') {
            $.ajax({
                url: SITEURL + '/controller.php',
                method: 'post',
                data: {
                    'action': 'check_postalcode_allow',
                    'shop': shop,
                    'shipping_pincode': shipping_pincode
                },
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.success == 'true') {
                        $("#continue_to_ship_method_btn").removeAttr('disabled');
                    } else {
                        Swal.fire({
                            title: '',
                            text: obj.message,
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    $("#continue_to_ship_method_btn").removeAttr('disabled');
                }
            });
        } else {
            $("#continue_to_ship_method_btn").removeAttr('disabled');
        }
    }

    <?php }?>

    $(document).keydown(function(event) {
        if (event.keyCode == 123) {
            return false;
        } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
            return false;
        }
    });

    $(document).on("contextmenu", function(e) {
        e.preventDefault();
    });

    $("#complete_age_verification_btn").click(function() {
        if (validateBillingInfo()) {
            $("#av_bday_name1").html($('#shipping_first_name').val().trim() + ' ' + $('#shipping_last_name').val()
                    .trim());
            $("#av_bday_name2").html($('#billing_first_name').val().trim() + ' ' + $('#billing_last_name').val()
                    .trim());
            if ($("#av_ssn_name1").length > 0) {
                $("#av_ssn_name1").html($('#shipping_first_name').val().trim() + ' ' + $('#shipping_last_name')
                        .val().trim());
            }
            if ($("#av_ssn_name2").length > 0) {
                $("#av_ssn_name2").html($('#billing_first_name').val().trim() + ' ' + $('#billing_last_name').val()
                        .trim());
            }

            if ($("#billing-2").is(':checked')) {
                //if choose different billing address
                $("#av_bday_div2").show();
                if ($("#av_ssn_div2").length > 0) {
                    $("#av_ssn_div2").show();
                }
            } else {
                $("#av_bday_div2").hide();
                $("#birthday_2").val('');
                if ($("#av_ssn_div2").length > 0) {
                    $("#av_ssn_div2").hide();
                }
            }
            $("#inexModal").show();
        }
    });

    function validateBillingInfo() {
        var $sfname = $('#shipping_first_name');
        var $slname = $('#shipping_last_name');
        var $saddress = $('#shipping_address');
        var $saddress2 = $('#shipping_address2');
        var $scity = $('#shipping_city');
        var $scountry = $('#shipping_country');
        var $sstate = $('#shipping_state');
        var $szip = $('#shipping_pincode');

        var $bfname = $('#billing_first_name');
        var $blname = $('#billing_last_name');
        var $baddress = $('#billing_address1');
        var $baddress2 = $('#billing_address2');
        var $bcity = $('#billing_city');
        var $bcountry = $('#billing_country');
        var $bstate = $('#billing_state');
        var $bzip = $('#billing_pincode');
        var $bphone = $('#billing_phone');

        var errCnt = 0;
        $('.form-group .error-msg').remove();
        $('.form-group').removeClass('field--error');

        if ($bfname.val() == "") {
            $('<p class="error-msg">Please enter your first name</p>').insertAfter($bfname.parent('label'));
            $bfname.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($blname.val() == "") {
            $('<p class="error-msg">Please enter your last name</p>').insertAfter($blname.parent('label'));
            $blname.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($baddress.val() == "") {
            $('<p class="error-msg">Please enter an address</p>').insertAfter($baddress.parent('label'));
            $baddress.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($bcity.val() == "") {
            $('<p class="error-msg">Please enter a city</p>').insertAfter($bcity.parent('label'));
            $bcity.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($bcountry.val() == "") {
            $('<p class="error-msg">Please select a country</p>').insertAfter($bcountry.parent('label'));
            $bcountry.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($('#billing_state option').length > 0) {
            if ($bstate.val() == "") {
                $('<p class="error-msg">Select a state</p>').insertAfter($bstate.parent('label'));
                $bstate.parent().parent().addClass('field--error');
                errCnt++;
            }
        }
        if ($bzip.val() == "") {
            $('<p class="error-msg">Please enter a ZIP code</p>').insertAfter($bzip.parent('label'));
            $bzip.parent().parent().addClass('field--error');
            errCnt++;
        }
        if ($bphone.val() == "") {
            $('<p class="error-msg">Please enter a valid phone number</p>').insertAfter($bphone.parent('label'));
            $bphone.parent().parent().addClass('field--error');
            errCnt++;
        }

        if ($("#billing-2").is(':checked') && errCnt == 0) {
            //if shipping or billing state is include in same-address condition
            <?php if (isset($cs_data->cs_age_verification_state_list_same_address) && !empty($cs_data->cs_age_verification_state_list_same_address)) { ?>
            var state_list_same_address =
                    '<?=$cs_data->cs_age_verification_state_list_same_address?>'; //CA,TX,AL
            var state_list_same_address_arr = state_list_same_address.split(','); //['CA','TX','AL']

            var state_for_same_address = '';
            if ($('#shipping_state option').length > 0) {
                if ($.inArray($sstate.val(), state_list_same_address_arr) >= 0) {
                    state_for_same_address = $sstate.val();
                }
            }
            if ($('#billing_state option').length > 0) {
                if ($.inArray($bstate.val(), state_list_same_address_arr) >= 0) {
                    state_for_same_address = $bstate.val();
                }
            }

            if (state_for_same_address != '') {
                if ($sfname.val() != $bfname.val() ||
                        $slname.val() != $blname.val() ||
                        $saddress.val() != $baddress.val() ||
                        $saddress2.val() != $baddress2.val() ||
                        $scity.val() != $bcity.val() ||
                        $sstate.val() != $bstate.val() ||
                        $scountry.val() != $bcountry.val() ||
                        $szip.val() != $bzip.val()
                ) {
                    var title = 'ATTENTION ' + state_for_same_address + ' RESIDENTS: <br>Please be advised that ' +
                            state_for_same_address + ' state regulations require your billing and shipping addresses to match';
                    Swal.fire({
                        title: title,
                        text: 'You are selecting a billing address that is different than your shipping address. Please be make sure that both billing and shipping addresses match or we may not be able to fulfill your order.',
                        confirmButtonText: "I understand"
                    });
                    //errCnt++; //when address not same, then just show msg, not consider as error. So commented this errCnt++;
                }
            }

            <?php }?>
        }


        if (errCnt == 0) {
            return true;
        }
    }
    $("#verify_age_complete_order").click(async function() {
        var errCnt = 0;
        $('.form-group .error-msg').remove();
        $('.form-group').removeClass('field--error');
        var shipping_state = $("#shipping_state").val();
        var shipping_pincode = $('#shipping_pincode').val();
        var billing_state = $("#billing_state").val();
        var billing_pincode = $('#billing_pincode').val();
        var birthdate_1 = $("#birthdate_1");
        var birthmonth_1 = $("#birthmonth_1");
        var birthyear_1 = $("#birthyear_1");
        if (birthdate_1.val() == "" || birthmonth_1.val() == "" || birthyear_1.val() == "") {
            $("#error_msg_birthday_1").html('<p class="error-msg">Please enter full birthday</p>');
            errCnt++;
        } else {
            var birthday_1_val = birthmonth_1.val() + '/' + birthdate_1.val() + '/' + birthyear_1.val();
            var bd1_date1 = new Date(birthday_1_val);
            var bd1_date2 = new Date(); // current date
            var bd1_diffTime = Math.abs(bd1_date2 - bd1_date1);
            var age_1 = Math.ceil(bd1_diffTime / (1000 * 3600 * 24 * 365)); //in years

            <?php if (isset($cs_data->cs_age_verification_state_list_with_age) && !empty($cs_data->cs_age_verification_state_list_with_age)) { ?>
            var state_list_with_age =
                    '<?=$cs_data->cs_age_verification_state_list_with_age?>'; //CA:21,TX:19
            var state_list_with_age_arr = state_list_with_age.split(','); //  ['CA:21','TX:19']
            if (state_list_with_age_arr.length > 0 && shipping_state != '') {
                var age_limit_for_current_ship_state = '';
                //check current shipping-state is included in age-veri conditions or not
                $(state_list_with_age_arr).each(function(k, v) {
                    if (v.includes(":")) {
                        var state_age_arr = v.split(':'); // ['TX','19']
                        if (state_age_arr[0] != undefined && state_age_arr[0] != '' && state_age_arr[
                                        1] !=
                                undefined && state_age_arr[1] != '') {
                            if (state_age_arr[0] == shipping_state) {
                                age_limit_for_current_ship_state = state_age_arr[1];
                            }
                        }
                    }
                });
                if (age_limit_for_current_ship_state != '') {
                    //if we find age limit in condition, then compare with bday-age
                    if (parseInt(age_1) < parseInt(age_limit_for_current_ship_state)) {
                        $("#error_msg_birthday_1").html(
                                '<p class="error-msg">You are not old enough to purchase in this state. You must be ' +
                                age_limit_for_current_ship_state + ' in ' + shipping_state +
                                ' state to purchase.</p>');
                        errCnt++;
                    }
                }
            }
            <?php }?>

            <?php if (isset($cs_data->cs_age_verification_zipcode_list_with_age) && !empty($cs_data->cs_age_verification_zipcode_list_with_age)) { ?>
            var zipcode_list_with_age =
                    '<?=$cs_data->cs_age_verification_zipcode_list_with_age?>'; //77074:21,77506:19
            var zipcode_list_with_age_arr = zipcode_list_with_age.split(','); //  ['77074:21','77506:19']
            if (zipcode_list_with_age_arr.length > 0 && shipping_pincode != '') {
                var age_limit_for_current_ship_zipcode = '';
                //check current shipping-zipcode is included in age-veri conditions or not
                $(zipcode_list_with_age_arr).each(function(k, v) {
                    if (v.includes(":")) {
                        var zipcode_age_arr = v.split(':'); // ['77074','19']
                        if (zipcode_age_arr[0] != undefined && zipcode_age_arr[0] != '' &&
                                zipcode_age_arr[
                                        1] != undefined && zipcode_age_arr[1] != '') {
                            if (zipcode_age_arr[0] == shipping_pincode) {
                                age_limit_for_current_ship_zipcode = zipcode_age_arr[1];
                            }
                        }
                    }
                });
                if (age_limit_for_current_ship_zipcode != '') {
                    //if we find age limit in condition, then compare with bday-age
                    if (parseInt(age_1) < parseInt(age_limit_for_current_ship_zipcode)) {
                        $("#error_msg_birthday_1").html(
                                '<p class="error-msg">You are not old enough to purchase in this state. You must be ' +
                                age_limit_for_current_ship_zipcode + ' in ' + shipping_pincode +
                                ' zipcode to purchase.</p>');
                        errCnt++;
                    }
                }
            }
            <?php }?>
        }

        if ($("#billing-2").is(':checked')) {
            var birthdate_2 = $("#birthdate_2");
            var birthmonth_2 = $("#birthmonth_2");
            var birthyear_2 = $("#birthyear_2");
            if (birthdate_2.val() == "" || birthmonth_2.val() == "" || birthyear_2.val() == "") {
                $("#error_msg_birthday_2").html('<p class="error-msg">Please enter full birthday</p>');
                errCnt++;
            } else {
                var birthday_2_val = birthmonth_2.val() + '/' + birthdate_2.val() + '/' + birthyear_2.val();
                var bd2_date1 = new Date(birthday_2_val);
                var bd2_date2 = new Date(); // current date
                var bd2_diffTime = Math.abs(bd2_date2 - bd2_date1);
                var age_2 = Math.ceil(bd2_diffTime / (1000 * 3600 * 24 * 365)); //in years

                <?php if (isset($cs_data->cs_age_verification_state_list_with_age) && !empty($cs_data->cs_age_verification_state_list_with_age)) { ?>
                var state_list_with_age =
                        '<?=$cs_data->cs_age_verification_state_list_with_age?>'; //CA:21,TX:19
                var state_list_with_age_arr = state_list_with_age.split(','); //  ['CA:21','TX:19']
                if (state_list_with_age_arr.length > 0 && billing_state != '') {
                    var age_limit_for_current_bill_state = '';
                    //check current shipping-state is included in age-veri conditions or not
                    $(state_list_with_age_arr).each(function(k, v) {
                        if (v.includes(":")) {
                            var state_age_arr = v.split(':'); // ['TX','19']
                            if (state_age_arr[0] != undefined && state_age_arr[0] != '' &&
                                    state_age_arr[
                                            1] != undefined && state_age_arr[1] != '') {
                                if (state_age_arr[0] == billing_state) {
                                    age_limit_for_current_bill_state = state_age_arr[1];
                                }
                            }
                        }
                    });
                    if (age_limit_for_current_bill_state != '') {
                        //if we find age limit in condition, then compare with bday-age
                        if (parseInt(age_2) < parseInt(age_limit_for_current_bill_state)) {
                            $("#error_msg_birthday_2").html(
                                    '<p class="error-msg">You are not old enough to purchase in this state. You must be ' +
                                    age_limit_for_current_bill_state + ' in ' + billing_state +
                                    ' state to purchase.</p>');
                            errCnt++;
                        }
                    }
                }
                <?php }?>

                <?php if (isset($cs_data->cs_age_verification_zipcode_list_with_age) && !empty($cs_data->cs_age_verification_zipcode_list_with_age)) { ?>
                var zipcode_list_with_age =
                        '<?=$cs_data->cs_age_verification_zipcode_list_with_age?>'; //77074:21,77506:19
                var zipcode_list_with_age_arr = zipcode_list_with_age.split(','); //  ['77074:21','77506:19']
                if (zipcode_list_with_age_arr.length > 0 && billing_pincode != '') {
                    var age_limit_for_current_bill_zipcode = '';
                    //check current billing-zipcode is included in age-veri conditions or not
                    $(zipcode_list_with_age_arr).each(function(k, v) {
                        if (v.includes(":")) {
                            var zipcode_age_arr = v.split(':'); // ['77074','19']
                            if (zipcode_age_arr[0] != undefined && zipcode_age_arr[0] != '' &&
                                    zipcode_age_arr[1] != undefined && zipcode_age_arr[1] != '') {
                                if (zipcode_age_arr[0] == billing_pincode) {
                                    age_limit_for_current_bill_zipcode = zipcode_age_arr[1];
                                }
                            }
                        }
                    });
                    if (age_limit_for_current_bill_zipcode != '') {
                        //if we find age limit in condition, then compare with bday-age
                        if (parseInt(age_2) < parseInt(age_limit_for_current_bill_zipcode)) {
                            $("#error_msg_birthday_2").html(
                                    '<p class="error-msg">You are not old enough to purchase in this state. You must be ' +
                                    age_limit_for_current_bill_zipcode + ' in ' + shipping_pincode +
                                    ' zipcode to purchase.</p>');
                            errCnt++;
                        }
                    }
                }
                <?php }?>

            }
        }
        <?php if (isset($cs_data->cs_ssn_verification_require) && $cs_data->cs_ssn_verification_require=='required') { ?>
        var ssn_number1 = $("#ssn_number1");
        if (ssn_number1.val() == "") {
            $('<p class="error-msg">Please enter ssn number</p>').insertAfter(ssn_number1.parent('label'));
            ssn_number1.parents('.form-group').addClass('field--error');
            errCnt++;
        }
        if ($("#billing-2").is(':checked')) {
            var ssn_number2 = $("#ssn_number2");
            if (ssn_number2.val() == "") {
                $('<p class="error-msg">Please enter ssn number</p>').insertAfter(ssn_number2.parent('label'));
                ssn_number2.parents('.form-group').addClass('field--error');
                errCnt++;
            }
        }
        <?php }?>

        if (errCnt == 0) {
            //paymentForm.requestCardNonce();
        }

    });

    function google_ana_page_load(step_num, option_title) {
        <?php if (isset($cs_data->cs_google_analytics_tracking_id) && !empty($cs_data->cs_google_analytics_tracking_id)) { ?>
        (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', '<?=$cs_data->cs_google_analytics_tracking_id?>');
        ga('require', 'ec');
        ga('set', 'currencyCode', '<?=$shop_currency_name?>'); // Set currency to Euros.

        <?php if (isset($cartinfo['items']) && !empty($cartinfo['items'])) {
            $item_position = 1;
            foreach ($cartinfo['items'] as $single_item) { ?>
        ga('ec:addProduct', {
            'id': '<?=$single_item['id']?>',
            'name': '<?=str_replace("'", "", $single_item['product_title'])?>',
            'category': '<?=$single_item['product_type']?>',
            //'brand': product.brand,
            'variant': '<?=str_replace("'", "", $single_item['variant_title'])?>',
            'price': parseFloat(
                    '<?=$single_item['price']/100?>'),
            'quantity': parseInt(
                    '<?=$single_item['quantity']?>'),
            'position': parseInt('<?=$item_position?>')
        });
        <?php }
            $item_position++;
        }?>

        ga('ec:setAction', 'checkout', {
            'step': step_num, // A value of 1 indicates this action is first checkout step.
            'option': option_title
        });
        ga('send', 'pageview');

        <?php }?>
    }

    function google_ana_purchase_done(id, affiliation, revenue, tax, shipping, coupon) {
        <?php if (isset($cs_data->cs_google_analytics_tracking_id) && !empty($cs_data->cs_google_analytics_tracking_id)) { ?>
        (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', '<?=$cs_data->cs_google_analytics_tracking_id?>');
        ga('require', 'ec');
        ga('set', 'currencyCode',
                '<?=$shop_currency_name?>'); // Set currency to Euros.

        <?php if (isset($cartinfo['items']) && !empty($cartinfo['items'])) {
            $item_position = 1;
            foreach ($cartinfo['items'] as $single_item) { ?>
        ga('ec:addProduct', {
            'id': '<?=$single_item['id']?>',
            'name': '<?=str_replace("'", "", $single_item['product_title'])?>',
            'category': '<?=$single_item['product_type']?>',
            //'brand': product.brand,
            'variant': '<?=$single_item['variant_title']?>',
            'price': parseFloat(
                    '<?=$single_item['price']/100?>'),
            'quantity': parseFloat(
                    '<?=$single_item['quantity']?>'),
            'position': parseInt('<?=$item_position?>')
        });
        <?php }
            $item_position++;
        }?>

        ga('ec:setAction', 'purchase', {
            'id': id, //T12345
            'affiliation': affiliation, //Google Store - Online
            'revenue': revenue, //37.39
            'tax': tax, //2.85
            'shipping': shipping, //5.34
            'coupon': coupon // SUMMER2013 - User added a coupon at checkout.
        });
        ga('send', 'pageview');

        <?php }?>
    }

</script>