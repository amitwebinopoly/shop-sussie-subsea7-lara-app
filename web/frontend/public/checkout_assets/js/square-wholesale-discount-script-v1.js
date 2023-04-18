//fetch current variant
function fetch_current_variant(){
    if(document.getElementsByName("id")){
        let nameIdElem = document.getElementsByName("id")[0];
        if(nameIdElem.tagName=='SELECT'){
            return nameIdElem.options[nameIdElem.selectedIndex].value;
        }else{
            return nameIdElem.value;
        }
    }
    return "";
}

// for product-details page, where it has only one product, then call this function
function fetch_product_sqws_discount(shopify_shop, customer_id, sqws_tag, price_class_arr, product_id, variant_id){
    var product_ids_arr = [];
    product_ids_arr.push(product_id+"|"+variant_id);
    var product_ids = product_ids_arr.join(',');

    if(price_class_arr.length>0){
        for(var pca_i=0; pca_i<price_class_arr.length; pca_i++){
            var classElement = document.getElementsByClassName(price_class_arr[pca_i]);
            if (classElement.length > 0) {
                for(var ce_i=0; ce_i<classElement.length; ce_i++){
                    classElement[ce_i].style.display = 'none';
                }
            }
        }
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                if(price_class_arr.length>0){
                    for(var pca_i=0; pca_i<price_class_arr.length; pca_i++){
                        var classElement = document.getElementsByClassName(price_class_arr[pca_i]);
                        if (classElement.length > 0) {
                            for(var ce_i=0; ce_i<classElement.length; ce_i++){
                                classElement[ce_i].style.display = 'inline-block';
                                var actual_price = classElement[ce_i].innerHTML.replace(/[^0-9.]/g,'');	//remove all except numbers and decimal
                                actual_price = parseFloat(actual_price);
                                var new_price = actual_price;
                                if(actual_price!="" && actual_price>0
                                    && obj.square_discount!=undefined
                                    && obj.square_discount[product_id]!=undefined
                                    && obj.square_discount[product_id].cal_type!=undefined
                                    && obj.square_discount[product_id].discount!=undefined
                                ){
                                    if(obj.square_discount[product_id].cal_type=='%'){
                                        new_price = actual_price - (actual_price*obj.square_discount[product_id].discount/100);
                                    }else if(obj.square_discount[product_id].cal_type=='-'){
                                        new_price = actual_price - obj.square_discount[product_id].discount;
                                    }
                                    new_price = new_price.toFixed('2');
                                    classElement[ce_i].innerHTML = obj.shop_currency_symbol+new_price;
                                }
                            }
                        }
                    }
                }
            }
        }
    };
    xhttp.open("POST", "/apps/square-payment-lostrange/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_fetch_product_sqws_discount&shop="+shopify_shop+"&customer_id="+customer_id+"&cust_ws_tag="+sqws_tag+"&product_ids="+product_ids);
}

