<?php namespace App\Http\Controllers;

use App\Models\AbandonedCheckoutModel;
use App\Models\AppModel;
use App\Models\CheckoutSettingModel;
use App\Http\Controllers\InexController;
use App\Http\Controllers\GraphqlController;

use App\Models\OrderItemModel;
use App\Models\OrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use PHPUnit\Framework\Exception;
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
				//$isa_data = $AppModel->select_int_ship_address_by_shop($shop);

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
				if(isset($shopData['shop']['taxes_included']) && $shopData['shop']['taxes_included']==false){
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
				//$this->param['isa_data'] = !empty($isa_data)?$isa_data:[];

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
	public function get_draft_order_data(Request $request){
		//$shipping_line
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		//$storefront_access_token = $shop_db_data['storefront_access_token'];
		//$rest_client = new Rest($shop, $token);

		$headers = array(
			'X-Shopify-Access-Token' => $token
			//'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, false); //pass true for store front apis

		$post_cart = base64_decode($_POST['cart']);
		$string = html_entity_decode($post_cart, ENT_QUOTES);
		$cartInfo = json_decode($string,1);
		if(!empty($cartInfo)){
			$shipping_line_arr = [];
			if(isset($_POST['shipping_lines']) && !empty($_POST['shipping_lines'])){
				$shipping_line_arr = json_decode($_POST['shipping_lines'],1);
			}
			$do_data = $this->calculate_draft_order($GraphqlController,$cartInfo,$shipping_line_arr);

			if(isset($do_data['data']['draftOrderCalculate']['calculatedDraftOrder']) && !empty($do_data['data']['draftOrderCalculate']['calculatedDraftOrder']) ){
				$res['success'] = 'true';
				$res['message'] = '';
				$res['data'] = $do_data['data']['draftOrderCalculate']['calculatedDraftOrder'];
			}else{
				$res['success'] = 'false';
				$res['message'] = 'Something went wrong while calculate draft order';
			}
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Invalid cart data';
		}
		echo json_encode($res,1);
	}
	public function store_abondoned_steps(Request $request){
		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];

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
	public function process_checkout(Request $request){
		$AbandonedCheckoutModel = new AbandonedCheckoutModel();

		$res = [];
		$shop_db_data = $request->get('shop_db_data'); // Provided by the EnsureFrontendShopAuth middleware

		$shop = $shop_db_data['shop'];
		$token = $shop_db_data['access_token'];
		//$storefront_access_token = $shop_db_data['storefront_access_token'];
		$rest_client = new Rest($shop, $token);
		$gql_client = new Graphql($shop, $token);

		$headers = array(
			'X-Shopify-Access-Token' => $token
			//'X-Shopify-Storefront-Access-Token' => $storefront_access_token
		);
		$GraphqlController = new GraphqlController($shop, $headers, false); //pass true for store front apis

		$cart_total = 0;
		$cart_subtotal = 0;
		$cart_shipping_price = 0;
		$cart_tax_price = 0;
		$discount_codes = '';
		$discount_price = 0;

		$post_cart = base64_decode($_POST['cart']);
		$string = html_entity_decode($post_cart, ENT_QUOTES);
		$cartInfo = json_decode($string,1);

		$result = $rest_client->get('shop');
		$shopData = $result->getDecodedBody();
		if(isset($shopData['shop']['taxes_included']) && $shopData['shop']['taxes_included']==false){
			$need_to_calculate_tax = 'Yes';
		}else{
			$need_to_calculate_tax = 'No';
		}

		$shipping_line_arr = [];
		if(isset($_POST['shipping_lines']) && !empty($_POST['shipping_lines'])){
			$shipping_line_arr = json_decode($_POST['shipping_lines'],1);
		}
		$do_data = $this->calculate_draft_order($GraphqlController,$cartInfo,$shipping_line_arr);
		if(isset($do_data['data']['draftOrderCalculate']['calculatedDraftOrder']) && !empty($do_data['data']['draftOrderCalculate']['calculatedDraftOrder']) ){
			$calculatedDraftOrder = $do_data['data']['draftOrderCalculate']['calculatedDraftOrder'];

			//check inventory
			foreach($cartInfo['items'] as $item){
				$query = '{
                              productVariant(id: "gid://shopify/ProductVariant/'.$item['variant_id'].'"){
                                inventoryQuantity
                                inventoryManagement
                                inventoryPolicy
                                product{ title } } }';
				$var_data = $GraphqlController->runByQuery($query);
				if(isset($var_data['data']['productVariant']['inventoryManagement']) && $var_data['data']['productVariant']['inventoryManagement']!='NOT_MANAGED'
					&& isset($var_data['data']['productVariant']['inventoryPolicy']) && $var_data['data']['productVariant']['inventoryPolicy']=='DENY'
				){
					$available_inv_qty = $var_data['data']['productVariant']['inventoryQuantity'];
					$pro_title = $var_data['data']['productVariant']['product']['title'];
					if($item['quantity'] > $available_inv_qty){
						//if purchase-qty is greater than available-qty
						$res['success'] = 'false';
						$res['message'] = $pro_title.' has not enough quantity. Available qty is '.$available_inv_qty;
						echo json_encode($res,1);
						exit;
					}
				}
			}

			$currency = (isset($_POST['shop_currency_name']) && !empty($_POST['shop_currency_name']))?$_POST['shop_currency_name']:"USD";
			if(isset($_POST['email_marketing_subscribe']) && $_POST['email_marketing_subscribe'] == "1"){
				$email_marketing_subscribe = 'true';
			}else{
				$email_marketing_subscribe = 'false';
			}

			$insertOrder = array(
				'cart_token' => $cartInfo['token'],
				'email' => $_POST['checkout_email'],
				'note' => isset($_POST['note'])?$_POST['note']: $cartInfo['note'],
				'total_price' => $calculatedDraftOrder['totalPrice'],
				'subtotal_price' => $calculatedDraftOrder['subtotalPrice'],
				'total_tax' => $calculatedDraftOrder['totalTax'],
				//'tags' => 'paid',
				'currency' => $currency,
				'financial_status' => $calculatedDraftOrder['totalPrice']>0?'pending':'paid',
				'inventory_behaviour' => 'decrement_ignoring_policy',
				//'confirmed' => true,
				//'total_discounts' => isset(self::$payres['order']['total_discounts'])?self::$payres['order']['total_discounts']:0,
				'send_receipt' => true,
				'send_fulfillment_receipt' => true,

				// 'buyer_accepts_marketing' => intval($_POST['email_marketing_subscribe'])? "true": "false",
				'buyer_accepts_marketing' => $email_marketing_subscribe,
				"metafields" => [
					/*[
                        "key" => "transactionid",
                        'value' => $paymentRes['transactionid'],
                        'value_type' => 'string',
                        'namespace' => 'square_payment'
                    ]*/
				],
				'note_attributes' => [
					[
						'name' => 'Cost Center Number',
						'value' => ((isset($_POST['po_number']) && !empty($_POST['po_number']))?$_POST['po_number']:'---')
					],
					[
						'name' => 'First Approver 1',
						'value' => ((isset($_POST['first_approver_1']) && !empty($_POST['first_approver_1']))?$_POST['first_approver_1']:'---')
					],
					[
						'name' => 'First Approver Status 1',
						'value' => '---'
					],
					[
						'name' => 'Approver name',
						'value' => ((isset($_POST['department_1']) && !empty($_POST['department_1']))?$_POST['department_1']:'---')
					],
				],
				'billing_address' => array(
					'address1' => $_POST['billing']['address'],
					'address2' => $_POST['billing']['address2'],
					'city' => $_POST['billing']['city'],
					'company' => isset($_POST['billing']['company'])?$_POST['billing']['company']:'',
					'first_name' => $_POST['billing']['first_name'],
					'last_name' => $_POST['billing']['last_name'],
					'phone' => $_POST['billing']['phone'],
					'zip' => $_POST['billing']['pincode'],
					'country_code' => $_POST['billing']['country'],
					'province_code' => $_POST['billing']['state'],
					'default' => true
				),
				'shipping_address' => array(
					'address1' => $_POST['shipping']['address'],
					'address2' => $_POST['shipping']['address2'],
					'city' => $_POST['shipping']['city'],
					'company' => isset($_POST['shipping']['company'])?$_POST['shipping']['company']:'',
					'first_name' => $_POST['shipping']['first_name'],
					'last_name' => $_POST['shipping']['last_name'],
					'phone' => $_POST['phone'],
					'zip' => $_POST['shipping']['pincode'],
					'country_code' => $_POST['shipping']['country'],
					'province_code' => $_POST['shipping']['state']
				),
				'payment_gateway_names' => array(
					0 => 'custom'
				),
				'processing_method' => 'direct',
				'referring_site' => ''
			);
			if($calculatedDraftOrder['totalPrice']>0){
				$insertOrder['transactions'] = array(
					array(
						'amount' => $calculatedDraftOrder['totalPrice'],
						"gateway" => "manual",
						'kind' => 'sale'
					)
				);
			}

			if(isset($_POST['cart_attributes']) && !empty($_POST['cart_attributes'])){
				$cart_attributes_arr = json_decode(html_entity_decode($_POST['cart_attributes'], ENT_QUOTES),1);
				if(!empty($cart_attributes_arr)){
					foreach($cart_attributes_arr as $k=>$v){
						$tmp_arr = [];
						$tmp_arr['name'] = $k;
						$tmp_arr['value'] = $v;
						array_push($insertOrder['note_attributes'],$tmp_arr);
					}
				}
			}
			if(isset($_POST['additional_note']) && !empty($_POST['additional_note'])){
				$tmp_arr = [];
				$tmp_arr['name'] = 'Additional Note';
				$tmp_arr['value'] = $_POST['additional_note'];
				array_push($insertOrder['note_attributes'],$tmp_arr);
			}

			$customer = array(
				"accepts_marketing" => true,
				"email" => $_POST['checkout_email']
			);
			$insertOrder['customer'] = $customer;

			$sq_ttl_discount = 0;
			$subs_ttl_discount = 0;
			$lineItems = array();
			if (isset($cartInfo['items']) && !empty($cartInfo['items']) && count($cartInfo['items']) > 0) {

				foreach ($cartInfo['items'] as $item) {
					$item_total_discount = 0;
					$lineItems [] = array(
						'variant_id' => $item['variant_id'],
						'quantity' => $item['quantity'],
						'price' => number_format($item['price']/100,'2','.',''),
						'properties' => (array) $item['properties'],
						'total_discount' => number_format($item_total_discount,'2','.','')
					);
				}
				$insertOrder['line_items'] = $lineItems;
			}

			//add discount
			$discounts = array();
			/*if (isset($checkoutInfo['applied_discount']) && !empty($checkoutInfo['applied_discount'])) {
				$discounts[] = array(
					'amount' => $checkoutInfo['applied_discount']['amount'],
					'code' => $checkoutInfo['applied_discount']['title'],
					'type' => 'fixed_amount'    //don't change fixed_amount dynamic
				);
				$insertOrder['discount_codes'] = $discounts;
			}else{
				if(isset($checkoutInfo['line_items']) && !empty($checkoutInfo['line_items'])){
					foreach($checkoutInfo['line_items'] as $single_li){
						if(isset($single_li['applied_discounts'][0]) && !empty($single_li['applied_discounts'][0]) && $single_li['applied_discounts'][0]['amount']>0){
							$discounts[] = array(
								'amount' => $single_li['applied_discounts'][0]['amount'],
								'code' => $single_li['applied_discounts'][0]['description'],
								'type' => 'fixed_amount'    //don't change fixed_amount dynamic
							);
							$insertOrder['discount_codes'] = $discounts;
						}
					}
				}
			}
			if(isset($checkout_gdata['data']['node']['appliedGiftCards'][0]) && !empty($checkout_gdata['data']['node']['appliedGiftCards'][0])){
				if($checkout_gdata['data']['node']['appliedGiftCards'][0]['presentmentAmountUsed']['amount'] > 0){
					$giftcard_price = $checkout_gdata['data']['node']['appliedGiftCards'][0]['presentmentAmountUsed']['amount'];
					$discounts[] = array(
						'amount' => $giftcard_price,
						'code' => 'Gift card - xxxx '.$checkout_gdata['data']['node']['appliedGiftCards'][0]['lastCharacters'],
						'type' => 'fixed_amount'    //don't change fixed_amount dynamic
					);
					$insertOrder['discount_codes'] = $discounts;
				}
			}
			if(!empty($sq_ttl_discount) && $sq_ttl_discount>0){
				$discounts[] = array(
					'amount' => $sq_ttl_discount,
					'code' => 'Wholesale Discount',
					'type' => 'fixed_amount'    //don't change fixed_amount dynamic
				);
				$insertOrder['discount_codes'] = $discounts;
			}else if(!empty($subs_ttl_discount) && $subs_ttl_discount>0){
				$discounts[] = array(
					'amount' => $subs_ttl_discount,
					'code' => 'Subscription Discount',
					'type' => 'fixed_amount'    //don't change fixed_amount dynamic
				);
				$insertOrder['discount_codes'] = $discounts;
			}*/

			//shipping
			$shippingsArr = array();
			$local_pickup_exist = 'No';
			$free_shipping_exist = 'No';
			if(isset($_POST['shipping_lines']) && !empty($_POST['shipping_lines'])){
				$sl_json = html_entity_decode($_POST['shipping_lines'], ENT_QUOTES);
				$sl_arr = json_decode($sl_json,1);
				if(isset($sl_arr['id']) && $sl_arr['id']=='local-pickup-0.00'){
					$local_pickup_exist = 'Yes';
					$shippingsArr[] = array(
						'price' => $sl_arr['price'],
						'title' => $sl_arr['title']
					);
					$insertOrder['shipping_lines'] = $shippingsArr;
				}else if(isset($sl_arr['id']) && $sl_arr['id']=='free-shipping-0.00'){
					$free_shipping_exist = 'Yes';
					$shippingsArr[] = array(
						'price' => $sl_arr['price'],
						'title' => $sl_arr['title']
					);
					$insertOrder['shipping_lines'] = $shippingsArr;
				}
			}
			if ( ($local_pickup_exist=='No' && $free_shipping_exist=='No') && !empty($calculatedDraftOrder['shippingLine']) && count($calculatedDraftOrder['shippingLine']) > 0) {
				$shippingsArr[] = array(
					'price' => $calculatedDraftOrder['shippingLine']['discountedPriceSet']['presentmentMoney']['amount'],
					'title' => $calculatedDraftOrder['shippingLine']['title']
				);
				$insertOrder['shipping_lines'] = $shippingsArr;
			}

			//taxlines
			$taxArr = array();
			if (!empty($calculatedDraftOrder['taxLines']) != '' && count($calculatedDraftOrder['taxLines']) > 0/* && $need_to_calculate_tax=='Yes'*/) {
				foreach($calculatedDraftOrder['taxLines'] as $single_tl){
					$taxArr[] = array(
						'title' => $single_tl['title'],
						'price' => $single_tl['priceSet']['presentmentMoney']['amount'],
						'rate' => floatval($single_tl['rate'])
					);
				}
				$insertOrder['tax_lines'] = $taxArr;
			}

			if(isset($shopData['taxes_included']) && $shopData['taxes_included']==true){
				$insertOrder['taxes_included'] = $shopData['taxes_included'];
			}

			//create order
			$shopifyOrderResult = $rest_client->post('orders',['order'=>$insertOrder]);
			$lastorder = $shopifyOrderResult->getDecodedBody();
			if (isset($lastorder['order']['id'])) {
				try{
					$this->manage_approvers($lastorder['order'],$shop);
				}catch (Exception $e){

				}

				//if abandoned-checkout token exist then delete it from database
				if(isset($_POST['ac_token']) && !empty($_POST['ac_token'])){
					$AbandonedCheckoutModel->delete_abandoned_checkout_by_token($_POST['ac_token']);
				}

				$res['success'] = 'true';
				$res['message'] = '';
				$res['data'] = [
					'thankyou_page' => $lastorder['order']['order_status_url'],
					'shop_order_id' => $lastorder['order']['id'],
					'shop_order_number' => $lastorder['order']['order_number'],
					'shop_order_total_price' => $lastorder['order']['total_price'],
					'shop_order_total_tax' => $lastorder['order']['total_tax'],
					'shop_order_total_shipping' => @$lastorder['order']['total_shipping_price_set']['shop_money']['amount'],
					'shop_order_promocode' => @$lastorder['order']['discount_codes'][0]['code']
				];
			}


		}else{
			$res['success'] = 'false';
			$res['message'] = 'Something went wrong while calculate draft order';
		}

		echo json_encode($res,1);

	}
	public function manage_approvers($order,$shop){
		$OrderModel = new OrderModel();
		$OrderItemModel = new OrderItemModel();

		$order_id = $order['id'];
		$AwsController = new AwsController(env('AWS_BUCKET_ACCESS_KEY'), env('AWS_BUCKET_SECRET_KEY'), env('AWS_BUCKET_REGION'));

		$subject = 'Shop Subsea 7 Company Store- Approval is REQUIRED';

		$param = [
			'order' => $order
		];
		$message = view('mail_template.email_approver_required',$param)->render();

		$first_approver_1 = '';
		$second_approver_1 = '';
		$department_1 = '';
		$department_amount_1 = '';
		$first_approver_mail_body_1 = '';
		$second_approver_mail_body_1 = '';
		$approver_name = '';

		$first_approver_2 = '';
		$second_approver_2 = '';
		$department_2 = '';
		$department_amount_2 = '';
		$first_approver_mail_body_2 = '';
		$second_approver_mail_body_2 = '';

		$first_approver_3 = '';
		$second_approver_3 = '';
		$department_3 = '';
		$department_amount_3 = '';
		$first_approver_mail_body_3 = '';
		$second_approver_mail_body_3 = '';

		$po_number = '';
		$send_to_customer = 'No';

		$approve_link = route('approve_status_link',[$shop,'approved',$order_id]);
		$not_approve_link = route('approve_confirm_link',[$shop,'notapproved',$order_id]);

		foreach ($order['note_attributes'] as $key => $value) {
			if($value['name'] == 'Cost Center Number'){
				$po_number = $value['value'];
			}else if($value['name'] == 'First Approver 1'){
				$first_approver_1 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?fa1='.$value['value'];
					$nal = $not_approve_link.'?fa1='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$first_approver_mail_body_1 = $body;
				}
			}else if($value['name'] == 'Second Approver 1'){
				$second_approver_1 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?sa1='.$value['value'];
					$nal = $not_approve_link.'?sa1='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$second_approver_mail_body_1 = $body;
				}
			}else if($value['name'] == 'Department 1'){
				$department_1 = $value['value'];
			}else if($value['name'] == 'Department Amount 1'){
				$department_amount_1 = $value['value'];
			}else if($value['name'] == 'Approver name'){
				$approver_name = $value['value'];
			}else if($value['name'] == 'First Approver 2'){
				$first_approver_2 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?fa2='.$value['value'];
					$nal = $not_approve_link.'?fa2='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$first_approver_mail_body_2 = $body;
				}
			}else if($value['name'] == 'Second Approver 2'){
				$second_approver_2 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?sa2='.$value['value'];
					$nal = $not_approve_link.'?sa2='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$second_approver_mail_body_2 = $body;
				}
			}else if($value['name'] == 'Department 2'){
				$department_2 = $value['value'];
			}else if($value['name'] == 'Department Amount 2'){
				$department_amount_2 = $value['value'];
			}else if($value['name'] == 'First Approver 3'){
				$first_approver_3 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?fa3='.$value['value'];
					$nal = $not_approve_link.'?fa3='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$first_approver_mail_body_3 = $body;
				}
			}else if($value['name'] == 'Second Approver 3'){
				$second_approver_3 = $value['value'];
				if($value['value']!='' && $value['value']!='---'){
					$al = $approve_link.'?sa3='.$value['value'];
					$nal = $not_approve_link.'?sa3='.$value['value'];

					$body = str_replace('[APPROVE_LINK]',$al,$message);
					$body = str_replace('[NOT_APPROVE_LINK]',$nal,$body);
					$second_approver_mail_body_3 = $body;
				}
			}else if($value['name'] == 'Department 3'){
				$department_3 = $value['value'];
			}else if($value['name'] == 'Department Amount 3'){
				$department_amount_3 = $value['value'];
			}
		}

		if($first_approver_mail_body_1!='' && $first_approver_1!=''){
			$dp = 'Approver Name : '.$approver_name.'<br>Cost Center Number : '.$po_number.'<br>';
			$first_approver_mail_body_1 = str_replace('[DEPARTMENT_DATA]',$dp,$first_approver_mail_body_1);
			$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $first_approver_1, $subject, $first_approver_mail_body_1);
			$send_to_customer = 'Yes';
		}

		//send email to customer
		if($send_to_customer == 'Yes'){
			$param = [
				'order' => $order
			];
			$cust_email_body = view('mail_template.email_to_customer_while_order_placed',$param)->render();

			$cust_email_subject = 'Shop Subsea 7 Company Store- #'.$order['order_number'].' Approval is PENDING';
			$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $order['customer']['email'], $cust_email_subject, $cust_email_body,env('SUPER_ADMIN_EMAIL'));
		}

		$shipping_address = @$order['shipping_address']['address1'].', '.@$order['shipping_address']['address2'].', '.@$order['shipping_address']['city'].' '.@$order['shipping_address']['province_code'].' '.@$order['shipping_address']['zip'].', '.@$order['shipping_address']['country'].', '.@$order['shipping_address']['phone'];
		$order_insert_data = [
			"so_order_id" => $order['id'],
			"so_order_number" => $order['order_number'],
			"so_order_sub_total" => $order['subtotal_price'],
			"so_order_shipping_charges" => isset($order['total_shipping_price_set']['shop_money']['amount']) ? $order['total_shipping_price_set']['shop_money']['amount']:'0',
			"so_order_tax" => $order['total_tax'],
			"so_total_price" => $order['total_price'],
			"so_cust_id" => @$order['customer']['id'],
			"so_cust_email" => @$order['customer']['email'],
			"so_cust_name" => @$order['customer']['first_name'].' '.@$order['customer']['last_name'],
			"so_cust_phone" => @$order['customer']['phone'],

			"so_first_approver_1" => $first_approver_1,
			"so_first_approver_status_1" => '',
			"so_second_approver_1" => $second_approver_1,
			"so_second_approver_status_1" => '',
			"so_department_1" => $department_1,
			"so_department_amount_1" => $department_amount_1,

			"so_first_approver_2" => $first_approver_2,
			"so_first_approver_status_2" => '',
			"so_second_approver_2" => $second_approver_2,
			"so_second_approver_status_2" => '',
			"so_department_2" => $department_2,
			"so_department_amount_2" => $department_amount_2,

			"so_first_approver_3" => $first_approver_3,
			"so_first_approver_status_3" => '',
			"so_second_approver_3" => $second_approver_3,
			"so_second_approver_status_3" => '',
			"so_department_3" => $department_3,
			"so_department_amount_3" => $department_amount_3,

			"so_user_first_name" => @$order['customer']['first_name'],
			"so_user_last_name" => @$order['customer']['last_name'],
			"so_ship_to_first_name" => @$order['shipping_address']['first_name'],
			"so_ship_to_last_name" => @$order['shipping_address']['last_name'],
			"so_ship_to_address" => rtrim($shipping_address,', '),

			"so_json" => json_encode($order,1),
			"so_add_date" => time(),
			"so_modify_date" => ""
		];

		$so_id = $OrderModel->insert_shop_orders($order_insert_data);
		if(isset($order['line_items']) && !empty($order['line_items'])){
			foreach($order['line_items'] as $single_item){
				$item_insert_data = [
					"soi_so_id" => $so_id,
					"soi_product_id" => $single_item['product_id'],
					"soi_variant_id" => $single_item['variant_id'],
					"soi_title" => $single_item['title'].' '.$single_item['variant_title'],
					"soi_quantity" => $single_item['quantity'],
					"soi_price" => $single_item['price'],
					"soi_sku" => $single_item['sku'],
					"soi_vendor" => $single_item['vendor'],
					"soi_add_date" => time()
				];
				$OrderItemModel->insert_shop_order_items($item_insert_data);
			}
		}
	}
	public function approver_status($shop,$status,$oid){
		$OrderModel = new OrderModel();

		$shopCred = \App\Models\Session::where('shop', $shop)->get()->toArray();
		if(isset($shopCred[0]['id']) && !empty($shopCred[0]['id'])){
			$token = $shopCred[0]['access_token'];
			$rest_client = new Rest($shop, $token);

			$headers = array(
				'X-Shopify-Access-Token' => $token
				//'X-Shopify-Storefront-Access-Token' => $storefront_access_token
			);
			$GraphqlController = new GraphqlController($shop, $headers, false); //pass true for store front apis

			$AwsController = new AwsController(env('AWS_BUCKET_ACCESS_KEY'), env('AWS_BUCKET_SECRET_KEY'), env('AWS_BUCKET_REGION'));

			$shopifyOrderResult = $rest_client->get('orders/'.$oid);
			$orderInfo = $shopifyOrderResult->getDecodedBody();
			if(isset($orderInfo['order']) && !empty($orderInfo['order'])){
				$order = $orderInfo['order'];

				if($status=='approved'){
					$subject = 'Shop Subsea 7 Company Store- Order ' . $order['name'] . ' - Approved';
					$param = [
						'order' => $order
					];
					$message = view('mail_template.email_to_customer_order_approved',$param)->render();

					$firstto = env('SUPER_ADMIN_EMAIL');
					$secondto = $order['email'];

					echo "<h3> Your order status has been sent. Thank you! </h3>";
					if (!empty($firstto)) {
						$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $firstto, $subject, $message);
					}
					if (!empty($secondto)) {
						$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $secondto, $subject, $message);
					}

					// update note_attributes with approver-status
					$note_attributes = [];
					if (isset($order['note_attributes'])) {
						$note_attributes = $order['note_attributes'];
					}
					if( isset($_GET['fa1']) && !empty($_GET['fa1'])){
						$na_arr = [
							'name' => 'First Approver Status 1',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_1" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa1']) && !empty($_GET['sa1'])){
						$na_arr = [
							'name' => 'Second Approver Status 1',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_1" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if( isset($_GET['fa2']) && !empty($_GET['fa2'])){
						$na_arr = [
							'name' => 'First Approver Status 2',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_2" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa2']) && !empty($_GET['sa2'])){
						$na_arr = [
							'name' => 'Second Approver Status 2',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_2" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if( isset($_GET['fa3']) && !empty($_GET['fa3'])){
						$na_arr = [
							'name' => 'First Approver Status 3',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_3" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa3']) && !empty($_GET['sa3'])){
						$na_arr = [
							'name' => 'Second Approver Status 3',
							'value' => 'Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_3" => 'Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}

					//update note attributes in shopify order
					$updateOrder = array(
						'note_attributes' => $note_attributes
					);
					$rest_client->put('orders/'.$order['id'],['order'=>$updateOrder]);

					//if all approver status is "Approve", then order mark as paid, fullfil all item
					$order_data = $OrderModel->select_by_order_id($order['id']);

					$approver_count = 0;
					$status_approve_count = 0;
					if(!empty($order_data)){
						$order_data = $order_data[0];

						if( ($order_data->so_first_approver_1!='' && $order_data->so_first_approver_1!='---') ||
							($order_data->so_second_approver_1!='' && $order_data->so_second_approver_1!='---')
						){
							$approver_count++;
						}
						if($order_data->so_first_approver_status_1=='Approved' || $order_data->so_second_approver_status_1=='Approved'){
							$status_approve_count++;
						}
						//if($order_data->so_second_approver_1!='' && $order_data->so_second_approver_1!='---'){ $approver_count++; }
						//if($order_data->so_second_approver_status_1=='Approved'){ $status_approve_count++; }

						if( ($order_data->so_first_approver_2!='' && $order_data->so_first_approver_2!='---') ||
							($order_data->so_second_approver_2!='' && $order_data->so_second_approver_2!='---')
						){
							$approver_count++;
						}
						if($order_data->so_first_approver_status_2=='Approved' || $order_data->so_second_approver_status_2=='Approved'){
							$status_approve_count++;
						}
						//if($order_data->so_second_approver_2!='' && $order_data->so_second_approver_2!='---'){ $approver_count++; }
						//if($order_data->so_second_approver_status_2=='Approved'){ $status_approve_count++; }

						if( ($order_data->so_first_approver_3!='' && $order_data->so_first_approver_3!='---') ||
							($order_data->so_second_approver_3!='' && $order_data->so_second_approver_3!='---')
						){
							$approver_count++;
						}
						if($order_data->so_first_approver_status_3=='Approved' || $order_data->so_second_approver_status_3=='Approved'){
							$status_approve_count++;
						}
						//if($order_data->so_second_approver_3!='' && $order_data->so_second_approver_3!='---'){ $approver_count++; }
						//if($order_data->so_second_approver_status_3=='Approved'){ $status_approve_count++; }
					}

					if($approver_count==$status_approve_count && $approver_count>0 && $status_approve_count>0){
						//mark as paid
						$mutation = 'mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
						  orderMarkAsPaid(input: $input) {
							order { id }
							userErrors { field message }
						  }
						}
						';
						$input_query = '{
						  "input": {
							"id": "gid://shopify/Order/'.$order['id'].'"
						  }
						}';

						$GraphqlController->runByMutation($mutation,$input_query);

					}
				}
				else if($status=='notapproved'){
					$subject = 'Shop Subsea 7 Company Store- Order ' . $order['name'] . ' - Not approved';

					$param = [
						'order' => $order
					];
					$message = view('mail_template.email_to_customer_order_not_approved',$param)->render();

					$firstto = env('SUPER_ADMIN_EMAIL');
					$secondto = $order['email'];

					if (!empty($firstto)) {
						echo "<h3> Mail has been sent. </h3>";
						$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $firstto, $subject, $message);
					}
					if (!empty($secondto)) {
						$AwsController->sendEmail(env('AWS_AUTHORIZED_EMAIL'), $secondto, $subject, $message);
					}

					// update note_attributes with approver-status
					$note_attributes = [];
					if (isset($result['note_attributes'])) {
						$note_attributes = $result['note_attributes'];
					}
					if( isset($_GET['fa1']) && !empty($_GET['fa1'])){
						$na_arr = [
							'name' => 'First Approver Status 1',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_1" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa1']) && !empty($_GET['sa1'])){
						$na_arr = [
							'name' => 'Second Approver Status 1',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_1" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if( isset($_GET['fa2']) && !empty($_GET['fa2'])){
						$na_arr = [
							'name' => 'First Approver Status 2',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_2" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa2']) && !empty($_GET['sa2'])){
						$na_arr = [
							'name' => 'Second Approver Status 2',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_2" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if( isset($_GET['fa3']) && !empty($_GET['fa3'])){
						$na_arr = [
							'name' => 'First Approver Status 3',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_first_approver_status_3" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}
					if(isset($_GET['sa3']) && !empty($_GET['sa3'])){
						$na_arr = [
							'name' => 'Second Approver Status 3',
							'value' => 'Not Approved'
						];
						array_push($note_attributes,$na_arr);

						//update status in db
						$status_update_data = [
							"so_second_approver_status_3" => 'Not Approved'
						];
						$OrderModel->update_shop_orders_by_order_id($order['id'],$status_update_data);
					}

					if(isset($reason) && !empty($reason)){
						$na_arr = [
							'name' => 'Reason for not approve',
							'value' => $reason
						];
						array_push($note_attributes,$na_arr);
					}

					$updateOrder = array(
						'note_attributes' => $note_attributes
					);
					$rest_client->put('orders/'.$order['id'],['order'=>$updateOrder]);
				}
				else{
					echo 'Invalid status';
				}

			}else{
				echo 'Order info is not available.';
			}
		}
		else{
			echo 'Invalid request.';
		}
	}
	public function approver_confirm($shop,$status,$oid){
		$OrderModel = new OrderModel();

		$shopCred = \App\Models\Session::where('shop', $shop)->get()->toArray();
		if(isset($shopCred[0]['id']) && !empty($shopCred[0]['id']) && $status=='notapproved'){
			$token = $shopCred[0]['access_token'];
			$rest_client = new Rest($shop, $token);

			/*$headers = array(
				'X-Shopify-Access-Token' => $token
				//'X-Shopify-Storefront-Access-Token' => $storefront_access_token
			);
			$GraphqlController = new GraphqlController($shop, $headers, false); //pass true for store front apis

			$AwsController = new AwsController(env('AWS_BUCKET_ACCESS_KEY'), env('AWS_BUCKET_SECRET_KEY'), env('AWS_BUCKET_REGION'));*/

			$shopifyOrderResult = $rest_client->get('orders/'.$oid);
			$orderInfo = $shopifyOrderResult->getDecodedBody();
			if(isset($orderInfo['order']) && !empty($orderInfo['order'])){
				$order = $orderInfo['order'];

				$param = [
					'shop' => $shop,
					'order' => $order
				];
				return view('order_status_confirm',$param);
			}else{
				echo 'Order info is not available.';
			}
		}
		else{
			echo 'Invalid request.';
		}
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
	public function calculate_draft_order($GraphqlController,$cartInfo,$shipping_line_arr){
		$lineItems = '';
		if(isset($cartInfo['items']) && !empty($cartInfo['items'])){
			foreach($cartInfo['items'] as $item){
				$lineItems .= '{
                              "variantId": "gid://shopify/ProductVariant/'.$item['variant_id'].'",
                              "quantity": '.$item['quantity'].'
                            },';
			}
			$lineItems = trim($lineItems,',');
		}

		$mutation = 'mutation draftOrderCalculate($input: DraftOrderInput!) {
                  draftOrderCalculate(input: $input) {
                    calculatedDraftOrder {
                      subtotalPrice
                      totalPrice
                      totalShippingPrice
                      totalTax,
                      lineItems{
                        discountedTotal{ amount }
                        discountedUnitPrice{ amount }
                        image{ originalSrc }
                        quantity
                        title
                      }
                      availableShippingRates{
						handle price{ amount } title
					  }
                      shippingLine{
                        title
                        shippingRateHandle
                        discountedPriceSet{
                          presentmentMoney{ amount }
                        }
                      }
                      taxLines{
                        priceSet{ presentmentMoney{ amount } }
                        rate
                        ratePercentage
                        title
                      }
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }';
		$input_qry = '{
                  "input": {
                  ';
		if(isset($_POST['shipping_pincode']) && !empty($_POST['shipping_pincode'])){
			$input_qry .= '"shippingAddress": {
                      "address1": "'.@$_POST['shipping_address'].'",
                      "address2": "'.@$_POST['shipping_address2'].'",
                      "city": "'.@$_POST['shipping_city'].'",
                      "countryCode": "'.@$_POST['shipping_country'].'",
                      "firstName": "'.@$_POST['shipping_first_name'].'",
                      "lastName": "'.@$_POST['shipping_last_name'].'",
                      "phone": "'.@$_POST['shipping_phone'].'",
                      "provinceCode": "'.@$_POST['shipping_state'].'",
                      "zip": "'.@$_POST['shipping_pincode'].'"
                    },';
		}else if(isset($_POST['shipping']['pincode']) && !empty($_POST['shipping']['pincode'])){
			$input_qry .= '"shippingAddress": {
                      "address1": "'.@$_POST['shipping']['address'].'",
                      "address2": "'.@$_POST['shipping']['address2'].'",
                      "city": "'.@$_POST['shipping']['city'].'",
                      "countryCode": "'.@$_POST['shipping']['country'].'",
                      "firstName": "'.@$_POST['shipping']['first_name'].'",
                      "lastName": "'.@$_POST['shipping']['last_name'].'",
                      "phone": "'.@$_POST['phone'].'",
                      "provinceCode": "'.@$_POST['shipping']['state'].'",
                      "zip": "'.@$_POST['shipping']['pincode'].'"
                    },';
		}
		$input_qry .= '"lineItems": ['.$lineItems.']';

		if(isset($shipping_line_arr) && !empty($shipping_line_arr)){
            $input_qry .= ',
                "shippingLine": {
                  "price": '.$shipping_line_arr['price'].',
                  "title": "'.$shipping_line_arr['title'].'",
                  "shippingRateHandle": "'.$shipping_line_arr['id'].'"
                }';
        }

		$input_qry .= '}
                }';
		$do_data = $GraphqlController->runByMutation($mutation,$input_qry);
		return $do_data;
	}

}