// for product-details page, where it has only one product, then call this function
function fetch_product_sqsbs_subscription(shopify_shop, main_div_sqsbs_id, product_id){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                var html = '';
                var is_subs = 'false';
                var r = obj['row'];

                // hidden fields
                html += '<input type="hidden" name="properties[_sg_id]" value="'+r['sg_id']+'">';
                html += '<input type="hidden" name="properties[_is_subs]" id="hidden_is_subs">';
                html += '<input type="hidden" name="properties[_interval]" id="hidden_interval">';
                if( (r['sg_discount_type']=='SAME' || r['sg_discount_type']=='DIFFERENT') && r['sg_main_discount']>0 ){
                    html += '<input type="hidden" name="properties[_subscription_discount]" value="'+r['sg_main_discount']+'">';
                }
                // hidden fields

                //html += '<h2>'+r['sg_name']+'</h2>';

                // recurring_option
                html += '<div>';
                if(r['sg_recurring_option']=='SUBS_ONLY'){
                    html += 'Subscribe';
                    if( (r['sg_discount_type']=='SAME' || r['sg_discount_type']=='DIFFERENT') && r['sg_main_discount']>0 ){
                        html += ' ('+r['sg_main_discount']+'% Off)';
                    }
                    is_subs = 'true';
                }else if(r['sg_recurring_option']=='SUBS_AND_PRCHS'){
                    var do_p_check = 'checked';
                    var do_s_check = '';
                    if(r['sg_default_option']=='SUBS'){
                        do_p_check = '';
                        do_s_check = 'checked';
                        is_subs = 'true';
                    }
                    html += '<div class="proview_otp_class"><input type="radio" name="properties[_subs_type]" value="ONE_TIME_PRCHS" id="proview_otp" style="min-height: 0;" '+do_p_check+'> <label for="proview_otp"> '+(r['sg_prchs_text']!=''?r['sg_prchs_text']:"One-time purchase")+'</label></div><br>';

                    html += '<div class="proview_sas" ><input type="radio" name="properties[_subs_type]" value="SUBS_AND_SAVE" id="proview_sas" style="min-height: 0;" '+do_s_check+'> <label class="proview_sas_class" id="proview_sas_lbl" data-sg_main_discount="'+r['sg_main_discount']+'" data-sg_recurr_discount="'+r['sg_recurr_discount']+'" data-sg_subs_text="'+(r['sg_subs_text']!=''?r['sg_subs_text']:"Subscribe")+'" for="proview_sas">'+(r['sg_subs_text']!=''?r['sg_subs_text']:"Subscribe");
                    /*if( (r['sg_discount_type']=='SAME' || r['sg_discount_type']=='DIFFERENT') && r['sg_main_discount']>0 ){
                        html += ' ('+r['sg_main_discount']+'% Off)';
                    }*/
                    html += '</label></div> <br>';
                }
                html += '</div>';

                if(r['sg_interval_type']=='CUSTOMER_SELECT' && r['sg_interval_range']!='' && r['sg_interval_count']>0){
                    var rng = r['sg_interval_range'].split(',');
                    var mdsi_style = '';
                    if(r['sg_recurring_option']=='SUBS_AND_PRCHS'){
                        if(r['sg_default_option']=='PRCHS'){
                            mdsi_style = 'display:none;';
                        }
                    }

                    // select interval
                    html += '<div id="proview_main_div_select_interval" style="'+mdsi_style+'">';

                    if(r['sg_interval_display_type']=='SINGLE_DROP'){
                        html += '<select id="proview_interval_count_and_range">';
                        html += '<option value="" style="display: none;">- Select Interval -</option>';
                        for(var rng_i=0; rng_i<rng.length; rng_i++){
                            for(var i=1;i<=r['sg_interval_count'];i++){
                                html += '<option value="'+i+' '+rng[rng_i]+'">'+i+' '+(rng[rng_i]).ucwords()+'(s)</option>';
                            }
                        }
                        html += '</select>';
                    }else{
                        html += '<select id="proview_interval_count">';
                        html += '<option value="" style="display: none;">- Select Interval -</option>';
                        for(var i=1;i<=r['sg_interval_count'];i++){
                            html += '<option value="'+i+'">'+i+'</option>';
                        }
                        html += '</select>';
                        html += '<select id="proview_interval_range">';
                        html += '<option value="" style="display: none;">- Select Interval -</option>';
                        for(var rng_i=0; rng_i<rng.length; rng_i++){
                            html += '<option value="'+rng[rng_i]+'">'+(rng[rng_i]).ucwords()+'(s)</option>';
                        }
                        html += '</select>';
                    }

                    html += '</div>';

                }
                document.getElementById(main_div_sqsbs_id).innerHTML = html;
                document.getElementById('hidden_is_subs').value = is_subs;

                if(r['sg_interval_type']=='FIXED' && r['sg_interval_range']!='' && r['sg_interval_count']>0){
                    document.getElementById('hidden_interval').value = r['sg_interval_count']+' '+r['sg_interval_range'];
                }

                if(r['sg_interval_type']=='CUSTOMER_SELECT' && r['sg_interval_range']!='' && r['sg_interval_count']>0 && r['sg_default_option']=='SUBS'){
                    //if default selected is subscription, then auto selected both dropdown
                    if(document.getElementById('proview_interval_count_and_range')){
                        document.getElementById('proview_interval_count_and_range').selectedIndex = 1;//select first option
                    }else{
                        document.getElementById('proview_interval_count').selectedIndex = 1;//select first option
                        document.getElementById('proview_interval_range').selectedIndex = 1;//select first option
                    }
                    proview_set_interval();
                }

                //when html set in dom, then we can write click/change event in js
                set_subs_events();
                set_discount_price_in_label();

            }
        }
    };
    xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_fetch_product_sqsbs_subscription&shop="+shopify_shop+"&product_id="+product_id);
}
String.prototype.ucwords = function() {
    return this.toLowerCase().replace(/\b[a-z]/g, function(letter) {
        return letter.toUpperCase();
    });
};