// for collection,cart,etc page, where it has multiple products, then call this function
function fetch_multi_sqws_discount(shopify_shop, customer_id, sqws_tag, price_class_arr, subtotal_class_arr, total_amount_class, page_reload_events){
    price_class_arr = price_class_arr.concat(subtotal_class_arr);//here we need to find discount for both, so concat them and fetch discount in one shot
    var product_ids_arr = [];
    if(price_class_arr.length>0){
        for(var pca_i=0; pca_i<price_class_arr.length; pca_i++){
            var classElement = document.getElementsByClassName(price_class_arr[pca_i]);
            if (classElement.length > 0) {
                for(var ce_i=0; ce_i<classElement.length; ce_i++){
                    classElement[ce_i].style.display = 'none';
                    if (classElement[ce_i].hasAttribute("data-product_id")) {
                        product_ids_arr.push(classElement[ce_i].getAttribute("data-product_id")+"|"+classElement[ce_i].getAttribute("data-variant_id"));
                    }

                }
            }
        }
    }
    var product_ids = product_ids_arr.join(',');

    //hide total price while processing
    if(total_amount_class!=''){
        var classElement = document.getElementsByClassName(total_amount_class);
        if (classElement.length > 0) {
            for(var ce_i=0; ce_i<classElement.length; ce_i++){
                classElement[ce_i].style.display = 'none';
            }
        }
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                if(price_class_arr.length>0){
                    for(var pca_i=0; pca_i<price_class_arr.length; pca_i++){
                        var classElement = document.getElementsByClassName(price_class_arr[pca_i]);
                        if (classElement.length > 0) {
                            for(var ce_i=0; ce_i<classElement.length; ce_i++){
                                classElement[ce_i].style.display = 'inline-block';
                                var actual_price = classElement[ce_i].innerHTML.replace(/[^0-9.]/g,'');	//remove all except numbers and decimal
                                var product_id = '';
                                if (classElement[ce_i].hasAttribute("data-product_id")) {
                                    product_id = classElement[ce_i].getAttribute("data-product_id");
                                }
                                var quantity = 1;
                                if (classElement[ce_i].hasAttribute("data-quantity")) {
                                    quantity = classElement[ce_i].getAttribute("data-quantity");
                                }
                                actual_price = parseFloat(actual_price);
                                var new_price = actual_price;
                                if(actual_price!="" && actual_price>0
                                    && obj.square_discount!=undefined
                                    && obj.square_discount[product_id]!=undefined
                                    && obj.square_discount[product_id].cal_type!=undefined
                                    && obj.square_discount[product_id].discount!=undefined
                                ){
                                    if(obj.square_discount[product_id].cal_type=='%'){
                                        new_price = actual_price - (actual_price*obj.square_discount[product_id].discount/100);
                                    }else if(obj.square_discount[product_id].cal_type=='-'){
                                        new_price = actual_price - (obj.square_discount[product_id].discount * parseInt(quantity));
                                    }
                                    new_price = new_price.toFixed('2');
                                    classElement[ce_i].innerHTML = obj.shop_currency_symbol+new_price;
                                }
                            }
                        }
                    }
                }

                // after replace price in all items, we need to update total based on new-subtotal
                if(subtotal_class_arr.length>0){
                    var new_total_amount = 0;
                    for(var pca_i=0; pca_i<subtotal_class_arr.length; pca_i++){
                        var classElement = document.getElementsByClassName(subtotal_class_arr[pca_i]);
                        if (classElement.length > 0) {
                            for(var ce_i=0; ce_i<classElement.length; ce_i++){
                                var t_price = classElement[ce_i].innerHTML.replace(/[^0-9.]/g,'');	//remove all except numbers and decimal
                                t_price = parseFloat(t_price);
                                if(t_price!="" && t_price>0){
                                    new_total_amount += t_price;
                                }
                            }
                        }
                    }
                    if(total_amount_class!=''){
                        var classElement = document.getElementsByClassName(total_amount_class);
                        if (classElement.length > 0) {
                            for(var ce_i=0; ce_i<classElement.length; ce_i++){
                                classElement[ce_i].style.display = 'inline-block';
                                classElement[ce_i].innerHTML = obj.shop_currency_symbol+new_total_amount.toFixed('2');
                            }
                        }
                    }
                }

                //if page-reloads events found then reload page
                if(Object.keys(page_reload_events).length>0){
                    for (const [ key_event, value_classarr ] of Object.entries(page_reload_events)) {
                        document.addEventListener(key_event,function(e){
                            if(value_classarr.length>0){
                                for(var vca_i=0; vca_i<value_classarr.length; vca_i++){
                                    var elem_class_names = e.target.className.trim().split(" ");    //when click/change on any element, then fetch that elemet's all class and compare with condition-arr-class
                                    if(e.target && elem_class_names.indexOf(value_classarr[vca_i])!=-1){
                                        setTimeout(function () {
                                            top.location.reload();
                                        },2000);
                                    }
                                }
                            }
                        });
                    }
                }
            }
        }
    };
    xhttp.open("POST", "/apps/square-payment-lostrange/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_fetch_product_sqws_discount&shop="+shopify_shop+"&customer_id="+customer_id+"&cust_ws_tag="+sqws_tag+"&product_ids="+product_ids);
}