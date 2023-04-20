<?php
$logo_link = "/cart";
$qa_cart_link = "/cart";
?>

<!DOCTYPE html>
<html lang="en" class="anyflexbox">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="<?= (isset($cs_data->cs_page_description) && !empty($cs_data->cs_page_description))?$cs_data->cs_page_description: '' ?>">
    <meta name="author" content="">
    <meta name="keyword" content="">

    <?php if(isset($cs_data->cs_favicon_image) && !empty($cs_data->cs_favicon_image)){ ?>
    <link rel="shortcut icon" href="<?=$cs_data->cs_favicon_image?>">
    <?php }?>

    <title><?= (isset($cs_data->cs_page_title) && !empty($cs_data->cs_page_title))?$cs_data->cs_page_title:$page_meta_title ?> </title>

    {{--<script type="text/javascript">
        var SITEURL = "<?php echo SITEURL; ?>";
        var ADMINURL = "<?php echo ADMINURL; ?>";
    </script>--}}

    <link href="{{$checkout_assets_url}}/css/master_main.css?<?php echo time() ?>" rel="stylesheet" type="text/css" />
    <link href="{{$checkout_assets_url}}/css/sq-payment-form.css?<?php echo time() ?>" rel="stylesheet" type="text/css" />

    <script src="{{$checkout_assets_url}}/js/jquery-3.6.0.min.js?time=<?= time() ?>"></script>
    <script src="{{$checkout_assets_url}}/js/sweetalert2.min.js?time=<?= time() ?>"></script>

    <style>
        .uni-form-input .form-control{
            background-color: #fff !important;
        }
        .uni-form-input.radio{
            background-color: #fff !important;
        }
        .uni-form-input span{
            z-index: 2 !important;
        }
        .form-tab .tablinks.active{
            font-weight: 800;
        }


        <?php if(isset($cs_data) && !empty($cs_data)){ ?>

        <?php if($cs_data->cs_main_content_bg_image!=''){ ?>
        body{
            background: url(<?=$cs_data->cs_main_content_bg_image?>) center top;
        }
        <?php }else if($cs_data->cs_main_content_bg_color!=''){ ?>
        body{
            background: <?=$cs_data->cs_main_content_bg_color?> !important;
        }
        <?php } ?>

        <?php if($cs_data->cs_order_summary_bg_image!=''){ ?>
        .sidebar::after{
            background: url(<?=$cs_data->cs_order_summary_bg_image?>) center top;
        }
        <?php }else if($cs_data->cs_order_summary_bg_color!=''){ ?>
        .sidebar::after{
            background: <?=$cs_data->cs_order_summary_bg_color?> !important;
        }
        <?php } ?>
        <?php if($cs_data->cs_order_summary_bg_color!=''){ ?>
        .order-summary-toggle{
            background: <?=$cs_data->cs_order_summary_bg_color?> !important;
            border-top: 1px solid <?= $cs_data->cs_text_color!='' ? $cs_data->cs_text_color : 'inherit'; ?> !important;
            border-bottom: 1px solid <?= $cs_data->cs_text_color!='' ? $cs_data->cs_text_color : 'inherit'; ?> !important;
            color: <?= $cs_data->cs_text_color!='' ? $cs_data->cs_text_color : 'inherit'; ?> !important;
        }
        .order-summary-toggle__text{
            color: <?= $cs_data->cs_text_color!='' ? $cs_data->cs_text_color : 'inherit'; ?> !important;
        }
        .order-summary-toggle__dropdown, .order-summary-toggle__icon{
            fill: <?= $cs_data->cs_text_color!='' ? $cs_data->cs_text_color : 'inherit'; ?> !important;
        }
        <?php } ?>

        <?php if($cs_data->cs_fonts!=''){ ?>
        body, button, h1, h2, h3, h4, h5, h6, input, select, textarea{
            font-family: <?=$cs_data->cs_fonts?>;
        }
        <?php } ?>

        <?php if($cs_data->cs_text_color!=''){ ?>
        body,
        .sq-label,
        .payment-due__price,
        .review-block__label,
        .form-tab .tablinks, .sidebar{
            color: <?=$cs_data->cs_text_color?> !important;
        }
        <?php } ?>

        <?php if($cs_data->cs_button_color!=''){ ?>
        .submit.btn{
            background: <?=$cs_data->cs_button_color?> !important;
        }
        .form-footer a,
        .form-footer svg{
            color: <?=$cs_data->cs_button_color?> !important;
        }
        <?php } ?>

        <?php if($cs_data->cs_button_text_color!=''){ ?>
        .submit.btn{
            color: <?=$cs_data->cs_button_text_color?> !important;
        }
        <?php } ?>

        <?php }?>

        @media only screen and (max-width: 992px) {
            .order-summary-toggle__text {
                color: #b66c34 !important;
            }
            .order-summary-toggle__dropdown, .order-summary-toggle__icon {
                fill: #b66c34 !important;
                -webkit-transition: fill 0.2s ease-in-out;
                transition: fill 0.2s ease-in-out;
            }
            .checkout-page {
                margin-top: 0rem!important;
            }
        }
        @media only screen and (max-width: 749px) {
            .form-group.form-footer {
                display: flex;
                flex-direction: column-reverse;
                flex-wrap: wrap;
                text-align: center;
                width: 100%;
            }
            #continue_to_ship_method_btn, #cont_to_pay_method, #complete-order-btn {
                width: 100%;
            }
            .review-block__inner {
                -webkit-flex-wrap: wrap;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                display: flex;
                flex-direction: column;
            }
        }
    </style>
    <style>
        body{
            line-height: 1.3em;
            font-size: 14px;
            word-wrap: break-word;
            word-break: break-word;
            -webkit-font-smoothing: subpixel-antialiased;
        }

        #product_summery_table h6{
            font-size: 20px;
            line-height: 1.2;
        }
        .product__description h6 {
            font-size: 20px;
            margin: 0px 0;
            font-weight: 400;
        }

        .product-table .product__price {
            text-align: right;
            font-family: Helvetica Now Display;
            font-size: 14px;
            min-width: 46px;
        }
        .product__description span {
            font-size: 12px;
            font-family: Helvetica Now Text;
            line-height: 1.3em;
        }
        .total-line__price span#cart_total_price {
            letter-spacing: -0.04em;
            line-height: 1em;
            font-size: 25px!important;
            font-family: Helvetica Now Display;
        }
        #continue_to_ship_method_btn[disabled] {
            background-color: #cccccc !important;
        }
        #continue_to_ship_method_btn, #cont_to_pay_method, #complete-order-btn {
            /*background-color: #ebaf5a !important;*/
            border: 1px transparent solid;
            /*color: #2f2312 !important;*/
            border-radius: 0;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 0;
        }
        .form-group.form-footer {
            margin-top: 15px;
        }
        #continue_to_ship_method_btn:hover, #cont_to_pay_method:hover, #complete-order-btn:hover{
            border: 1px solid #fff;
            background-color: #000 !important;
            color: #fff !important;
        }
        .form-footer a, .form-footer a .icon-svg--color-accent{
            text-decoration: none;
            color: #ebaf5a !important;
            -webkit-transition: color 0.2s ease-in-out;
            transition: color 0.2s ease-in-out;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            font-size: 17px!important;
            vertical-align: middle;
        }
        .form-footer a:hover, .form-footer a .icon-svg--color-accent:hover{
            color: #9a5c2c !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
        }
        label[for="email_marketing_subscribe"] h4{
            font-size: 14px!important;
            color: #000;
            font-weight: 400;
        }
        .total-line__name.additional_content{
            border: 2px solid #8d8c89;
            border-radius: 5px;
            padding: 15px;
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .right-bottom-text p {
            font-size: 15px;
            letter-spacing: .05rem;
            line-height: 1.5em;
            margin: 0;
        }
        .disclaimer_links {
            /*display: flex;*/
            font-size: 14px;
            margin-top: 15px;
        }
        .disclaimer_links p {
            margin: -3px 10px 0;
        }
        .order-summary__section--total-lines a{
            text-decoration: underline;
        }
        .disclaimer_links a {
            text-decoration: none;
        }
        input, select, textarea {
            font-size: 14px;
            color: #09262d!important;
        }
        #shpping_rates_list .radio-box .radio__label span{
            color:#545454 !important;
        }
        #shpping_rates_list .radio-box .radio__label span.radio__label__secondary{
            color: #737373!important;
            font-size: 0.8571428571em !important;
            line-height: 1.3em !important;
        }
        #shpping_rates_list .radio__label{
            line-height: 1.3em;
        }
        .discount_code .btn--disabled {
            cursor: default;
            background: #567177 !important;
            -webkit-box-shadow: none !important;
            box-shadow: none;
            text-transform: uppercase;
            border-radius: 0;
        }
        /* tfoot.total-line-table__footer td, tfoot.total-line-table__footer th {
            padding-bottom: 3.8em;
        } */
        th.total-line__name.payment-due-label {
            font-size: 18px!important;
        }

        .order-summary__section--total-lines th.total-line__name.payment-due-label.discount_code {
            padding: 0;
        }

        span#shop_currency {
            font-size: 12px;
            margin-right: 0.5em;
            vertical-align: bottom;
        }


        .logo.logo-desktop {
            /*padding: 20px 0;*/
            margin-bottom: 0;
            /*padding-top: 60px;*/
        }

        .checkout-page {
            margin-top: 3.5rem!important;
        }
        .form-tab .tablinks {
            font-weight: 500;
            color: #000;
            font-size: 16px;
        }
        .form-tab {
            padding-bottom: 2em;
        }
        h2.sec-title {
            font-size: 22px;
            font-weight: 400;
            margin-bottom: 1.5em;
            margin-top: 0;
            line-height: 1.3em;
        }
        .uni-form-input::-webkit-input-placeholder {
            color: #737373;
        }
        .uni-form-input span {
            top: 14px;
            font-size: 14px;
            color: #737373;
        }
        .uni-form-input input.form-control {
            padding-top: 1.3em;
            padding-bottom: 0.3571428571em;
            font-size: 14px;
            color: #09262d!important;
        }
        .form-group .uni-form-input span {
            font-size: 14px;
            color: #737373 !important;
        }

        h2.sec-title small {
            font-size: 15px;
            display: block;
            color: #333333;
            margin-top: 10px;
            line-height: 19px;
        }

        .section.section--shipping-address h2.sec-title {
            margin-bottom: 10px;
            margin-top: 10px;
            font-size: 22px;

        }

        .shipping-form label.uni-form-input h4 {
            padding-left: 26px;
        }

        .shipping-form input#email_marketing_subscribe {
            /* padding-right: 0.75em; */
            white-space: nowrap;
            position: absolute;
            left: 0;
            border-color: #d9d9d9;
            background-color: white;
        }

        .uni-form-input input.form-control:focus, input:focus {
            outline: none;
            border-color: #b66c34;
            -webkit-box-shadow: 0 0 0 1px #b66c34;
        }

        .form-footer>a:hover .icon-svg--color-accent {
            fill: #9a5c2c !important;
            -webkit-transform: inherit;
            transform: inherit;
        }

        .form-footer a, .form-footer a .icon-svg--color-accent {
            vertical-align: initial;
        }


        input#discount_code {
            padding: 10px 15px;
        }

        .edit-details {
            background-color: transparent !important;
            font-size: 14px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            line-height: 1.3em;
            margin-top: 0;
        }

        .review-block__label {
            color: #4d4d4d !important;
            font-weight: 400;
        }
        <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
        .review-block__label {max-width:120px !important;}
        <?php }?>

        .review-block__link a {
            color: #000;
            font-size: 14px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            line-height: 1.3em;
            font-weight: normal;
        }

        #continue_to_ship_method_btn:hover, #cont_to_pay_method:hover {
            border: 1px solid #fff !important;
            background-color: #000 !important;
            color: #fff !important;
        }
        .section.section--billing-detail label.radio__label span {
            font-weight: 500;
            color: #333333 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            line-height: 1.3em;
        }

        .review-block__content.shipping_email_div, .review-block__content.shipping_address_div, .review-block__content.shipping_method_div {
            color: #333333;
            font-size: 14px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            line-height: 1.3em;
        }

        .section.section--payment-detail h2.sec-title, .section.section--billing-detail h2.sec-title {
            margin-bottom: 1.5rem;
            margin-top: 2rem;
        }

        .section.section--shipping-detail h2.sec-title {
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        span.af_after_loading {
            text-transform: uppercase;
        }
        footer.main__footer {
            padding: 5px 0 30px;
            border-top: 1px solid #e6e6e6;
        }

        span.af_after_loading {
            text-transform: uppercase;
        }

        ul.policy-list {
            zoom: 1;
            text-align: center;
            gap: 20px;
        }
        .policy-list:after, .policy-list:before {
            content: "";
            display: table;
        }

        .policy-list__item {
            float: left;
            font-size: 0.8571428571em;
            margin-right: 1.5em;
            margin-bottom: 0.5em;
        }

        li.policy-list__item a, li.policy-list__item p {
            color: #ebaf5a;
            font-size: 12px;
        }

        .legal li a, .legal li p{
            color: #ebaf5a;
            font-size: 12px;
        }
        a, .link {
            text-decoration: none;
            color: #b66c34;
            -webkit-transition: color 0.2s ease-in-out;
            transition: color 0.2s ease-in-out;
        }
        ul, ol {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }
        li {
            display: inline-block;
        }

        ul.legal {
            display: inline-block;
            width: 100%;
        }

        .main {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-flex: 1;
            -webkit-flex: 1 0 auto;
            -ms-flex: 1 0 auto;
            flex: 1 0 auto;
        }

        .main {
            width: 52%;
            padding-right: 6%;
            float: left;
        }

        .title_modal__header {
            display: table;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            width: 100%;
            border-bottom: 1px solid #e6e6e6;
            zoom: 1;
            padding: 20px 20px 20px;
            color: #545454;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif !important;
            line-height: 1.3em;
            font-size: 2em;
            font-weight: normal;
        }

        .container_modal__header .swal2-popup {
            padding: 0;
        }

        .container_modal__header .swal2-content {
            padding: 1.5em;
        }

        .container_modal__header {
            background: rgba(0,0,0,.4);
            background-color: rgba(0,0,0,0.6);
            background-color: rgba(0,0,0,0.6);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
        }

        .container_modal__header .swal2-close {
            position: absolute;
            z-index: 2;
            top: 15px;
            right: 10px;
        }
        .pac-logo:after {
            background-position: 15px !important;
            text-align: left !important;
            padding: 20px !important;
            background-color: rgba(0,0,0,0.02);
        }

        tr.total-line.total-line--shipping svg.bi.bi-info-circle {
            color: #a5b3b7;
            display: inline-block;
            vertical-align: text-top;
            fill: currentColor;
            width: 14px;
            height: 14px;
            cursor: pointer;
            margin-left: 0.2857142857em;
        }
        tr.total-line.total-line--shipping .checkout_tooltip svg.bi.bi-info-circle {
            width: 14px;
            height: 14px;
        }
        tr.total-line.total-line--shipping svg.bi.bi-info-circle:hover {
            color: #9a5c2c;
        }

        .checkout_tooltip {
            position: relative;
            display: inline-block;
        }

        .checkout_tooltip-right::after {
            content: "";
            position: absolute;
            top: 14%;
            right: 100%;
            margin-top: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent #fff transparent transparent;
        }

        .checkout_tooltip .checkout_tooltiptext {
            visibility: hidden;
            width: 220px;
            background-color: white;
            color: #194149;
            text-align: left;
            border-radius: 6px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            top: -7px;
            left: 140%;
            line-height: 1.3em;
            font-size: 14px;
            padding: 5px 15px;

        }

        .checkout_tooltip:hover .checkout_tooltiptext {
            visibility: visible;
        }

        tr.total-line.total-line--shipping .checkout_tooltip svg.bi.bi-info-circle {
            background: #fff;
            overflow: hidden;
            border-radius: 10px;
            color: #100f0f !important;
            margin-left: 2px !important;
            cursor: pointer;
            padding: 0.9px;
        }

        .pac-item {
            cursor: default;
            padding: 0 4px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            line-height: 30px;
            text-align: left;
            border-top: 1px solid #e6e6e6;
            font-size: 11px;
            color: #515151;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            text-align: left;
            width: 100%;
            padding: 10px 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pac-item:hover {
            background-color: rgba(0,0,0,0.08);
        }


        .notice {
            position: relative;
            display: table;
            opacity: 1;
            margin-bottom: 1.4285714286em;
            padding: 1em;
            border-radius: 4px;
            border: 1px solid #d3e7f5;
            background-color: #eff8ff;
            color: #545454;
            -webkit-transition: opacity 0.5s ease-in-out;
            transition: opacity 0.5s ease-in-out;
        }

        .notice--error {
            border-color: #fad9d9;
            background-color: #ffebeb;
        }
        .notice--error .notice__icon {
            color: #e22120;
        }
        .notice__content {
            display: table-cell;
            width: 100%;
            padding-right: 1.1428571429em;
        }
        .notice .icon-svg--size-24 {
            width: 24px;
            height: 24px;
        }
        .notice .icon-svg {
            display: inline-block;
            vertical-align: middle;
            fill: currentColor;
        }
        .field__caret {
            border-left: 1px rgba(179,179,179,0.5) solid;
        }

        .field__caret .icon-svg--color-adaptive-lighter {
            /*color: #004d5a!important;*/
            width: 10px!important;
        }
        .field__caret {
            display: block;
            width: 2.1428571429em;
            height: 43%;
            pointer-events: none;
            position: absolute;
            top: 50%;
            right: 0;
            -webkit-transform: translate(0%, -50%);
            transform: translate(0%, -50%);
        }
        .field__caret-svg {
            position: absolute;
            margin-left: -2px;
            top: 50%;
            left: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        .icon-svg--size-10 {
            width: 12px;
            height: 12px;
            margin: 0;
        }
        .icon-svg {
            display: inline-block;
            vertical-align: middle;
            fill: currentColor;
        }


        .tag__wrapper {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            width: 100%;
        }

        .icon-svg--size-18 {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        button.tag__button {
            color: inherit;
            font: inherit;
            margin: 0;
            padding: 0;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            -webkit-font-smoothing: inherit;
            border: none;
            background: transparent;
            line-height: normal;
            margin-left: 0.8571428571em;
        }

        .icon-svg--color-adaptive-light {
            color: #a5b3b7;
            fill: currentColor;
        }
        .reduction-code__icon {
            margin-right: 0.1428571429em;
            vertical-align: top;
        }

        .list__discount__none {
            display: none;
        }

        .tag__discount {
            border-radius: 4px;
            background-color: rgba(165,179,183,0.11);
            color: #a5b3b7;
            font-size: 0.8571428571em;
            padding: 0.74rem;
            overflow: hidden;
            margin-top: 0.8571428571em;
            border-spacing: inherit;
        }
        .tags-list__discount {
            width: 100%;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
        }

        .tag__button .icon-svg {
            stroke: rgba(165,179,183,0.9);
        }
        .tag__button {
            margin-left: 0.8571428571em;
        }
        .reduction-code__text {
            font-size: 1em;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
            line-height: 1.3em;
        }
        .tag__text {
            font-weight: 500;
            overflow: hidden;
            display: inline-block;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .main-div-discount-code-mobile span.reduction-code__text {
            color: #000000	;
        }

        .main-div-discount-code-mobile .icon-svg--color-adaptive-light {
            color: #0000009c;
            fill: currentColor;
            vertical-align: middle;
        }

        .main-div-discount-code-mobile .tag__button .icon-svg {
            stroke: #0000009c;
        }

        .main-div-discount-code-mobile .tag__discount {
            background-color: rgb(165 179 183 / 36%);
        }

        .icon-svg--size-12 {
            width: 12px;
            height: 12px;
            margin: 0;
            stroke: rgba(165,179,183,0.9);
        }

        .tags-list .tag {
            margin-top: 0.8571428571em;
            margin-right: 0.8571428571em;
        }
        .tags-list .tag:last-child {
            margin-right: 0;
        }

        svg.icon-svg.icon-svg--size-18.btn__spinner.icon-svg--spinner-button {
            fill: #2f2312;
            width: 20px;
            height: 20px;
        }

        .btn__spinner {
            -webkit-animation: rotate 0.5s linear infinite;
            animation: rotate 0.5s linear infinite;
            opacity: 1;
        }

        .content-box.blank-slate {
            border-color: #d9d9d9;
            background: #fff;
            background-clip: padding-box;
            border: 1px solid #d9d9d9;
            border-radius: 5px;
            color: #545454;
            padding: 1.1428571429em;
            text-align: center;
            z-index: 10999;
            margin-bottom: 1.5em;
        }

        .content-box.blank-slate .icon-svg--size-32 {
            width: 32px;
            height: 32px;
            color: #b66c34;
            fill: currentColor;
        }

        .content-box.blank-slate .blank-slate__icon {
            margin-bottom: 1.1428571429em;
        }
        .content-box.blank-slate h3 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif !important;
            font-size: 1em;
            font-weight: 500;
            line-height: 1.3em;
            color: #333333;
            margin: 0px;
        }

        .content-box.blank-slate .icon-svg--spinner {
            -webkit-animation: fade-in 0.5s ease-in-out, rotate 0.5s linear infinite;
            animation: fade-in 0.5s ease-in-out, rotate 0.5s linear infinite;
        }



        @-webkit-keyframes rotate {
            0% {
                -webkit-transform: rotate(0);
                transform: rotate(0);
            }
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        .order-summary__section--total-lines.main-div-discount-code-mobile {
            display: none;
        }

        @media only screen and (min-width: 992px) {
            .sidebar {
                width: 42%;
            }
            .logo a {
                padding: 0 !important;
            }
        }

        @media only screen and (max-width: 991px) {
            .section.section--shipping-detail h2.sec-title {
                margin-bottom: 20px;
                margin-top: 15px;
            }
            .form-group.form-footer {
                margin-top: 15px;
                margin-bottom: 0;
            }

            h2.sec-title {
                margin-bottom: 0.9rem;
            }
            h2.sec-title small {
                margin-top: 2px;
            }
            .section.section--shipping-address h2.sec-title {
                margin-bottom: 0;
                margin-top: 1rem;
            }
            .shipping-form label.uni-form-input h4 {
                line-height: 1.8em;
            }
            .review-block__label {
                padding-bottom: 0.2857142857em;
                color: #737373 !important;
                font-size: 14px;
            }

            .order-summary__section--total-lines.main-div-discount-code-mobile {
                display: block;
            }
            .order-summary__section--total-lines.main-div-discount-code-mobile input.form-control {
                padding: 0.9285714286em 0.7857142857em;
                font-size: 14px;
                color: #09262d!important;
                line-height: inherit;
            }

            .order-summary__section--total-lines.main-div-discount-code-mobile .discount_code .btn--disabled {
                cursor: default;
                background: #cccccc !important;
                -webkit-box-shadow: none !important;
                box-shadow: none !important;
            }

            svg.icon-svg.icon-svg--size-16.btn__icon.shown-on-mobile {
                width: 20px!important;
                color: #2f2312;
                height: 20px;
            }

            .order-summary__section--total-lines.main-div-discount-code-mobile {
                margin-bottom: 0;
            }

            .main-div-discount-code-mobile h2.section__title {
                font-size: 22px;
                font-weight: 400;
                color: #333333;
                margin: 0;
                margin-bottom: 1em;
                padding-top: 15px;
            }
            .main-div-discount-code-mobile span#discount-msg-mobile {
                line-height: 1.3em;
                margin: 0.5714285714em 0 0.2857142857em;
                color: #142b3b;
                font-size: 14px;
            }
            .main-div-discount-code-mobile th.total-line__name.payment-due-label {
                padding: 0;
            }
        }

        .gift_icon {
            width: 11%;
        }
        .gift_icon_flex {
            display: flex;
            align-items: flex-end;
            padding-bottom: 20px;
            gap: 15px;
        }
        .order-summary_flex {
            width: 80%;
        }
        .swal-footer {
            text-align: center !important;
        }

    </style>

</head>
<body class="checkout-form-body">

<main id="checkout-page" class="wrapper">
    <!-- Form -->
    <div class="logo-mobile logo"
         style="margin-bottom: 0px;<?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?>
                 background-image: url(<?= $cs_data->cs_banner_image ?>);
                 background-repeat: no-repeat;
                 background-size: cover;
                 background-position: center center;
         <?php } ?><?= isset($logo_position) ? " text-align:{$logo_position};" : ''; ?>">
		<span class="custom-container">
			<a href="<?=$logo_link?>"
               style="padding-top: 1.5em; padding-bottom: 1.5em;">
                <?php if (isset($cs_data->cs_logo_image) && !empty($cs_data->cs_logo_image)) { ?>
                <img src="<?= $cs_data->cs_logo_image ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width:auto;max-height: 2.85714em;">
                <?php } else if (isset($is_data->ss_logo) && !empty($is_data->ss_logo)) { ?>
                <img src="<?= $is_data->ss_logo ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width:auto;max-height: 2.85714em;">
                <?php }  ?>
            </a>
		</span>
    </div>
    <button class="resp-summury order-summary-toggle">
		<span class="custom-container">
			<span class="order-summary-toggle__icon-wrapper">
				<svg width="20" height="19" xmlns="http://www.w3.org/2000/svg" class="order-summary-toggle__icon">
                    <path
                            d="M17.178 13.088H5.453c-.454 0-.91-.364-.91-.818L3.727 1.818H0V0h4.544c.455 0 .91.364.91.818l.09 1.272h13.45c.274 0 .547.09.73.364.18.182.27.454.18.727l-1.817 9.18c-.09.455-.455.728-.91.728zM6.27 11.27h10.09l1.454-7.362H5.634l.637 7.362zm.092 7.715c1.004 0 1.818-.813 1.818-1.817s-.814-1.818-1.818-1.818-1.818.814-1.818 1.818.814 1.817 1.818 1.817zm9.18 0c1.004 0 1.817-.813 1.817-1.817s-.814-1.818-1.818-1.818-1.818.814-1.818 1.818.814 1.817 1.818 1.817z">
                    </path>
                </svg>
			</span>
			<span class="order-summary-toggle__text order-summary-toggle__text--show" id="show_summary_text">
				<span>Show order summary</span>
				<svg width="11" height="6" xmlns="http://www.w3.org/2000/svg" class="order-summary-toggle__dropdown"
                     fill="#000">
                    <path
                            d="M.504 1.813l4.358 3.845.496.438.496-.438 4.642-4.096L9.504.438 4.862 4.534h.992L1.496.69.504 1.812z">
                    </path>
                </svg>
			</span>
			<span class="order-summary-toggle__text order-summary-toggle__text--hide" id="hide_summary_text">
				<span>Hide order summary</span>
				<svg width="11" height="7" xmlns="http://www.w3.org/2000/svg" class="order-summary-toggle__dropdown"
                     fill="#000">
                    <path
                            d="M6.138.876L5.642.438l-.496.438L.504 4.972l.992 1.124L6.138 2l-.496.436 3.862 3.408.992-1.122L6.138.876z">
                    </path>
                </svg>
			</span>
			<span class="order-summary-toggle__total-recap total-recap" data-order-summary-section="toggle-total-recap">
				<span class="total-recap__strike-price" id="mobile_total_strike_price" style="display:none;"></span>
				<span class="total-recap__final-price" id="mobile_total_final_price"
                      data-checkout-payment-due-target="<?= ($cartinfo['total_price'] > 0 ? $cartinfo['total_price'] : 0) ?>">$<?= ($cartinfo['total_price'] > 0 ? ($cartinfo['total_price'] / 100) : 0.00) ?></span>
			</span>
		</span>
    </button>



    <?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?>
    <div class="logo logo-desktop" style="margin-bottom 0px; text-align: <?= $logo_position ?>; <?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?>
            background-image: url(<?= $cs_data->cs_banner_image ?>);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
    <?php } ?>">
        <div class="custom-container">
            <a href="<?=$logo_link?>"
               style="display:block;width:100%;padding-top: 80px; padding-bottom: 40px;">
                <?php if (isset($cs_data->cs_logo_image) && !empty($cs_data->cs_logo_image)) { ?>
                <img src="<?= $cs_data->cs_logo_image ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width: <?= $logo_size ?>;">
                <?php } else if (isset($is_data->ss_logo) && !empty($is_data->ss_logo)) { ?>
                <img src="<?= $is_data->ss_logo ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width: <?= $logo_size ?>;">
                <?php }  ?>
            </a>
        </div>
    </div>
    <?php } ?>
    <form method="post" id="checkout-frm" class="">
        <div class="custom-container">
            <div class="main">
                <div class="main-content"
                     style="<?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?> padding-top:2em; <?php } ?>">

                    <div class="logo logo-desktop"
                         style="<?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?>display:none;<?php } ?><?= isset($logo_position) ? " text-align:{$logo_position};" : ''; ?>">
                        <a href="<?=$logo_link?>">
                            <?php if (isset($cs_data->cs_logo_image) && !empty($cs_data->cs_logo_image)) { ?>
                            <img src="<?= $cs_data->cs_logo_image ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width: <?= $logo_size ?>;">
                            <?php }else if (isset($is_data->ss_logo) && !empty($is_data->ss_logo)) { ?>
                            <img src="<?= $is_data->ss_logo ?>" alt="shop-logo" class="img-fluid" id="shop_logo" style="width: <?= $logo_size ?>;">
                            <?php }else  ?>
                        </a>
                    </div>
                    <div class="checkout-page">

                        <div class="form-tab">
                            <a onclick="
                                    js:window.location.href = '<?=$qa_cart_link?>';"
                               class="tablinks cart__link"> Cart <svg class="icon-svg" role="img" xmlns="http://www.w3.org/2000/svg"
                                                                      viewBox="0 0 10 10">
                                    <path d="M2 1l1-1 4 4 1 1-1 1-4 4-1-1 4-4"></path>
                                </svg>
                            </a>
                            <a class="tablinks" onclick="openCity(event, 'tab-1')" id="defaultOpen"> Information <svg
                                        class="icon-svg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10">
                                    <path d="M2 1l1-1 4 4 1 1-1 1-4 4-1-1 4-4"></path>
                                </svg>
                            </a>
                            <?php if (!isset($sms_multi_shipping_lines_arr)) { ?>
                            <a class="tablinks" onclick="if (validateCustomerInfo()) {
                                openCity(event, 'tab-2');
                            }" id='tab-2-open'> Shipping <svg class="icon-svg" role="img"
                                                              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10">
                                    <path d="M2 1l1-1 4 4 1 1-1 1-4 4-1-1 4-4"></path>
                                </svg>
                            </a>
                            <?php }?>
                            <a class="tablinks" onclick="if (validateCustomerInfo()) {
                                openCity(event, 'tab-3');
                            }" id='tab-3-open'> Payment
                            </a>
                        </div>

                        <?php if (isset($oos_inventory_var_arr) && !empty($oos_inventory_var_arr)) { ?>
                        <div class="step" data-step="stock_problems" data-last-step="false">
                            <div class="step__sections">
                                <div class="section section--page-title">
                                    <svg focusable="false" aria-hidden="true" class="icon-svg icon-svg--size-48 exclamation-mark section__hanging-icon" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" stroke-width="2"><circle class="exclamation-mark__circle" cx="25" cy="25" r="24" fill="none"></circle><path class="exclamation-mark__line" d="M25 12v18"></path><circle class="exclamation-mark__dot" cx="25" cy="37" r="1"></circle></svg>
                                    <h2 class="section__title" id="main-header" tabindex="-1">
                                        Out of stock
                                    </h2>

                                    <p class="section__text">
                                        Some items are no longer available. Please return to cart and remove below product(s).
                                    </p>
                                </div>
                                <div class="section">
                                    <div class="section__content">
                                        <table class="product-table product-table--bordered product-table--extra-loose stock-problem-table">
                                            <thead class="product-table__header">
                                            <tr>
                                                <th scope="col" colspan="2">Description</th>
                                                <th scope="col">Quantity</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($oos_inventory_var_arr as $oos_var) { ?>
                                            <tr>
                                                <td class="product__image">
                                                    <div class="product-thumbnail ">
                                                        <div class="product-thumbnail__wrapper">
                                                            <img alt="<?=$oos_var['title']?>" class="product-thumbnail__image" src="<?=$oos_var['image']?>">
                                                        </div>
                                                        <span class="product-thumbnail__quantity" aria-hidden="true"><?=$oos_var['quantity']?></span>
                                                    </div>
                                                </td>
                                                <th class="product__title" scope="row">
                                                    <span class="product__description__name page-main__emphasis"><?=$oos_var['title']?></span>
                                                </th>
                                                <td class="product__status product__status--sold-out">
                                                    <svg class="icon-svg icon-svg--size-14 icon-svg--inline-before product__status__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14"><g stroke="currentColor" stroke-width="2" stroke-linecap="square" fill="none"><circle cx="7" cy="7" r="6"></circle><path d="M11.066 2.934l-7.628 7.628"></path></g></svg>
                                                    Sold out
                                                </td>
                                            </tr>
                                            <?php }?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group form-footer">
                                <a href="#tab1"
                                   onclick="
                                           js:window.location.href = '<?= $qa_cart_link ?>';">
                                    <svg class="icon-svg--color-accent" role="img" xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 10 10">
                                        <path d="M8 1L7 0 3 4 2 5l1 1 4 4 1-1-4-4"></path>
                                    </svg>
                                    Return to cart
                                </a>
                            </div>
                        </div>
                        <?php } else { ?>

                        <div class="tabcontent" id="tab-1">
                            <div class="shipping-form">
                                <div class="section section--contact-information">

                                    <div class="form-group">
                                        <h2 class="sec-title">
                                            <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                            Your Contact Information
                                            <?php }else{ ?>
                                            Contact information
                                            <?php }?>
                                        </h2>
                                        <label for="checkout_email" class="uni-form-input">
                                            <input type="text" id="checkout_email" name="checkout_email" class="form-control"
                                                   autocomplete="shipping email"
                                                   value="<?= isset($ac_data->ac_customer_email) ? $ac_data->ac_customer_email : ''; ?>"
                                                   data-empty="<?= (isset($ac_data->ac_customer_email) && !empty($ac_data->ac_customer_email)) ? 'false' : 'true'; ?>">
                                            <span>Email</span>
                                        </label>
                                    </div>


                                    <?php
                                    $is_prfilled = isset($cs_data->cs_email_marketing_preselect)?$cs_data->cs_email_marketing_preselect:"";
                                    if ($is_prfilled == 'Yes') {
                                    $is_prfilled = 'checked';
                                    } else {
                                    $is_prfilled = '';
                                    }
                                    ?>
                                    <?php if (isset($cs_data->cs_email_marketing_status) && $cs_data->cs_email_marketing_status == 'enable') { ?>
                                    <div class="form-group">
                                        <label for="email_marketing_subscribe" class="uni-form-input">
                                            <h4 style="margin: 0;">
                                                <input type="checkbox" id="email_marketing_subscribe"
                                                <?php echo $is_prfilled; ?>
                                                       name="email_marketing_subscribe" class="input-checkbox" value="1">
                                                <?= $cs_data->cs_email_marketing_title ?>
                                            </h4>
                                            <p><?= $cs_data->cs_email_marketing_content ?>
                                            </p>
                                        </label>
                                    </div>
                                    <?php } ?>


                                    <div class="form-group">
                                        <label for="po_number" class="uni-form-input">
                                            <input type="text" id="po_number" name="po_number" class="form-control" autocomplete="shipping po_number" value="" data-empty="true">
                                            <span>Cost Center Number</span>
                                        </label>
                                    </div>

                                </div>

                                <div class="section section--shipping-address">
                                    <div style="<?= ((isset($sms_multi_shipping_lines_arr) && !empty($sms_multi_shipping_lines_arr)) || (isset($is_in_person_event) && $is_in_person_event=='Yes'))?"display:none;":"" ?>">
                                        <div class="form-group">
                                            <h2 class="sec-title">
                                                Delivery Address
                                            </h2>
                                        </div>
                                        <div class="notice notice--error default-background" id="vendor_shipping_error_div" style="display: none;">
                                            <svg class="icon-svg icon-svg--size-24 notice__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 24C5.373 24 0 18.627 0 12S5.373 0 12 0s12 5.373 12 12-5.373 12-12 12zm0-2c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-16c.552 0 1 .448 1 1v5c0 .552-.448 1-1 1s-1-.448-1-1V7c0-.552.448-1 1-1zm-1.5 10.5c0-.828.666-1.5 1.5-1.5.828 0 1.5.666 1.5 1.5 0 .828-.666 1.5-1.5 1.5-.828 0-1.5-.666-1.5-1.5z"></path></svg>
                                            <div class="notice__content">
                                                <p class="notice__text" id="vendor_shipping_error_msg"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" id="shipping_country_div">
                                            <div class="uni-form-input select">
                                                <span>Country/region</span>
                                                <select class="form-control" id="shipping_country" name="shipping[country]">
                                                    <?php if (!empty($countries) && is_array($countries)) { ?>
                                                    <?php foreach ($countries as $country) { ?>
                                                    <?php if ($country['code'] != '*') {
                                                        if (isset($ac_data->ac_shipping_country) && $ac_data->ac_shipping_country == $country['code']) {
                                                            $selected = 'selected';
                                                        } elseif ($country['code'] == 'US') {
                                                            $selected = 'selected';
                                                        } else {
                                                            $selected = '';
                                                        }
                                                        ?>
                                                    <option <?= $selected ?>
                                                            data-provinces='<?= !empty($country['provinces']) ? json_encode($country['provinces']) : "" ?>'
                                                            value="<?= $country['code'] ?>"
                                                            data-name="<?= $country['name'] ?>"
                                                            data-taxname="<?= $country['tax_name'] ?>"
                                                            data-tax="<?= $country['tax'] ?>"
                                                            data-id="<?= $country['id'] ?>"><?= $country['name'] ?>
                                                    </option>
                                                    <?php } ?>
                                                    <?php } ?>
                                                    <?php } ?>
                                                </select>
                                                <div class="field__caret">
                                                    <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-10 field__caret-svg" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 3h10L5 8" fill-rule="nonzero"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-2">
                                            <label for="shipping_first_name" class="uni-form-input">
                                                <input type="text" id="shipping_first_name" name="shipping[first_name]"
                                                       class="form-control" autocomplete="shipping given-name"
                                                       value="<?= isset($ac_data->ac_shipping_first_name) ? $ac_data->ac_shipping_first_name : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_first_name) && !empty($ac_data->ac_shipping_first_name)) ? 'false' : 'true'; ?>">
											<span>
												<?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                Gift recipient first name
                                                <?php }else{ ?>
                                                First name
                                                <?php }?>
											</span>
                                            </label>
                                        </div>
                                        <div class="form-group col-2">
                                            <label for="shipping_last_name" class="uni-form-input">
                                                <input type="text" id="shipping_last_name" name="shipping[last_name]"
                                                       class="form-control" autocomplete="shipping family-name"
                                                       value="<?= isset($ac_data->ac_shipping_last_name) ? $ac_data->ac_shipping_last_name : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_last_name) && !empty($ac_data->ac_shipping_last_name)) ? 'false' : 'true'; ?>">
											<span>
												<?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                Gift recipient last name
                                                <?php }else{ ?>
                                                Last name
                                                <?php }?>
											</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label for="shipping_company" class="uni-form-input">
                                                <input type="text" id="shipping_company" name="shipping[company]"
                                                       class="form-control" autocomplete="shipping company"
                                                       value=""
                                                       data-empty="true"
                                                       placeholder="">
                                                <span>Company (optional)</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label for="shipping_address" class="uni-form-input">
                                                <input type="text" id="shipping_address" name="shipping[address]"
                                                       class="form-control" autocomplete="shipping address-line1"
                                                       value="<?= isset($ac_data->ac_shipping_address) ? $ac_data->ac_shipping_address : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_address) && !empty($ac_data->ac_shipping_address)) ? 'false' : 'true'; ?>"
                                                       placeholder="">
                                                <span>Address</span>
                                            </label>
                                        </div>

                                        <div class="form-group">
                                            <label for="shipping_address2" class="uni-form-input">
                                                <input type="text" id="shipping_address2" name="shipping[address2]"
                                                       class="form-control" autocomplete="shipping address-line2"
                                                       value="<?= isset($ac_data->ac_shipping_address2) ? $ac_data->ac_shipping_address2 : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_address2) && !empty($ac_data->ac_shipping_address2)) ? 'false' : 'true'; ?>">
                                                <span>Apartment, suite, etc. (optional)</span>
                                            </label>
                                        </div>
                                        <div class="form-group col-3">
                                            <label for="shipping_city" class="uni-form-input">
                                                <input type="text" id="shipping_city" name="shipping[city]" class="form-control"
                                                       autocomplete="shipping address-level2"
                                                       value="<?= isset($ac_data->ac_shipping_city) ? $ac_data->ac_shipping_city : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_city) && !empty($ac_data->ac_shipping_city)) ? 'false' : 'true'; ?>">
                                                <span>City</span>
                                            </label>
                                        </div>
                                        <div class="form-group col-3" id="shipping_state_div">
                                            <div class="uni-form-input select">
                                                <span>State</span>
                                                <select class="form-control" id="shipping_state" name="shipping[state]">

                                                </select>
                                                <div class="field__caret">
                                                    <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-10 field__caret-svg" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 3h10L5 8" fill-rule="nonzero"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-1" id="shipping_pincode_div">
                                            <label for="shipping_pincode" class="uni-form-input">
                                                <input type="text" id="shipping_pincode" name="shipping[pincode]"
                                                       class="form-control" autocomplete="shipping postal-code"
                                                       value="<?= isset($ac_data->ac_shipping_pincode) ? $ac_data->ac_shipping_pincode : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_shipping_pincode) && !empty($ac_data->ac_shipping_pincode)) ? 'false' : 'true'; ?>"
                                                        >
                                                <span>ZIP Code</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="uni-form-input">
                                                <input type="text" id="phone" name="phone" class="form-control"
                                                       autocomplete="shipping phone"
                                                       data-mask="(000) 000-0000"
                                                       value="<?= isset($ac_data->ac_customer_phone) ? $ac_data->ac_customer_phone : ''; ?>"
                                                       data-empty="<?= (isset($ac_data->ac_customer_phone) && !empty($ac_data->ac_customer_phone)) ? 'false' : 'true'; ?>">
                                                <span>Phone</span>
                                                <div class="field__icon">
                                                    <div class="tooltip-container">
                                                        <button type="button" class="tooltip-control" data-tooltip-control="true" aria-label="More information" aria-describedby="tooltip-for-phone" aria-controls="tooltip-for-phone" aria-pressed="false" placeholder="Phone">
                                                            <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-16 icon-svg--block icon-svg--center" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm.7 13H6.8v-2h1.9v2zm2.6-7.1c0 1.8-1.3 2.6-2.8 2.8l-.1 1.1H7.3L7 7.5l.1-.1c1.8-.1 2.6-.6 2.6-1.6 0-.8-.6-1.3-1.6-1.3-.9 0-1.6.4-2.3 1.1L4.7 4.5c.8-.9 1.9-1.6 3.4-1.6 1.9.1 3.2 1.2 3.2 3z"></path>
                                                            </svg>
                                                        </button>
                                                        <span class="tooltip">In case we need to contact you about your order</span>
                                                    </div>
                                                </div>

                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group form-footer">
                                        <a href="<?= $qa_cart_link ?>">
                                            <svg class="icon-svg--color-accent" role="img" xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 10 10">
                                                <path d="M8 1L7 0 3 4 2 5l1 1 4 4 1-1-4-4"></path>
                                            </svg>
                                            Return to cart
                                        </a>
                                        <?php if (isset($sms_multi_shipping_lines_arr) && !empty($sms_multi_shipping_lines_arr)) { ?>
                                        <button class="submit btn" id="cont_to_pay_method" type="button">Continue to payment</button>
                                        <?php } else { ?>
                                        <button class="submit btn" id="continue_to_ship_method_btn" type="button">Continue to
                                            shipping</button>
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!isset($sms_multi_shipping_lines_arr)) { ?>
                        <div class="tabcontent" id="tab-2">
                            <div class="shipping-form">
                                <div class="form-group">
                                    <div class="section section--contact-information">
                                        <div class="edit-details">
                                            <div class="review-block">
                                                <div class="review-block__inner">
                                                    <div class="review-block__label">
                                                        <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                        Your contact information
                                                        <?php }else{ ?>
                                                        Contact
                                                        <?php }?>
                                                    </div>
                                                    <div class="review-block__content shipping_email_div"></div>
                                                </div>
                                                <div class="review-block__link">
                                                    <a class="link--small" href="#defaultOpen" onclick="openCity(event, 'tab-1');
														document.getElementById('defaultOpen').click();">
                                                        <span aria-hidden="true">Change</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <hr class="divider">
                                            <div class="review-block">
                                                <div class="review-block__inner">
                                                    <div class="review-block__label">
                                                        <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                        Gift recipient delivery address
                                                        <?php }else{ ?>
                                                        Ship to
                                                        <?php }?>
                                                    </div>
                                                    <div class="review-block__content shipping_address_div"></div>
                                                </div>
                                                <div class="review-block__link">
                                                    <a class="link--small" href="#defaultOpen" onclick="openCity(event, 'tab-1');
														document.getElementById('defaultOpen').click();">
                                                        <span aria-hidden="true">Change</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section section--shipping-detail">
                                    <div class="form-group">
                                        <h2 class="sec-title"> Shipping method </h2>
                                        <div class="shpping-rates-loader" id="shpping_rates_loader">
                                            <div class="content-box blank-slate" role="region" aria-labelledby="shipping-rates__title" tabindex="-1" aria-live="polite">
                                                <svg class="icon-svg icon-svg--color-accent icon-svg--size-32 icon-svg--spinner blank-slate__icon" aria-hidden="true" focusable="false"><path d="M32 16c0 8.837-7.163 16-16 16S0 24.837 0 16 7.163 0 16 0v2C8.268 2 2 8.268 2 16s6.268 14 14 14 14-6.268 14-14h2z"></path></svg>
                                                <h3 id="shipping-rates__title">Getting available shipping rates…</h3>
                                            </div>
                                        </div>
                                        <div class="uni-form-input radio" id="shpping_rates_div" style="position: relative; display:none;">
                                            <div id="shpping_rates_list"></div>
                                        </div>
                                    </div>
                                    <div class="form-group form-footer">
                                        <a href="javascript:;" onclick="openCity(event, 'tab-1');
											document.getElementById('defaultOpen').click();"> <svg
                                                    class="icon-svg--color-accent" role="img" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 10 10">
                                                <path d="M8 1L7 0 3 4 2 5l1 1 4 4 1-1-4-4"></path>
                                            </svg> Return to information </a>
                                        <button class="submit btn" id="cont_to_pay_method" type="button">Continue to payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php }?>

                        <div class="tabcontent" id="tab-3">
                            <div class="form-group"
                                 style="<?= isset($sms_multi_shipping_lines_arr)?"display:none":"" ?>">
                                <div class="section section--edit-information">
                                    <div class="edit-details">
                                        <div class="review-block">
                                            <div class="review-block__inner">
                                                <div class="review-block__label">
                                                    <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                    Your contact information
                                                    <?php }else{ ?>
                                                    Contact
                                                    <?php }?>
                                                </div>
                                                <div class="review-block__content shipping_email_div"></div>
                                            </div>
                                            <div class="review-block__link">
                                                <a class="link--small" href="#defaultOpen" onclick="openCity(event, 'tab-1');
														document.getElementById('defaultOpen').click();">
                                                    <span aria-hidden="true">Change</span>
                                                </a>
                                            </div>
                                        </div>
                                        <hr class="divider">
                                        <div class="review-block">
                                            <div class="review-block__inner">
                                                <div class="review-block__label">
                                                    <?php if (isset($cartinfo['attributes']['gift-message']) && $cartinfo['attributes']['gift-message']=='Yes') { ?>
                                                    Gift recipient delivery address
                                                    <?php }else{ ?>
                                                    Ship to
                                                    <?php }?>
                                                </div>
                                                <div class="review-block__content shipping_address_div"></div>
                                            </div>
                                            <div class="review-block__link">
                                                <a class="link--small" href="#tab-1" onclick="openCity(event, 'tab-1');

													document.getElementById('defaultOpen').click();">
                                                    <span aria-hidden="true">Change</span>
                                                </a>
                                            </div>
                                        </div>
                                        <hr class="divider">
                                        <div class="review-block">
                                            <div class="review-block__inner">
                                                <div class="review-block__label">
                                                    Method
                                                </div>
                                                <div class="review-block__content shipping_method_div">

                                                </div>
                                            </div>
                                            <div class="review-block__link">
                                                <a class="link--small" href="#tab-2" onclick="openCity(event, 'tab-2');
                                                            document.getElementById('tab-2-open').click();">
                                                    <span aria-hidden="true">Change</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="order-summary__section--total-lines main-div-discount-code-mobile">
                                <table class="total-line-table">
                                    <tbody class="total-line-table__tbody">
                                    <tr class="total-line">
                                        <th class="total-line__name payment-due-label discount_code" scope="row">
                                            <h2 class="section__title">Discount</h2>
                                            <label for="discount_code" class="uni-form-input">
                                                <input autocomplete="inexoff" type="text" name="discount_code" id="discount_code_mobile"
                                                       class="form-control" placeholder="Discount code" />
                                            </label>
                                            <button class="submit btn btn--disabled" type="button" id="discount_code_btn_mobile"
                                                    disabled="">

												<span class="af_after_loading">
													<div class="btn__content visually-hidden-on-mobile" aria-hidden="true"> Apply  </div>
													<svg class="icon-svg icon-svg--size-16 btn__icon shown-on-mobile" viewBox="0 0 16 16">
                                                        <path d="M16 8.1l-8.1 8.1-1.1-1.1L13 8.9H0V7.3h13L6.8 1.1 7.9 0 16 8.1z"></path>
                                                    </svg>
												</span>
                                                <i class="btn__spinner icon icon--button-spinner" style="display: none;">
                                                    <svg class="icon-svg icon-svg--size-18 btn__spinner icon-svg--spinner-button">
                                                        <path d="M20 10c0 5.523-4.477 10-10 10S0 15.523 0 10 4.477 0 10 0v2c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8h2z"></path>
                                                    </svg>
                                                </i>
                                            </button>
                                            <span style="display:block;" id="discount-msg-mobile"></span>
                                            <div class="list__discount__none" id="reduction_code_div_mobile">
                                                <div class="tags-list__discount">
                                                    <div class="tag__discount">
                                                        <div class="tag__wrapper">
															<span class="tag__text">
															<span class="reduction-code">
																<svg class="icon-svg icon-svg--color-adaptive-light icon-svg--size-18 reduction-code__icon" aria-hidden="true" focusable="false">
                                                                    <path d="M17.78 3.09C17.45 2.443 16.778 2 16 2h-5.165c-.535 0-1.046.214-1.422.593l-6.82 6.89c0 .002 0 .003-.002.003-.245.253-.413.554-.5.874L.738 8.055c-.56-.953-.24-2.178.712-2.737L9.823.425C10.284.155 10.834.08 11.35.22l4.99 1.337c.755.203 1.293.814 1.44 1.533z" fill-opacity=".55"></path>
                                                                    <path d="M10.835 2H16c1.105 0 2 .895 2 2v5.172c0 .53-.21 1.04-.586 1.414l-6.818 6.818c-.777.778-2.036.782-2.82.01l-5.166-5.1c-.786-.775-.794-2.04-.02-2.828.002 0 .003 0 .003-.002l6.82-6.89C9.79 2.214 10.3 2 10.835 2zM13.5 8c.828 0 1.5-.672 1.5-1.5S14.328 5 13.5 5 12 5.672 12 6.5 12.672 8 13.5 8z"></path>
                                                                </svg>
																<span class="reduction-code__text" id="reduction_code_text_mobile"></span>
															</span>
															</span>
                                                            <button class="tag__button" type="button" id="remove-discount-tag-mobile">
                                                                <svg class="icon-svg icon-svg--color-adaptive-dark icon-svg--size-12 icon-svg--block" aria-hidden="true" focusable="false">
                                                                    <path d="M1.5 1.5l10.05 10.05M11.5 1.5L1.45 11.55" stroke-width="2" fill="none" fill-rule="evenodd" stroke-linecap="round"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="section section--payment-detail">
                                <div class="form-group">
                                    <h2 class="sec-title"> Select your order Approver </h2>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;margin-top: 10px;">
                                    <div class="uni-form-input select">
                                        <span>Approver</span>
                                        <input type="hidden" name="first_approver_1" id="first_approver_1" value="">
                                        <input type="hidden" name="second_approver_1" id="second_approver_1" value="">
                                        <select name="department_1" id="department_1" class="form-control" required="">
                                            <option data-firstapprover="" data-secondapprover="" value=""></option>
                                            <option data-firstapprover="david.barnes@subsea7.com" data-secondapprover="" value="Barnes, David">Barnes, David</option>
                                            <option data-firstapprover="craig.broussard@subsea7.com" data-secondapprover="" value="Broussard, Craig">Broussard, Craig</option>
                                            <option data-firstapprover="laura.butler@subsea7.com" data-secondapprover="" value="Butler, Laura">Butler, Laura</option>
                                            <option data-firstapprover="steven.shakespeare@subsea7.com" data-secondapprover="" value="Shakespeare, Steven">Shakespeare, Steven</option>
                                            <option data-firstapprover="thomas.tottenjr@subsea7.com" data-secondapprover="" value="Totten Jr, Thomas">Totten Jr, Thomas</option>
                                            <option data-firstapprover="matthias.vernier@subsea7.com" data-secondapprover="" value="Vernier, Matthias">Vernier, Matthias</option>
                                            <option data-firstapprover="james.ward@subsea7.com" data-secondapprover="" value="Ward, James">Ward, James</option>
                                            <option data-firstapprover="jeremy.woulds@subsea7.com" data-secondapprover="" value="Woulds, Jeremy">Woulds, Jeremy</option>
                                            <!--<option data-firstapprover="sweta@webinopoly.com" data-secondapprover="" value="Sweta">Sweta</option>
                                        <option data-firstapprover="amit.webinopoly@gmail.com" data-secondapprover="" value="Amit">Amit</option>-->
                                            <option data-firstapprover="shawna@mycorporateexpressions.com" data-secondapprover="" value="Store Admin">*Store Admin – Do Not Use</option>
                                        </select>
                                        <div class="field__caret">
                                            <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-10 field__caret-svg" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 3h10L5 8" fill-rule="nonzero"></path></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-left: 0;">
                                    <p style="float: right; color: red;" id="department_amount_msg"></p>
                                </div>

                                <div class="form-group" style="margin-left: 15px;">
                                    <label for="additional_note" class="uni-form-input">
                                        <textarea name="additional_note" id="additional_note" class="form-control" placeholder="Comment/Note"></textarea>
                                    </label>
                                </div>
                            </div>

                            <div class="section section--billing-detail">
                                <div class="form-group">
                                    <div class="billing_add_title">
                                        <h2 class="sec-title"> Billing address </h2>
                                        <span>Select the address that matches your card or payment method.</span>
                                    </div>
                                    <div class="uni-form-input radio content-box" id="billing_radio_section">
                                        <div class="radio-box content-box__row">
                                            <div class="radio__input">
                                                <input type="radio" id="billing-1" name="billingadd" value="ship"
                                                       class="billingadd" checked="">
                                            </div>
                                            <label class="radio__label" for="billing-1">
                                                <span class="radio__label__primary"> Same as shipping address </span>
                                            </label>
                                        </div>
                                        <div class="radio-box open-address content-box__row">
                                            <div class="radio__input">
                                                <input type="radio" id="billing-2" name="billingadd" value="bill"
                                                       class="billingadd">
                                            </div>
                                            <label class="radio__label" for="billing-2">
                                                <span class="radio__label__primary"> Use a different billing address </span>
                                            </label>
                                        </div>

                                        <div class="billing-address2 content-box__row content-box__row--secondary">

                                            <div class="form-group" id="billing_country_div">
                                                <div class="uni-form-input select">
                                                    <span>Country/region</span>
                                                    <select class="form-control billing_country" name="billing[country]"
                                                            id="billing_country" data-empty="true">
                                                        <option></option>
                                                        <?php if (!empty($countries) && is_array($countries)) { ?>
                                                        <?php foreach ($countries as $country) { ?>
                                                        <?php if ($country['code'] != '*') { ?>
                                                        <option <?= strtolower($country['code']) == 'us' ? 'selected' : '' ?>
                                                                data-provinces='<?= !empty($country['provinces']) ? json_encode($country['provinces']) : "" ?>'
                                                                value="<?= $country['code'] ?>"
                                                                data-name="<?= $country['name'] ?>"
                                                                data-taxname="<?= $country['tax_name'] ?>"
                                                                data-tax="<?= $country['tax'] ?>"
                                                                data-id="<?= $country['id'] ?>"><?= $country['name'] ?>
                                                        </option>
                                                        <?php } ?>
                                                        <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="field__caret">
                                                        <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-10 field__caret-svg" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 3h10L5 8" fill-rule="nonzero"></path></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-2">
                                                <label for="billing_first_name" class="uni-form-input">
                                                    <input id="billing_first_name" name="billing[first_name]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>First name</span>
                                                </label>
                                            </div>
                                            <div class="form-group col-2">
                                                <label for="billing_last_name" class="uni-form-input">
                                                    <input id="billing_last_name" name="billing[last_name]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>Last name</span>
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <label for="billing_company" class="uni-form-input">
                                                    <input id="billing_company" name="billing[company]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>Company (optional)</span>
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <label for="billing_address1" class="uni-form-input">
                                                    <input id="billing_address1" name="billing[address]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>Address</span>
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <label for="billing_address2" class="uni-form-input">
                                                    <input id="billing_address2" name="billing[address2]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>Apartment, suite, etc. (optional)</span>
                                                </label>
                                            </div>

                                            <div class="form-group col-3">
                                                <label for="billing_city" class="uni-form-input">
                                                    <input id="billing_city" name="billing[city]" class="form-control" type="text"
                                                           data-empty="true">
                                                    <span>City</span>
                                                </label>
                                            </div>
                                            <div class="form-group col-3" id="billing_state_div">
                                                <div class="uni-form-input select">
                                                    <span>State</span>
                                                    <select class="form-control billing_country" name="billing[state]"
                                                            id="billing_state" data-empty="true">
                                                    </select>
                                                    <div class="field__caret">
                                                        <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-10 field__caret-svg" role="presentation" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 3h10L5 8" fill-rule="nonzero"></path></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-1" id="billing_pincode_div">
                                                <label for="billing_pincode" class="uni-form-input">
                                                    <input id="billing_pincode" name="billing[pincode]" class="form-control"
                                                           type="text" data-empty="true">
                                                    <span>ZIP Code</span>
                                                </label>
                                            </div>
                                            <div class="form-group">
                                                <label for="billing_phone" class="uni-form-input">
                                                    <input id="billing_phone" name="billing[phone]" class="form-control"
                                                           type="text" data-empty="true" data-mask="(000) 000-0000">
                                                    <span>Phone</span>
                                                    <div class="field__icon">
                                                        <div class="tooltip-container">
                                                            <button type="button" class="tooltip-control" data-tooltip-control="true" aria-label="More information" aria-describedby="tooltip-for-phone" aria-controls="tooltip-for-phone" aria-pressed="false" placeholder="Phone">
                                                                <svg class="icon-svg icon-svg--color-adaptive-lighter icon-svg--size-16 icon-svg--block icon-svg--center" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                                                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm.7 13H6.8v-2h1.9v2zm2.6-7.1c0 1.8-1.3 2.6-2.8 2.8l-.1 1.1H7.3L7 7.5l.1-.1c1.8-.1 2.6-.6 2.6-1.6 0-.8-.6-1.3-1.6-1.3-.9 0-1.6.4-2.3 1.1L4.7 4.5c.8-.9 1.9-1.6 3.4-1.6 1.9.1 3.2 1.2 3.2 3z"></path>
                                                                </svg>
                                                            </button>
                                                            <span class="tooltip">In case we need to contact you about your order</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="shipping-form">
                                    <div class="form-group form-footer">
                                        <a href="javascript:;" id="return_to_shipping_method_btn" onclick="openCity(event, 'tab-2');

											document.getElementById('tab-2-open').click();"> <svg
                                                    class="icon-svg--color-accent" role="img" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 10 10">
                                                <path d="M8 1L7 0 3 4 2 5l1 1 4 4 1-1-4-4"></path>
                                            </svg> Return to shipping </a>


                                        <input type="hidden" name="customer_id" id="customer_id"
                                               value="<?= isset($ac_data->ac_customer_id) ? $ac_data->ac_customer_id : '' ?>" />
                                        <input type="hidden" name="note" id="note"
                                               value="<?= isset($_POST['note']) ? $_POST['note'] : '' ?>" />
                                        <input type="hidden" name="cust_ws_tag" id="cust_ws_tag"
                                               value="<?= isset($cust_ws_tag) ? $cust_ws_tag : '' ?>" />
                                        <input type="hidden" name="shop" id="shop_url"
                                               value="<?= $shop ?>" />
                                        <input type="hidden" name="cart"
                                               value='<?= base64_encode(json_encode($cartinfo)) ?>' />
                                        <input type="hidden" name="shipping_lines" id="shipping_lines" />
                                        <input type="hidden" name="sms_multi_shipping_lines" id="sms_multi_shipping_lines"
                                               value='<?=isset($_POST['sms_multi_shipping_lines'])?html_entity_decode($_POST['sms_multi_shipping_lines'], ENT_QUOTES):""?>' />
                                        <input type="hidden" name="sms_multi_tax_lines" id="sms_multi_tax_lines"
                                               value='<?=isset($multi_taxlines)?json_encode($multi_taxlines, 1):""?>' />
                                        <input type="hidden" name="tax_lines" id="tax_lines" />
                                        <input type="hidden" name="processCheckout" value="1" />
                                        <input type="hidden" name="ac_token" id="ac_token"
                                               value="<?= isset($ac_data->ac_token) ? $ac_data->ac_token : '' ?>" />
                                        <input type="hidden" name="shop_currency_name"
                                               value="<?= $shop_currency_name ?>" />
                                        <input type="hidden" name="shop_currency_symbol"
                                               value="<?= $shop_currency_symbol ?>" />
                                        <input type="hidden" name="checkout_id" id="checkout_id" value="" />

                                        <input type="hidden" id="card-nonce" name="nonce">
                                        <input type="hidden" name="allow_net30" value="<?= isset($_POST['allow_net30']) ? $_POST['allow_net30'] : '' ?>">

                                        <?php
                                        if (isset($cs_data->cs_age_verification_require) && $cs_data->cs_age_verification_require == 'required') {
                                            $cob_display = 'display:none;';
                                            $cavb_display = '';
                                        } else {
                                            $cob_display = '';
                                            $cavb_display = 'display:none;';
                                        }
                        ?>
                                        <button class="submit btn" type="button" id="complete-order-btn"
                                                style="<?= $cob_display ?>">
                                            <span>Complete Order</span>
                                            <i class="btn__spinner icon icon--button-spinner">
                                                <img
                                                        src="<?= $checkout_assets_url ?>/images/eclipse_ajax.gif" />
                                            </i>
                                        </button>
                                        <button class="submit btn" type="button" id="complete_age_verification_btn"
                                                style="<?= $cavb_display ?>">
                                            <span>Complete Age Verification</span>
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php }?>

                    </div>

                </div>
            </div>


            <div class="sidebar"
                 style="<?php if (isset($cs_data->cs_banner_image) && !empty($cs_data->cs_banner_image)) { ?> padding-top:2em; <?php } ?>">
                <div id="sidebar_section" class="sidebar-section">
                    <div class="order-summary__section__content">
                        <div class="order-summary__section__content">
                            <!-- <h3 data-ch-type="text" class="section-title">Order Summary</h3> -->
                            <?php
                            $cart_sub_total = 0;
    if (isset($cartinfo['items']) && is_array($cartinfo['items'])) { ?>
                            <table class="product-table" id="product_summery_table">
                                <tbody>
                                <?php foreach ($cartinfo['items'] as $itemkey => $item) { ?>
                                <tr class="product"
                                    id="product-<?= $itemkey ?>">
                                    <td class="product__image">
                                        <div class="product-thumbnail">
                                            <div class="product-thumbnail__wrapper">
                                                <img alt=""
                                                     src="<?= $item['image'] != '' ? $item['image'] : $checkout_assets_url . '/images/no-image.gif' ?>" />
                                            </div>
											<span
                                                    class="quantity"><?= $item['quantity'] ?></span>
                                        </div>
                                    </td>
                                    <td class="product__description">
                                        <h6>
                                            <?= $item['product_title'] ?>
                                            <small>
                                                <?php if ($item['variant_title'] != '') {
                                                    echo '<br>' . $item['variant_title'];
                                                } ?>
                                                <?php
                                                if (
                                                    isset($item['properties']['_is_subs']) && $item['properties']['_is_subs'] == 'true'
                                                    && isset($item['properties']['_interval']) && $item['properties']['_interval'] != ''
                                                ) {
                                                    echo '<br>Subscription: ' . strtolower($item['properties']['_interval'] . '(s)');
                                                }
                                    ?>
                                            </small>
                                        </h6>
                                        <?php if ($item['variant_title'] != '') { ?>
                                        <?php if (isset($itemOpts[$itemkey]) && $itemOpts[$itemkey] != '') { ?>
                                        <?php $productOptions = explode(',', $itemOpts[$itemkey]); ?>
                                        <?php foreach ($item['variant_options'] as $optkey => $optval) { ?>
                                        <span><?= $productOptions[$optkey] ?>&nbsp;:&nbsp;<?= $optval ?></span><br />
                                        <?php } ?>
                                        <?php } ?>
                                        <?php } ?>
                                        <?php
                                                $single_item_price = $item['price'] / 100;
                                    if (isset($item['square_discount']['discount']) && !empty($item['square_discount']['discount'])) {
                                        if ($item['square_discount']['cal_type'] == '%') {
                                            $single_item_price = $single_item_price - ($single_item_price * $item['square_discount']['discount'] / 100);
                                        } elseif ($item['square_discount']['cal_type'] == '-') {
                                            $single_item_price = $single_item_price - $item['square_discount']['discount'];
                                        }
                                    } elseif (isset($item['subscription_discount']['discount']) && !empty($item['subscription_discount']['discount'])) {
                                        if ($item['subscription_discount']['cal_type'] == '%') {
                                            $single_item_price = $single_item_price - ($single_item_price * $item['subscription_discount']['discount'] / 100);
                                        } elseif ($item['subscription_discount']['cal_type'] == '-') {
                                            $single_item_price = $single_item_price - $item['subscription_discount']['discount'];
                                        }
                                    }
                                    if (isset($item['properties']) && !empty($item['properties'])) {
                                        $card_msg_exist = 'No';
                                        $engrave_msg_arr = [];
                                        foreach ($item['properties'] as $propkey => $propval) {
                                            if ($item['properties'][$propkey] != '' && substr($propkey, 0, 15)=="engraving_line_") {
                                                array_push($engrave_msg_arr, $propval);
                                            } elseif ($propkey == 'card_message' && $propval=='yes') {
                                                $card_msg_exist = 'Yes';
                                            }
                                        }

                                        if (!empty($engrave_msg_arr)) {
                                            if ($card_msg_exist == 'Yes') {
                                                echo '<span>Card Message:</span><br />';
                                                if (isset($engrave_msg_arr[0])) {
                                                    echo '<span>Message: '.$engrave_msg_arr[0].'</span><br />';
                                                }
                                                if (isset($engrave_msg_arr[1])) {
                                                    echo '<span>Signature: '.$engrave_msg_arr[1].'</span><br />';
                                                }
                                            } else {
                                                echo '<span>Engraving Message:</span><br />';
                                                foreach ($engrave_msg_arr as $eng_msg) {
                                                    echo '<span>'.$eng_msg.'</span><br />';
                                                }
                                            }
                                        } else {
                                            foreach ($item['properties'] as $propkey => $propval) {
                                                if ($item['properties'][$propkey] != '' && substr($propkey, 0, 1)!="_") {
                                                    echo '<span> '.$propkey.'&nbsp;:&nbsp; '.$propval.' </span><br />';
                                                }
                                            }
                                        }
                                    } ?>
                                    </td>
                                    <td class="product__price">
                                        <?php
                                    $single_item_price = ($single_item_price * $item['quantity']);
                                    $single_item_price = floatval(number_format($single_item_price, '2', '.', ''));
                                    $cart_sub_total += $single_item_price;
                                    ?>
                                        <?php if ($single_item_price > 0) { ?>
                                        <span><?= $shop_currency_symbol ?><?= number_format($single_item_price, '2', '.', '') ?></span>
                                        <?php } else {?>
                                        <span>Free</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <?php }
    $cart_sub_total = number_format($cart_sub_total, '2', '.', '');
    ?>
                        </div>
                    </div>

                    <?php if (isset($need_to_display_promocode_section) && $need_to_display_promocode_section == 'Yes') { ?>
                    <div class="order-summary__section--total-lines" id="main_div_discount_code" style="display: none;">
                        <table class="total-line-table">
                            <tbody class="total-line-table__tbody">
                            <tr class="total-line">
                                <th class="total-line__name payment-due-label discount_code" scope="row">
                                    <label for="discount_code" class="uni-form-input">
                                        <input autocomplete="inexoff" type="text" name="discount_code" id="discount_code"
                                               class="form-control" placeholder="Discount code" />
                                    </label>
                                    <button class="submit btn btn--disabled" type="button" id="discount_code_btn"
                                            disabled="">
                                        <span class="af_after_loading">Apply</span>
                                        <i class="btn__spinner icon icon--button-spinner" style="display: none;">
                                            <svg class="icon-svg icon-svg--size-18 btn__spinner icon-svg--spinner-button">
                                                <path d="M20 10c0 5.523-4.477 10-10 10S0 15.523 0 10 4.477 0 10 0v2c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8h2z"></path>
                                            </svg>
                                        </i>
                                    </button>
                                    <span style="display:block;" id="discount-msg"></span>
                                    <div class="list__discount__none" id="reduction_code_div">
                                        <div class="tags-list__discount">
                                            <div class="tag__discount">
                                                <div class="tag__wrapper">
									<span class="tag__text">
									<span class="reduction-code">
										<svg class="icon-svg icon-svg--color-adaptive-light icon-svg--size-18 reduction-code__icon" aria-hidden="true" focusable="false">
                                            <path d="M17.78 3.09C17.45 2.443 16.778 2 16 2h-5.165c-.535 0-1.046.214-1.422.593l-6.82 6.89c0 .002 0 .003-.002.003-.245.253-.413.554-.5.874L.738 8.055c-.56-.953-.24-2.178.712-2.737L9.823.425C10.284.155 10.834.08 11.35.22l4.99 1.337c.755.203 1.293.814 1.44 1.533z" fill-opacity=".55"></path>
                                            <path d="M10.835 2H16c1.105 0 2 .895 2 2v5.172c0 .53-.21 1.04-.586 1.414l-6.818 6.818c-.777.778-2.036.782-2.82.01l-5.166-5.1c-.786-.775-.794-2.04-.02-2.828.002 0 .003 0 .003-.002l6.82-6.89C9.79 2.214 10.3 2 10.835 2zM13.5 8c.828 0 1.5-.672 1.5-1.5S14.328 5 13.5 5 12 5.672 12 6.5 12.672 8 13.5 8z"></path>
                                        </svg>
										<span class="reduction-code__text" id="reduction_code_text"></span>
									</span>
									</span>
                                                    <button type="submit" class="tag__button" type="button" id="remove-discount-tag">
                                                        <svg class="icon-svg icon-svg--color-adaptive-dark icon-svg--size-12 icon-svg--block" aria-hidden="true" focusable="false">
                                                            <path d="M1.5 1.5l10.05 10.05M11.5 1.5L1.45 11.55" stroke-width="2" fill="none" fill-rule="evenodd" stroke-linecap="round"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr class="total-line" style="display: none;">
                                <th align="center">OR</th>
                            </tr>
                            <tr class="total-line" style="display: none;">
                                <th class="total-line__name payment-due-label giftcard_code" scope="row">
                                    <label for="giftcard_code" class="uni-form-input">
                                        <input autocomplete="inexoff" type="text" name="giftcard_code" id="giftcard_code"
                                               class="form-control" placeholder="Gift Card" />
                                    </label>
                                    <button class="submit btn btn--disabled" type="button" id="giftcard_code_btn"
                                            disabled="">
                                        <span class="af_after_loading">Apply</span>
                                        <i class="btn__spinner icon icon--button-spinner" style="display: none;">
                                            <img
                                                    src="<?= $checkout_assets_url ?>/images/eclipse_ajax.gif" />
                                        </i>
                                    </button>
                                    <span style="display:block;" id="giftcard-msg"></span>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>

                    <div class="order-summary__section--total-lines">
                        <table class="total-line-table">
                            <tbody class="total-line-table__tbody">
                            <tr class="total-line total-line--subtotal">
                                <th class="total-line__name" scope="row">Subtotal</th>
                                <td class="total-line__price">
									<span class="order-summary__emphasis" id="cart_subtotal">
										<?= $shop_currency_symbol ?><?= $cart_sub_total ?>
									</span>
                                </td>
                            </tr>
                            <tr class="total-line total-line--subtotal" id="cart_discount_tr" style="display: none;">
                                <th class="total-line__name line__name__flex" scope="row">Discount
									<span class="applied-reduction-code">
										<svg width="16" height="15" xmlns="http://www.w3.org/2000/svg"
                                             class="icon-svg icon-svg--color-adaptive-light icon-svg--size-18 reduction-code__icon applied-reduction-code__icon" fill="#1990c6">
                                            <path d="M17.78 3.09C17.45 2.443 16.778 2 16 2h-5.165c-.535 0-1.046.214-1.422.593l-6.82 6.89c0 .002 0 .003-.002.003-.245.253-.413.554-.5.874L.738 8.055c-.56-.953-.24-2.178.712-2.737L9.823.425C10.284.155 10.834.08 11.35.22l4.99 1.337c.755.203 1.293.814 1.44 1.533z" fill-opacity=".55"></path><path d="M10.835 2H16c1.105 0 2 .895 2 2v5.172c0 .53-.21 1.04-.586 1.414l-6.818 6.818c-.777.778-2.036.782-2.82.01l-5.166-5.1c-.786-.775-.794-2.04-.02-2.828.002 0 .003 0 .003-.002l6.82-6.89C9.79 2.214 10.3 2 10.835 2zM13.5 8c.828 0 1.5-.672 1.5-1.5S14.328 5 13.5 5 12 5.672 12 6.5 12.672 8 13.5 8z"></path>
                                        </svg>
										<span class="applied-reduction-code__information" id="applied-reduction-code"></span>
										<a href="javascript:;" id="remove-discount">
                                            <svg class="icon-svg icon-svg--color-adaptive-dark icon-svg--size-12 icon-svg--block" aria-hidden="true" focusable="false">
                                                <path d="M1.5 1.5l10.05 10.05M11.5 1.5L1.45 11.55" stroke-width="2" fill="none" fill-rule="evenodd" stroke-linecap="round"></path>
                                            </svg>
                                        </a>
									</span>
                                </th>
                                <td class="total-line__price discount__flex__padding">
									<span class="order-summary__emphasis" id="cart_discount_div">
										-
									</span>
                                </td>
                            </tr>
                            <tr class="total-line total-line--shipping">
                                <th class="total-line__name" scope="row">Shipping</th>
                                <td class="total-line__price">
									<span class="order-summary__emphasis" id="cart_shipping">
										Calculated at next step
									</span>
                                </td>
                            </tr>

                            <tr class="total-line total-line--taxes " id="cart_taxes_tr" style="display: none;">
                                <th class="total-line__name" scope="row">Estimated taxes</th>
                                <td class="total-line__price">
									<span class="order-summary__emphasis" id="cart_taxes">
										-
									</span>
                                </td>
                            </tr>
                            <tr class="total-line total-line--taxes" id="cart_giftcard_tr" style="display: none;">
                                <th class="total-line__name" scope="row">Gift Card&nbsp;&nbsp;
									<span class="applied-giftcard-code-div">
										<img width="20" height="20"
                                             src="data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20fill%3D%22%23637381%22%20d%3D%22M11%209h7V7h-7v2zm0%209h5v-7h-5v7zm-7%200h5v-7H4v7zM2%209h7V7H2v2zm2.75-5.5c0-.827.673-1.5%201.5-1.5%201.562%200%202.411%201.42%202.662%203H6.25c-.827%200-1.5-.673-1.5-1.5zM13%203a1.001%201.001%200%200%201%200%202h-1.887c.207-.964.738-2%201.887-2zm6%202h-3.185c.113-.314.185-.647.185-1%200-1.654-1.346-3-3-3-1.243%200-2.202.567-2.871%201.425C9.347%201.005%208.047%200%206.25%200c-1.93%200-3.5%201.57-3.5%203.5%200%20.539.133%201.043.352%201.5H1a1%201%200%200%200-1%201v4a1%201%200%200%200%201%201h1v8a1%201%200%200%200%201%201h14a1%201%200%200%200%201-1v-8h1a1%201%200%200%200%201-1V6a1%201%200%200%200-1-1z%22%2F%3E%3C%2Fsvg%3E%0A"
                                             alt="">
										<span class="applied-reduction-code__information" id="applied-giftcard-code"></span>
										<a href="javascript:;" id="remove-giftcard" data-giftcard_id=""></a>
									</span>
                                </th>
                                <td class="total-line__price">
									<span class="order-summary__emphasis" id="cart_giftcard_div">
										-
									</span>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot class="total-line-table__footer">
                            <tr class="total-line">
                                <th class="total-line__name payment-due-label" scope="row">
                                    <span class="payment-due-label__total">Total</span>
                                </th>
                                <td class="total-line__price payment-due">
									<span class="payment-due__currency"
                                          id="shop_currency"><?= $shop_currency_name ?></span>
									<span class="payment-due__price"
                                          data-checkout-payment-due-target="<?= $cart_sub_total ?>"
                                          id="cart_total_price">
										<?= $shop_currency_symbol ?><?= $cart_sub_total ?>
									</span>
                                </td>
                            </tr>
                            <script>
                                $("#mobile_total_final_price").html('<?= $shop_currency_symbol ?><?= $cart_sub_total ?>');
                            </script>

                            <?php if (isset($sv_data[0]['sv_additional_content_section']) && !empty($sv_data[0]['sv_additional_content_section'])) {  ?>
                            <tr class="total-line full-border-line" id="main_section_additional_content_section"
                                style="display: none;">
                                <th colspan="2" class="total-line__name additional_content">
                                    <?= html_entity_decode($sv_data[0]['sv_additional_content_section']) ?>
                                </th>
                            </tr>
                            <?php } ?>

                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

        </div>


        <!-- INEX MODAL -->
        <div id="inexModal" class="inex_modal">
            <!-- Modal content -->
            <div class="inex_modal_content">
                <div class="inex_modal_head">
					<span class="inex_modal_title"><?php if (isset($cs_data->cs_age_popup_title) && !empty($cs_data->cs_age_popup_title)) {
                        echo $cs_data->cs_age_popup_title;
                        } else {
                        echo 'Age Verification';
                        } ?></span>
                </div>
                <div class="inex_modal_body">
                    <p><?php if (isset($cs_data->cs_age_popup_title) && !empty($cs_data->cs_age_popup_content)) {
                        echo nl2br($cs_data->cs_age_popup_content);
                        } ?></p>
                    <p>
                    <table class="inex_modal_table">
                        <tr id="av_bday_div1">
                            <td>Enter birthdate for <abbr id="av_bday_name1"></abbr></td>
                            <td>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Month</span>
                                        <select class="form-control" name="birthmonth_1" id="birthmonth_1">
                                            <option value="">-</option>
                                            <?php for ($dt = 1; $dt <= 12; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Date</span>
                                        <select class="form-control" name="birthdate_1" id="birthdate_1">
                                            <option value="">-</option>
                                            <?php for ($dt = 1; $dt <= 31; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Year</span>
                                        <select class="form-control" name="birthyear_1" id="birthyear_1">
                                            <option value="">-</option>
                                            <?php for ($dt = 1930; $dt <= 2004; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group field--error">
                                    <label class="uni-form-input" id="error_msg_birthday_1">
                                        <!--<input type="text" class="form-control" name="birthday_1" id="birthday_1" placeholder="MM/DD/YYYY" data-mask="00/00/0000" data-mask-clearifnotmatch="true">-->
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr id="av_bday_div2">
                            <td>Enter birthdate for <abbr id="av_bday_name2"></abbr></td>
                            <td>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Month</span>
                                        <select class="form-control" name="birthmonth_2" id="birthmonth_2">
                                            <option value="">-</option>
                                            <?php for ($dt = 1; $dt <= 12; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Date</span>
                                        <select class="form-control" name="birthdate_2" id="birthdate_2">
                                            <option value="">-</option>
                                            <?php for ($dt = 1; $dt <= 31; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group span-3">
                                    <div class="uni-form-input select">
                                        <span>Year</span>
                                        <select class="form-control" name="birthyear_2" id="birthyear_2">
                                            <option value="">-</option>
                                            <?php for ($dt = 1930; $dt <= 2004; $dt++) { ?>
                                            <option
                                                    value="<?= $dt ?>">
                                                <?= $dt ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group field--error">
                                    <label class="uni-form-input" id="error_msg_birthday_2">
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php if (isset($cs_data->cs_ssn_verification_require) && $cs_data->cs_ssn_verification_require == 'required') { ?>
                        <tr id="av_ssn_div1">
                            <td>Enter SSN Number for <abbr id="av_ssn_name1"></abbr></td>
                            <td>
                                <div class="form-group">
                                    <label class="uni-form-input">
                                        <input type="text" class="form-control" name="ssn_number1" id="ssn_number1">
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr id="av_ssn_div2">
                            <td>Enter SSN Number for <abbr id="av_ssn_name2"></abbr></td>
                            <td>
                                <div class="form-group">
                                    <label class="uni-form-input">
                                        <input type="text" class="form-control" name="ssn_number2" id="ssn_number2">
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>

                    </table>
                    </p>
                    <p class="form-footer">
                        <a href="javascript:;" class="inex_modal_close">
                            <svg class="icon-svg--color-accent" role="img" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 10 10">
                                <path d="M8 1L7 0 3 4 2 5l1 1 4 4 1-1-4-4"></path>
                            </svg>
                            <?php if (isset($cs_data->cs_age_popup_cancel_btn_label) && !empty($cs_data->cs_age_popup_cancel_btn_label)) {
                            echo $cs_data->cs_age_popup_cancel_btn_label;
                            } else {
                            echo 'Cancel';
                            } ?>
                        </a>
                        <button type="button" class="submit btn" id="verify_age_complete_order">
							<span><?php if (isset($cs_data->cs_age_popup_submit_btn_label) && !empty($cs_data->cs_age_popup_submit_btn_label)) {
                                echo $cs_data->cs_age_popup_submit_btn_label;
                                } else {
                                echo 'Verify Age and Complete Purchase';
                                } ?></span>
                            <i class="btn__spinner icon icon--button-spinner" style="display: none;">
                                <img
                                        src="<?= $checkout_assets_url ?>/images/eclipse_ajax.gif" />
                            </i>
                        </button>
                    </p>
                    <div style="clear: both"></div>
                </div>
            </div>
        </div>
        <!-- INEX MODAL -->

    </form>
</main>

@include('checkout_default_footer')

</body>

</html>