<?php namespace App\Http\Controllers;

use App\Models\AbandonedCheckoutModel;
use App\Models\AppModel;
use App\Models\CheckoutSettingModel;
use App\Http\Controllers\InexController;
use App\Http\Controllers\GraphqlController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;

class FrontEndController extends Controller {

	public $param=array();
	public $response=array();

	public function __construct()
	{
		/*$this->middleware(function ($request, $next) {
			parent::login_user_details();
			return $next($request);
		});*/
	}

	public function checkout($ac_token){
		$AbandonedCheckoutModel = new AbandonedCheckoutModel();
		$CheckoutSettingModel = new CheckoutSettingModel();
		$AppModel = new AppModel();
		$InexController = new InexController();

		$ac_data = $AbandonedCheckoutModel->select_by_token($ac_token);
		if(isset($ac_data[0]->ac_shop) && !empty($ac_data[0]->ac_shop)){
			$shop = $ac_data[0]->ac_shop;
			$cartinfo = json_decode($ac_data[0]->ac_cart_json,1);

			$shopCred = \App\Models\Session::where('shop', $shop)->get()->toArray();
			if(!empty($cartinfo) && !empty($shopCred)){
				$token = $shopCred[0]['access_token'];
				$rest_client = new Rest($shop, $token);
				$gql_client = new Graphql($shop, $token);

				// checkout setting from database
				$cs_data = $CheckoutSettingModel->select_by_shop($shop);
				$is_data = $AppModel->select_insperity_setting_by_shop($shop);

				$c = 0;
				$need_to_display_promocode_section = 'Yes';
				$need_to_calculate_tax = 'No';
				$item_arr_for_calc = [];
				$oos_inventory_var_arr = [];
				foreach($cartinfo['items'] as $single_item){
					if(@$single_item['product_id']!=''){
						$square_discount = [];
						$subscription_discount = [];
						/*if($cust_ws_tag!=''){
							//fetch wholesale pricing discount
							$ws_disc_data = Registry::get("WholesalePricing")->get_tag_product_discount($shop_url,$cust_ws_tag,$single_item['product_id'],$single_item['variant_id']);
							if(!empty($ws_disc_data)){
								$square_discount['cal_type'] = $ws_disc_data['cal_type'];
								$square_discount['discount'] = $ws_disc_data['discount'];
								$need_to_display_promocode_section = 'No';
							}
						}*/
						// subscription discount
						if(isset($single_item['properties']['_is_subs']) && $single_item['properties']['_is_subs']=='true' && isset($single_item['properties']['_subscription_discount']) && $single_item['properties']['_subscription_discount']>0){
							$subscription_discount['cal_type'] = '%';
							$subscription_discount['discount'] = $single_item['properties']['_subscription_discount'];
							$need_to_display_promocode_section = 'No';
						}
						$tmp_arr = [];
						$tmp_arr['id'] = $single_item['id'];
						$tmp_arr['product_id'] = $single_item['product_id'];
						$tmp_arr['price'] = $single_item['price'];
						$tmp_arr['quantity'] = $single_item['quantity'];
						$tmp_arr['properties'] = @$single_item['properties'];
						$tmp_arr['square_discount'] = $square_discount;
						$tmp_arr['subscription_discount'] = $subscription_discount;
						$item_arr_for_calc[] = $tmp_arr;

						$cartinfo['items'][$c]['square_discount'] = $square_discount;
						$cartinfo['items'][$c]['subscription_discount'] = $subscription_discount;

						//check inventory
						$query = 'query getProductVariant($id: ID!) {
                              productVariant(id: $id){
                                inventoryQuantity
                                inventoryManagement
                                inventoryPolicy
                                product{ title } } }';
						sleep(0.5);
						$response = $gql_client->query(
							[
								"query" => $query,
								"variables" => [
									"id" => 'gid://shopify/ProductVariant/'.$single_item['variant_id'].''
								]
							]
						);
						$var_data = $response->getDecodedBody();
						if(isset($var_data['data']['productVariant']['inventoryManagement']) && $var_data['data']['productVariant']['inventoryManagement']!='NOT_MANAGED'
							&& isset($var_data['data']['productVariant']['inventoryPolicy']) && $var_data['data']['productVariant']['inventoryPolicy']=='DENY'
						){
							$available_inv_qty = $var_data['data']['productVariant']['inventoryQuantity'];
							$pro_title = $var_data['data']['productVariant']['product']['title'];
							if($single_item['quantity'] > $available_inv_qty){
								array_push($oos_inventory_var_arr,[
									'title' => $single_item['title'],
									'image' => $single_item['image'],
								]);
							}
						}
					}
					$c++;
				}

				// get countries from shopify
				$countries = [];
				$result = $rest_client->get('shipping_zones');
				$ShippingZone = $result->getDecodedBody();
				if(isset($ShippingZone['shipping_zones']) && !empty($ShippingZone['shipping_zones'])){
					foreach($ShippingZone['shipping_zones'] as $sz){
						if(isset($sz['countries'])){
							foreach($sz['countries'] as $country){
								if(!isset($countries[$country['code']])){
									$countries[$country['code']] = [];
									$countries[$country['code']] = $country;
								}else{
									foreach($country['provinces'] as $province){
										if(!in_array($province['code'],array_column($countries[$country['code']]['provinces'],'code'))){
											$countries[$country['code']]['provinces'][] = $province;
										}
									}
								}

								$InexController->array_sort_by_column($countries[$country['code']]['provinces'], 'name');
							}
						}
					}
				}

				// get store data from shopify
				$result = $rest_client->get('shop');
				$shopData = $result->getDecodedBody();
				if(isset($shopData['taxes_included']) && $shopData['taxes_included']==false){
					$need_to_calculate_tax = 'Yes';
				}else{
					$need_to_calculate_tax = 'No';
				}

				$shop_currency_name = 'USD';
				$shop_currency_symbol = '$';
				if(isset($shopData['currency']) && !empty($shopData['currency'])){
					$shop_currency_name = $shopData['currency'];
				}
				if(isset($shopData['money_in_emails_format']) && !empty($shopData['money_in_emails_format'])){
					$shop_currency_symbol = str_replace(['{{amount}}','{{amount_no_decimals}}','{{amount_with_comma_separator}}','{{amount_no_decimals_with_comma_separator}}','{{amount_no_decimals_with_space_separator}}','{{amount_with_apostrophe_separator}}'],['','','','','',''],$shopData['money_in_emails_format']);
					if($shop_currency_symbol==''){
						$shop_currency_symbol=$shop_currency_name;
					}
				}

				if(isset($cs_data->cs_logo_position) && $cs_data->cs_logo_position=='center'){
					$logo_position = 'center';
				}else if(isset($cs_data->cs_logo_position) && $cs_data->cs_logo_position=='right'){
					$logo_position = 'right';
				}else{
					$logo_position = 'left';
				}
				if(isset($cs_data->cs_logo_width) && !empty($cs_data->cs_logo_width) && $cs_data->cs_logo_width>0){
					$logo_size = $cs_data->cs_logo_width.'px';
				}else if(isset($cs_data->cs_logo_size) && $cs_data->cs_logo_size=='small'){
					$logo_size = '150px';
				}else if(isset($cs_data->cs_logo_size) && $cs_data->cs_logo_size=='large'){
					$logo_size = '250px';
				}else{
					$logo_size = '200px';
				}

				$this->param['need_to_display_promocode_section'] = $need_to_display_promocode_section;
				$this->param['need_to_calculate_tax'] = $need_to_calculate_tax;
				$this->param['item_arr_for_calc'] = $item_arr_for_calc;
				$this->param['oos_inventory_var_arr'] = $oos_inventory_var_arr;

				$this->param['shop_currency_name'] = $shop_currency_name;
				$this->param['shop_currency_symbol'] = $shop_currency_symbol;
				$this->param['logo_position'] = $logo_position;
				$this->param['logo_size'] = $logo_size;

				$this->param['shop'] = $shop;
				$this->param['cartinfo'] = $cartinfo;
				$this->param['countries'] = $countries;
				$this->param['ac_data'] = $ac_data[0];
				$this->param['cs_data'] = !empty($cs_data)?$cs_data[0]:[];
				$this->param['is_data'] = !empty($is_data)?$is_data[0]:[];

				$this->param['page_meta_title'] = Config::get('constant.PAGE_META_TITLE');
				$this->param['proxy_path'] = Config::get('constant.PROXY_PATH');

				$this->param['checkout_assets_url'] = asset('').'checkout_assets';

				return view('checkout_default',$this->param);
			}else{
				return redirect()->back();
			}
		}else{
			return redirect()->back();
		}
	}

	public function post_abandoned_cart(Request $request){
		$res = [];
		$AbandonedCheckoutModel = new AbandonedCheckoutModel();
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$rest_client = new Rest($shop, $token);

		$cart_json = html_entity_decode($_POST['cart'], ENT_QUOTES);
		$cart_arr = json_decode($cart_json,1);

		$insertArr = [];
		$insertArr['ac_cart_token'] = $cart_arr['token'];
		$insertArr['ac_cart_json'] = $cart_json;
		$insertArr['ac_shipping_line_json'] = '';
		$insertArr['ac_step'] = 'FIRST_STEP';

		//get shopify customer data
		if(isset($_POST['customer_id']) && !empty($_POST['customer_id'])){
			$shopifyCustomerResult = $rest_client->get('customers/'.$_POST['customer_id'],[],['fields'=>'id,email,first_name,last_name,phone,addresses']);
			$shopifyCustomerData = $shopifyCustomerResult->getDecodedBody();
			if(isset($shopifyCustomerData['customer']) && !empty($shopifyCustomerData['customer'])){
				$insertArr['ac_customer_id'] = $shopifyCustomerData['customer']['id'];
				$insertArr['ac_customer_email'] = $shopifyCustomerData['customer']['email'];
				$insertArr['ac_customer_phone'] = $shopifyCustomerData['customer']['phone'];
				$insertArr['ac_shipping_first_name'] = $shopifyCustomerData['customer']['first_name'];
				$insertArr['ac_shipping_last_name'] = $shopifyCustomerData['customer']['last_name'];
				if(isset($shopifyCustomerData['customer']['addresses'][0]) && !empty($shopifyCustomerData['customer']['addresses'][0])){
					$insertArr['ac_shipping_address'] = $shopifyCustomerData['customer']['addresses'][0]['address1'];
					$insertArr['ac_shipping_address2'] = $shopifyCustomerData['customer']['addresses'][0]['address2'];
					$insertArr['ac_shipping_city'] = $shopifyCustomerData['customer']['addresses'][0]['city'];
					$insertArr['ac_shipping_pincode'] = $shopifyCustomerData['customer']['addresses'][0]['zip'];
					$insertArr['ac_shipping_state'] = $shopifyCustomerData['customer']['addresses'][0]['province'];
					$insertArr['ac_shipping_country'] = $shopifyCustomerData['customer']['addresses'][0]['country'];
				}
			}
		}

		$ac_exist = $AbandonedCheckoutModel->select_by_cart_token($cart_arr['token']);
		if(isset($ac_exist[0]) && !empty($ac_exist[0])){
			$ac_token = $ac_exist[0]->ac_token;
			$insertArr['ac_updated_on'] = @date('Y-m-d H:i:s');
			$AbandonedCheckoutModel->update_abandoned_checkout($ac_exist[0]->ac_id,$insertArr);
		}else{
			$ac_token = trim(base64_encode(time().rand(1000,9999)),'=');
			$insertArr['ac_token'] = $ac_token;
			$insertArr['ac_shop'] = $shop;
			$insertArr['ac_add_date'] = time();
			$insertArr['ac_created_on'] = @date('Y-m-d H:i:s');
			$AbandonedCheckoutModel->insert_abandoned_checkout($insertArr);
		}

		$res['success'] = 'true';
		$res['message'] = '';
		$res['ac_token'] = $ac_token;

		echo json_encode($res,1);

	}
	public function get_data_first_checkout(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$storefront_access_token = $shop_db_data['storefront_access_token'];
		//$rest_client = new Rest($shop, $token);

		$headers = array(
			//'X-Shopify-Access-Token' => $shopCred->access_token
			'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

		$checkout_data = $GraphqlController->create_first_shopify_checkout($_POST);
		if(isset($checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message']) && !empty($checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message'])){
			$res['success'] = 'false';
			$res['message'] = $checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message'];
		}else if(isset($checkout_data['data']['checkoutCreate']['checkout']['id']) && !empty($checkout_data['data']['checkoutCreate']['checkout']['id'])){
			$checkout_id = $checkout_data['data']['checkoutCreate']['checkout']['id'];
			$checkout_id = base64_encode($checkout_id);

			if(isset($_POST['discount_code']) && !empty($_POST['discount_code'])){
				$GraphqlController->apply_discount_code_checkout($checkout_id,$_POST['discount_code']);
			}

			$res['success'] = 'true';
			$res['message'] = '';
			$res['checkout_id'] = $checkout_id;

		}else{
			$res['success'] = 'false';
			$res['message'] = 'Error while calculation checkout details.';
		}

		echo json_encode($res,1);

	}
	public function grab_data_from_checkout(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$storefront_access_token = $shop_db_data['storefront_access_token'];
		//$rest_client = new Rest($shop, $token);

		$headers = array(
			//'X-Shopify-Access-Token' => $shopCred->access_token
			'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

		if(isset($_POST['checkout_id']) && !empty($_POST['checkout_id'])){
			$checkout_id = $_POST['checkout_id'];
			if(isset($_POST['shipping_email']) && !empty($_POST['shipping_email'])){
				$update_checkout_email_data = $GraphqlController->update_checkout_email($checkout_id,$_POST['shipping_email']);
				if(isset($update_checkout_email_data['data']['checkoutEmailUpdateV2']['checkout']['id'])){
					$checkout_id = base64_encode($update_checkout_email_data['data']['checkoutEmailUpdateV2']['checkout']['id']);
				}
			}
			if(isset($_POST['discount_code']) && !empty($_POST['discount_code'])){
				$GraphqlController->apply_discount_code_checkout($checkout_id,$_POST['discount_code']);
			}
			$checkout_update_data = $GraphqlController->update_checkout_shipping_address($_POST,$checkout_id);
			if(isset($checkout_update_data['data']['checkoutShippingAddressUpdateV2']['checkoutUserErrors'][0]['message'])){
				$res['success'] = 'false';
				$res['message'] = $checkout_update_data['data']['checkoutShippingAddressUpdateV2']['checkoutUserErrors'][0]['message'];
			}else{
				if(isset($_POST['shipping_handle']) && !empty($_POST['shipping_handle'])){
					$GraphqlController->update_checkout_shipping_line(html_entity_decode($_POST['shipping_handle']),$checkout_id);
				}

				$res['success'] = 'true';
				$res['message'] = '';
				$res['checkout_id'] = $checkout_id;
			}
		}else{
			$checkout_data = $GraphqlController->create_shopify_checkout($_POST);
			if(isset($checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message']) && !empty($checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message'])){
				$res['success'] = 'false';
				$res['message'] = $checkout_data['data']['checkoutCreate']['checkoutUserErrors'][0]['message'];
			}else if(isset($checkout_data['data']['checkoutCreate']['checkout']['id']) && !empty($checkout_data['data']['checkoutCreate']['checkout']['id'])){
				$checkout_id = $checkout_data['data']['checkoutCreate']['checkout']['id'];
				$checkout_id = base64_encode($checkout_id);

				$res['success'] = 'true';
				$res['message'] = '';
				$res['checkout_id'] = $checkout_id;
			}else{
				$res['success'] = 'false';
				$res['message'] = 'Error while calculation checkout details.';
			}
		}

		echo json_encode($res,1);

	}
	public function get_checkout_data(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$storefront_access_token = $shop_db_data['storefront_access_token'];
		$rest_client = new Rest($shop, $token);

		$headers = array(
			//'X-Shopify-Access-Token' => $shopCred->access_token
			'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

		$CheckoutSettingModel = new CheckoutSettingModel();
		$checkout_id = $_POST['checkout_id'];
		$checkout_fetch_count = 0;

		/*
            here below logic is for fetch proper shipping data from checkout.
            So untill "ready" status is false, it will not give any shipping methods.
            So we set counter which fetch data again, untill "ready" will be true, and this loop will not go to infinite due to this counter.
            */
		checkout_fetch2:
		$checkout_data = $GraphqlController->get_shopify_checkout($checkout_id);
		if(isset($checkout_data['data']['node']['ready']) && $checkout_data['data']['node']['ready']==false && $checkout_fetch_count<5){
			sleep(1);
			$checkout_fetch_count++;
			goto checkout_fetch2;
		}

		if(isset($checkout_data['data']['node']) && !empty($checkout_data['data']['node'])){
			$cs_data = $CheckoutSettingModel->select_by_shop($shop);

			//fetch checkout details for tax-line via rest api, becuase in graphql it will not provided
			$checkout_id_json = base64_decode($checkout_id);    // gid://shopify/Checkout/04c2c3ede4ae80cb2b258c101f4707e2?key=6813545e6115acbdd332712f07e7b773
			$checkout_id_arr = explode('?',$checkout_id_json);  // ['gid://shopify/Checkout/04c2c3ede4ae80cb2b258c101f4707e2', 'key=6813545e6115acbdd332712f07e7b773']
			$checkout_token = str_replace('gid://shopify/Checkout/','',$checkout_id_arr[0]);    // 04c2c3ede4ae80cb2b258c101f4707e2

			$shopifyCheckoutResult = $rest_client->get('checkouts/'.$checkout_token);
			$dataInfo = $shopifyCheckoutResult->getDecodedBody();

			if(isset($dataInfo['checkout']['tax_lines']) && !empty($dataInfo['checkout']['tax_lines'])){
				$checkout_data['data']['node']['taxLines'] = $dataInfo['checkout']['tax_lines'];
			}else{
				$checkout_data['data']['node']['taxLines'] = [];
			}
			$checkout_data['data']['node']['checkout_data'] = $dataInfo['checkout'];


			$item_wise_arr = [];
			if(isset($checkout_data['data']['node']['lineItems']['edges'])){
				foreach($checkout_data['data']['node']['lineItems']['edges'] as $lineItem){
					$variant_id = str_replace('gid://shopify/ProductVariant/','',$lineItem['node']['variant']['id']);
					$item_wise_arr[$variant_id][] = $lineItem;
				}
			}


			$line_items_fe = [];
			foreach($dataInfo['checkout']['line_items'] as $item){
				$dd = array_shift($item_wise_arr[$item['variant_id']]);
				$tmp_arr = [];
				$tmp_arr['variant_id'] = $item['variant_id'];
				$tmp_arr['product_id'] = $item['product_id'];
				$tmp_arr['title'] = $item['title'];
				$tmp_arr['variant_title'] = $item['variant_title'];
				$tmp_arr['image_url'] = $item['image_url'];
				$tmp_arr['price'] = $item['price'];
				$tmp_arr['discount_amount'] = isset($dd['node']['discountAllocations'][0]['allocatedAmount']['amount'])?$dd['node']['discountAllocations'][0]['allocatedAmount']['amount']:0;
				$tmp_arr['quantity'] = $item['quantity'];
				$tmp_arr['properties'] = $item['properties'];
				array_push($line_items_fe,$tmp_arr);
			}
			$checkout_data['data']['node']['line_items_fe'] = $line_items_fe;

			if(isset($cs_data->cs_local_pickup_status) && $cs_data->cs_local_pickup_status=='enable'){
				$lp_title = 'Local Pickup';
				if(!empty($cs_data->cs_local_pickup_label)){
					$lp_title = $cs_data->cs_local_pickup_label;
				}

				if(!isset($checkout_data['data']['node']['availableShippingRates']['shippingRates'])){
					$checkout_data['data']['node']['availableShippingRates']['shippingRates'] = [];
				}
				$checkout_data['data']['node']['availableShippingRates']['shippingRates'][] = [
					'handle' => 'local-pickup-0.00',
					'title' => $lp_title,
					'priceV2' => [
						'amount' => '0.00'
					]
				];
			}
			if(isset($_POST['allow_free_shipping']) && $_POST['allow_free_shipping']=='Yes'){
				$lp_title = 'Free Shipping';

				if(!isset($checkout_data['data']['node']['availableShippingRates']['shippingRates'])){
					$checkout_data['data']['node']['availableShippingRates']['shippingRates'] = [];
				}
				$checkout_data['data']['node']['availableShippingRates']['shippingRates'][] = [
					'handle' => 'free-shipping-0.00',
					'title' => $lp_title,
					'priceV2' => [
						'amount' => '0.00'
					]
				];
			}

			//sort shipping rates by amount
			$shippingRates = [];
			if(isset($checkout_data['data']['node']['availableShippingRates']['shippingRates']) && !empty($checkout_data['data']['node']['availableShippingRates']['shippingRates'])){
				foreach ($checkout_data['data']['node']['availableShippingRates']['shippingRates'] as $sprt){
					if(!isset($shippingRates[$sprt['priceV2']['amount']])){
						$shippingRates[$sprt['priceV2']['amount']] = [];
					}
					$shippingRates[$sprt['priceV2']['amount']][] = $sprt;
				}
				ksort($shippingRates);
				//$shippingRates = array_values($shippingRates);
				$final_sr = [];
				foreach ($shippingRates as $priceWiseSr){
					foreach ($priceWiseSr as $sr){
						$final_sr[] = $sr;
					}
				}
				$checkout_data['data']['node']['availableShippingRates']['shippingRates'] = $final_sr;
			}

			$res['DATA'] = $checkout_data['data']['node'];
		}

		$res['success'] = 'true';
		$res['message'] = '';

		echo json_encode($res,1);

	}
	public function store_abondoned_steps(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$storefront_access_token = $shop_db_data['storefront_access_token'];
		//$rest_client = new Rest($shop, $token);

		$headers = array(
			//'X-Shopify-Access-Token' => $shopCred->access_token
			'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

		$CheckoutSettingModel = new CheckoutSettingModel();
		$AbandonedCheckoutModel = new AbandonedCheckoutModel();

		$cs_data = $CheckoutSettingModel->select_by_shop($shop);
		if(isset($cs_data[0]->is_allow_send_abandoned_email) && $cs_data[0]->is_allow_send_abandoned_email=='Yes'){
			$_POST['cart'] = base64_decode($_POST['cart']);
			$cart_json = html_entity_decode($_POST['cart'], ENT_QUOTES);
			$ac_data = [
				'ac_customer_id' => $_POST['customer_id'],
				'ac_customer_email' => $_POST['customer_email'],
				'ac_customer_phone' => $_POST['customer_phone'],
				'ac_shipping_first_name'=>$_POST['shipping_first_name'],
				'ac_shipping_last_name'=>$_POST['shipping_last_name'],
				'ac_shipping_address'=>$_POST['shipping_address'],
				'ac_shipping_address2' =>$_POST['shipping_address2'],
				'ac_shipping_city' => $_POST['shipping_city'],
				'ac_shipping_pincode' => $_POST['shipping_pincode'],
				'ac_shipping_state' => $_POST['shipping_state'],
				'ac_shipping_country' => $_POST['shipping_country'],
				'ac_cart_json' => $cart_json,
				'ac_shipping_line_json' => $_POST['shipping_lines'],
				'ac_step' => $_POST['step'],
				'ac_add_date' => time()
			];
			if(isset($_POST['ac_token']) && !empty($_POST['ac_token'])){
				//if token exist, then data will update
				$ac_token = $_POST['ac_token'];
				$ac_data['ac_updated_on'] = @date('Y-m-d H:i:s');

				$ac_exist = $AbandonedCheckoutModel->select_by_token($ac_token);
				if(isset($ac_exist[0]) && !empty($ac_exist[0])){
					$AbandonedCheckoutModel->update_abandoned_checkout($ac_exist[0]->ac_id,$ac_data);
				}else{
					$res['success'] = 'false';
					$res['message'] = 'Invalid abandoned token';
				}
			}else{
				//if token is not exist, then data will insert
				$ac_token = trim(base64_encode(time().rand(1000,9999)),'=');
				$ac_data['ac_token'] = $ac_token;
				$ac_data['ac_shop'] = $_POST['shop'];
				$ac_data['ac_created_on'] = @date('Y-m-d H:i:s');
				$AbandonedCheckoutModel->insert_abandoned_checkout($ac_data);
			}

			$res['success'] = 'true';
			$res['message'] = '';
			$res['ac_token'] = $ac_token;
		}else{
			$res['success'] = 'false';
			$res['message'] = '';
		}

		echo json_encode($res,1);

	}
	public function apply_discount_in_checkout(Request $request){
		$res = [];

		if(isset($_POST['checkout_id']) && !empty($_POST['checkout_id'])
			&& isset($_POST['discount_code']) && !empty($_POST['discount_code'])
		){
			$checkout_id = $_POST['checkout_id'];
			$discount_code = $_POST['discount_code'];

			$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

			$shop = $shop_db_data['shop'];
			$token = $shop_db_data['access_token'];
			$storefront_access_token = $shop_db_data['storefront_access_token'];
			//$rest_client = new Rest($shop, $token);

			$headers = array(
				//'X-Shopify-Access-Token' => $shopCred->access_token
				'X-Shopify-Storefront-Access-Token' => $storefront_access_token
			);
			$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

			$discountInfo = $GraphqlController->apply_discount_code_checkout($checkout_id,$discount_code);
			if(isset($discountInfo['data']['checkoutDiscountCodeApplyV2']['checkoutUserErrors'][0]['message']) && !empty($discountInfo['data']['checkoutDiscountCodeApplyV2']['checkoutUserErrors'][0]['message'])){
				$res['success'] = 'false';
				$res['message'] = $discountInfo['data']['checkoutDiscountCodeApplyV2']['checkoutUserErrors'][0]['message'];
			}else{
				$res['success'] = 'true';
				$res['message'] = '';
			}
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Invalid request.';
		}

		echo json_encode($res,1);

	}
	public function remove_discount_in_checkout(Request $request){
		$res = [];

		if(isset($_POST['checkout_id']) && !empty($_POST['checkout_id'])){
			$checkout_id = $_POST['checkout_id'];

			$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

			$shop = $shop_db_data['shop'];
			$token = $shop_db_data['access_token'];
			$storefront_access_token = $shop_db_data['storefront_access_token'];
			//$rest_client = new Rest($shop, $token);

			$headers = array(
				//'X-Shopify-Access-Token' => $shopCred->access_token
				'X-Shopify-Storefront-Access-Token' => $storefront_access_token
			);
			$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

			$discountInfo = $GraphqlController->remove_discount_code_checkout($checkout_id);
			$res['success'] = 'true';
			$res['message'] = '';
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Invalid request.';
		}

		echo json_encode($res,1);

	}
	public function xxx(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		$storefront_access_token = $shop_db_data['storefront_access_token'];
		//$rest_client = new Rest($shop, $token);

		$headers = array(
			//'X-Shopify-Access-Token' => $shopCred->access_token
			'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, true); //pass true for store front apis

		echo json_encode($res,1);

	}

}