function set_subs_events(){
    //always put this function first, because this cover all select onchange event, then you can put id wise events
    if(document.getElementsByClassName('swatch_options') && document.getElementsByClassName('swatch_options').length>0){
        //in case of swatches, if we set <select> change event than default swatches are now working.
        setInterval(function(){
            set_discount_price_in_label();
        }, 5000);
    }else if(document.getElementsByTagName('select') && document.getElementsByTagName('select').length>0){
        for(var i=0; i<document.getElementsByTagName('select').length; i++){
            document.getElementsByTagName('select')[i].onchange = function () {
                set_discount_price_in_label();
            }
        }
    }
    if(document.getElementById('proview_otp')){
        document.getElementById('proview_otp').onclick = function () {
            document.getElementById('hidden_is_subs').value = 'false';
            if(document.getElementById('proview_main_div_select_interval')){
                document.getElementById('proview_main_div_select_interval').style.display = 'none';
                document.getElementById('hidden_interval').value = '';
                if(document.getElementById('proview_interval_count_and_range')){
                    document.getElementById('proview_interval_count_and_range').value = '';
                }else{
                    document.getElementById('proview_interval_count').value = '';
                    document.getElementById('proview_interval_range').value = '';
                }
            }
        }
    }
    if(document.getElementById('proview_sas')){
        document.getElementById('proview_sas').onclick = function () {
            document.getElementById('hidden_is_subs').value = 'true';
            if(document.getElementById('proview_main_div_select_interval')){
                document.getElementById('proview_main_div_select_interval').style.display = 'block';
                if(document.getElementById('proview_interval_count_and_range')){
                    document.getElementById('proview_interval_count_and_range').selectedIndex = 1;//select first option
                }else{
                    document.getElementById('proview_interval_count').selectedIndex = 1;//select first option
                    document.getElementById('proview_interval_range').selectedIndex = 1;//select first option
                }
                proview_set_interval();
            }
        }
    }
    if(document.getElementById('proview_interval_count_and_range')){
        document.getElementById('proview_interval_count_and_range').onchange = function () {
            proview_set_interval();
        }
    }
    if(document.getElementById('proview_interval_count')){
        document.getElementById('proview_interval_count').onchange = function () {
            proview_set_interval();
        }
    }
    if(document.getElementById('proview_interval_range')){
        document.getElementById('proview_interval_range').onchange = function () {
            proview_set_interval();
        }
    }
}
function proview_set_interval(){
    if(document.getElementById('proview_interval_count_and_range')){
        var picar_elem = document.getElementById("proview_interval_count_and_range");
        var picar = picar_elem.options[picar_elem.selectedIndex].value;
        document.getElementById('hidden_interval').value = picar;
    }else{
        var pic_elem = document.getElementById("proview_interval_count");
        var ic = pic_elem.options[pic_elem.selectedIndex].value;

        var pir_elem = document.getElementById("proview_interval_range");
        var ir = pir_elem.options[pir_elem.selectedIndex].value;

        if(ic==''){ ic='-'; }
        if(ir==''){ ir='-'; }

        document.getElementById('hidden_interval').value = ic+' '+ir;
    }
}

