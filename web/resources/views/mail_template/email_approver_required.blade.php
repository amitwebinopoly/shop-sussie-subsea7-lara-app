<html>
<body>
<div>
    <img src="https://cdn.shopify.com/s/files/1/0575/0521/8726/files/Subsea_Logo.png" style="width: 30%;"/>
    <p style="color:#333;font-size:12px;">Dear Approver,</p>
    <p style="color:#333;font-size:12px;">Please note an order has been placed via the Shop Subsea 7 Company Store and is pending your review & approval. This order is on HOLD until notice of approval is received.</p>
    <p style="color:#333;font-size:12px;">Thank you for your prompt response, we appreciate your business & look forward to providing you with excellent customer service.</p>

    <p>
    <a style="text-decoration:none;" href="[APPROVE_LINK]" > Approved  </a>
    or <a style="text-decoration:none;"  href="[NOT_APPROVE_LINK]"> Not Approved </a>
    </p>

    <p style="color:#333;font-size:12px;">Order Id {{$order['name']}}</p>

    <table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
        <tr>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Product Title</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Quantity</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Price</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Subtotal</th>
        </tr>
        <?php
        $order_sub_total = 0;
        foreach ($order['line_items'] as $key => $value) {
        $st = floatval($value['quantity'] * $value['price']);
        $order_sub_total += $st;
        ?>
        <tr  style="background-color: #fff;">
            <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$value['name']}}</td>
            <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$value['quantity']}}</td>
            <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$value['price']}}</td>
            <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$st}}</td>
        </tr>
        <?php } ?>
        <tr style="background-color: #fff;">
            <th colspan="3" style="border: 1px solid #dddddd;padding: 8px; text-align:left;">Sub Total</th>
            <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$order_sub_total}}</th>
        </tr>

        <?php
        $order_shipping = isset($order['total_shipping_price_set']['shop_money']['amount'])?$order['total_shipping_price_set']['shop_money']['amount']:0;
        $order_tax = $order['total_tax'];
        if(isset($order['taxes_included']) && $order['taxes_included']==true){
            $tax_label = 'Tax (included)';
            $grand_total = $order_sub_total + $order_shipping;
        }else{
            $tax_label = 'Tax';
            $grand_total = $order_sub_total + $order_shipping + $order_tax;
        }
        ?>
        
        <tr  style="background-color: #fff;">
            <th colspan="3" style="border: 1px solid #dddddd;padding: 8px; text-align:left;">Shipping</th>
            <th  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$order_shipping}}</th>
        </tr>

        <tr  style="background-color: #fff;">
            <th colspan="3" style="border: 1px solid #dddddd;padding: 8px; text-align:left;">{{$tax_label}}</th>
            <th  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$order_tax}}</th>
        </tr>

        <tr  style="background-color: #fff;">
            <th colspan="3" style="border: 1px solid #dddddd;padding: 8px; text-align:left;">Total</th>
            <th  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$grand_total}}</th>
        </tr>
    </table>

    <p>[DEPARTMENT_DATA]</p>

    <?php if(isset($order['customer']) && !empty($order['customer'])){ ?>
    <p>Customer<br>
        {{$order['customer']['first_name']}} {{$order['customer']['last_name']}}<br>
        {{$order['customer']['email']}}<br>
    </p>
    <?php }?>

    <?php if(isset($order['shipping_address']) && !empty($order['shipping_address'])){ ?>
    <p>Shipping Address<br>
        {{$order['shipping_address']['address1']}} {{$order['shipping_address']['address2']}}<br>
        {{$order['shipping_address']['city']}}<br>
        {{$order['shipping_address']['province_code']}} {{$order['shipping_address']['zip']}}<br>
        {{$order['shipping_address']['country']}}<br>
    </p>
    <?php }?>
</div>
</body>
</html>