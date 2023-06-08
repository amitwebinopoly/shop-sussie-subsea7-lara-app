<html><body>
<img src="https://cdn.shopify.com/s/files/1/0575/0521/8726/files/Subsea_Logo.png" style="width: 30%;"/>
<p style="color:#333;font-size:12px;">Order is Approved</p>
<p style="color:#333;font-size:12px;">Order Id {{$order['name']}}</p>

<table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
    <tr>
        <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Product Title</th>
        <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Quantity</th>
        <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Price</th>
        <th style="border: 1px solid #dddddd;text-align: left;padding: 8px;">Subtotal</th>
    </tr>
    @foreach ($order['line_items'] as $key => $value)
    <tr  style="background-color: #fff;">
        <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$value['name']}}</td>
        <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">{{$value['quantity']}}</td>
        <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$value['price']}}</td>
        <td  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$value['quantity'] * $value['price']}}</td>
    </tr>
    @endforeach
    <tr  style="background-color: #fff;">
        <th colspan="3" style="border: 1px solid #dddddd;padding: 8px; text-align:left;">Total</th>
        <th  style="border: 1px solid #dddddd;text-align: left;padding: 8px;">${{$order['total_price']}}</th>
    </tr>

</table>
</body></html>