function fetch_customer_sqsbs_subscription_list(shopify_shop, sqsbs_list_div_id, customer_id){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                document.getElementById(sqsbs_list_div_id).innerHTML = obj.tblHtml;
            }
        }
    };
    xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_fetch_customer_sqsbs_subscription_list&shop="+shopify_shop+"&customer_id="+customer_id);
}
function change_subs_status(shopify_shop,customer_id,status,sc_id){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                location.reload();
            }
        }
    };
    xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_sqsbs_subscription_status_change&shop="+shopify_shop+"&customer_id="+customer_id+"&status="+status+"&sc_id="+sc_id);
}
function get_subs_orders(shopify_shop,customer_id,sc_id){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                document.getElementById("main_div_sqsbs_order_list").innerHTML = obj.DATA;
            }else{
                document.getElementById("main_div_sqsbs_order_list").innerHTML = obj.MESSAGE;
            }
        }
    };
    xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_fetch_customer_sqsbs_subscription_order_list&shop="+shopify_shop+"&customer_id="+customer_id+"&sc_id="+sc_id);
}
function set_discount_price_in_label(){
    if(document.getElementById('square_product_json') && document.getElementById('proview_sas_lbl')){
        var square_product_json = document.getElementById('square_product_json').innerHTML;
        var square_product_arr = JSON.parse(square_product_json);
        var sg_main_discount = document.getElementById('proview_sas_lbl').getAttribute('data-sg_main_discount');
        var sg_recurr_discount = document.getElementById('proview_sas_lbl').getAttribute('data-sg_recurr_discount');
        var sg_subs_text = document.getElementById('proview_sas_lbl').getAttribute('data-sg_subs_text');
        var current_variant_id = document.getElementsByName('id')[0].value;

        if(square_product_arr.variants!=undefined && square_product_arr.variants.length>0){
            var current_variant_price = 0;
            for(var i=0; i<square_product_arr.variants.length; i++){
                if(current_variant_id == square_product_arr.variants[i].id){
                    current_variant_price = parseFloat(square_product_arr.variants[i].price)/100;
                }
            }
            if(sg_main_discount!='' && sg_main_discount>0 && current_variant_price>0){
                var main_discount_price = (parseFloat(current_variant_price)*parseFloat(sg_main_discount)/100).toFixed(2);
                sg_subs_text = sg_subs_text.replace('{{initial_discount_price}}',main_discount_price);
            }
            if(sg_recurr_discount!='' && sg_recurr_discount>0 && current_variant_price>0){
                var recurr_discount_price = (parseFloat(current_variant_price)*parseFloat(sg_recurr_discount)/100).toFixed(2);
                sg_subs_text = sg_subs_text.replace('{{recurring_discount_price}}',recurr_discount_price);
            }
            document.getElementById('proview_sas_lbl').innerHTML = sg_subs_text;
        }
    }
}

function open_swap_product_popup(shopify_shop,customer_id,sc_id){
    if(document.getElementById('inexSwappingProductModal')){
        document.getElementById('inexSwappingProductModal').style.display = 'block';
        document.getElementById("inexSwappingProductModal").scrollIntoView();

        document.getElementById("inexSwappingProductListDiv").innerHTML = '<div align="center">Loading...</div>';
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var obj = JSON.parse(this.responseText);
                if(obj.SUCCESS=='TRUE'){
                    document.getElementById("inexSwappingProductListDiv").innerHTML = obj.tblHtml;
                }
            }
        };
        xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("action=fe_fetch_customer_sqsbs_swap_products_list&shop="+shopify_shop+"&customer_id="+customer_id+"&sc_id="+sc_id);

        //handle modal close event
        if(document.getElementById('inexSwappingProductModalClose')){
            document.getElementById('inexSwappingProductModalClose').onclick = function () {
                document.getElementById('inexSwappingProductModal').style.display = 'none';
                document.getElementById("open_swap_product_"+sc_id).scrollIntoView();
            }
        }
    }
}
function swap_product_submit(shopify_shop,customer_id,sc_id,product_id){
    var variant_id = document.getElementById('select_swap_pro_var_'+product_id).value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var obj = JSON.parse(this.responseText);
            if(obj.SUCCESS=='TRUE'){
                document.getElementById('inexSwappingProductModalClose').click();
                location.reload();
            }else{
                alert(obj.MESSAGE);
            }
        }
    };
    xhttp.open("POST", "/apps/square-v2_1-development/controller_fe.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("action=fe_swap_product_submit&shop="+shopify_shop+"&customer_id="+customer_id+"&sc_id="+sc_id+"&variant_id="+variant_id);
}