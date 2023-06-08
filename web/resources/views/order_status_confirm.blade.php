<?php
$order_id = $order['id'];

$not_approve_link = route('approve_status_link',[$shop,'notapproved',$order_id]).'?v=1';
if(isset($_GET['fa1']) && !empty($_GET['fa1'])){
    $not_approve_link .= '&fa1='.$_GET['fa1'];
}else if(isset($_GET['fa2']) && !empty($_GET['fa2'])){
    $not_approve_link .= '&fa2='.$_GET['fa2'];
}else if(isset($_GET['fa3']) && !empty($_GET['fa3'])){
    $not_approve_link .= '&fa3='.$_GET['fa3'];
}else if(isset($_GET['sa1']) && !empty($_GET['sa1'])){
    $not_approve_link .= '&sa1='.$_GET['sa1'];
}else if(isset($_GET['sa2']) && !empty($_GET['sa2'])){
    $not_approve_link .= '&sa2='.$_GET['sa2'];
}else if(isset($_GET['sa3']) && !empty($_GET['sa3'])){
    $not_approve_link .= '&sa3='.$_GET['sa3'];
}
?>

<html>
<head>
    <style>
        .submit_btn{
            background-color: #d42e12;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            padding: 1.4em 1.7em;
        }
        .submit_btn:hover{
            background-color: #a5240e;
        }
    </style>
</head>
<body>
<table style="width:100%; text-align: left;" cellpadding="20">
    <tr>
        <th>Order Number : </th>
        <td>#<?=$order['order_number']?></td>
    </tr>
    <tr>
        <th>Customer Email : </th>
        <td><?=$order['email']?></td>
    </tr>
    <tr>
        <th>Order Status: </th>
        <td>Not Approved</td>
    </tr>
    <tr>
        <th>Reason for declining this order : </th>
        <td><textarea id="reason_not_approve" rows="5" style="width: 100%"></textarea></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;"><button type="button" class="submit_btn" id="not_approve_submit_btn">Submit</button></td>
    </tr>
</table>
<script>
    document.getElementById('not_approve_submit_btn').onclick = function () {
        var reason_not_approve = document.getElementById('reason_not_approve').value;
        if(reason_not_approve!=''){
            reason_not_approve = btoa(reason_not_approve);
        }
        var not_approve_link = '<?=$not_approve_link?>&reason='+reason_not_approve;
        location.href = not_approve_link;
    };
</script>
</body>
</html>