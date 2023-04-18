<?php namespace App\Http\Controllers;

use App\Models\AppModel;
use App\Http\Controllers\InexController;

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

class AppController extends Controller {

	public $param=array();
	public $response=array();

	public function __construct()
	{
		/*$this->middleware(function ($request, $next) {
			parent::login_user_details();
			return $next($request);
		});*/
	}

	public function upload_logo(Request $request){
		$res = [];

		if(isset($_FILES['logo_file']['name']) && !empty($_FILES['logo_file']['name']) && $_FILES['logo_file']['error']=='0'){
			$AppModel = new AppModel();

			$session = $request->get('shopifySession');
			$shop = $session->getShop();
			$token = $session->getAccessToken();
			$rest_client = new Rest($shop, $token);
			$gql_client = new Graphql($shop, $token);

			$result = $rest_client->get('themes');
			$restDecodedBody = $result->getDecodedBody();
			if(isset($restDecodedBody['themes']) && !empty($restDecodedBody['themes'])) {
				$activeThemeId = '';
				foreach ($restDecodedBody['themes'] as $theme) {
					if (strtolower($theme['role']) == 'main') {
						$activeThemeId = $theme['id'];
						break;
					}
				}

				$mutation = 'mutation stagedUploadsCreate($input: [StagedUploadInput!]!) {
				  stagedUploadsCreate(input: $input) {
					stagedTargets { url resourceUrl parameters{ name value } }
					userErrors { field message }
				  }
				}';

				$response = $gql_client->query(
					[
						"query" => $mutation,
						"variables" => [
							"input" => [
								"httpMethod" => "POST",
								//"resource" => "FILE",
								"resource" => "IMAGE",
								"filename" => $_FILES['logo_file']['name'],
								"mimeType" => $_FILES['logo_file']['type'],
								"fileSize" => (string)$_FILES['logo_file']['size']
							]
						]
					]
				);
				$stagedUploadsCreateRes = $response->getDecodedBody();

				/*echo '<pre>';
				print_r($stagedUploadsCreateRes);
				echo '</pre>';*/

				if(isset($stagedUploadsCreateRes['data']['stagedUploadsCreate']['stagedTargets'][0]['url'])){
					$stgUpldObj = $stagedUploadsCreateRes['data']['stagedUploadsCreate']['stagedTargets'][0];
					$post_req = [];
					if(!empty($stgUpldObj['parameters'])){
						foreach($stgUpldObj['parameters'] as $prm){
							$post_req[$prm['name']] = $prm['value'];
						}
					}
					$post_req['file'] = curl_file_create($_FILES['logo_file']['tmp_name'], $_FILES['logo_file']['type'], $_FILES['logo_file']['name']);

					/*echo '<pre>';
					print_r($post_req);
					echo '</pre>';*/

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $stgUpldObj['url']);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_req);
					curl_setopt($ch, CURLOPT_HTTPHEADER, [
						"Content-Type: multipart/form-data"
					]);

					$ch_result = curl_exec($ch);
					curl_close($ch);

					/*echo '<pre>';
					print_r($ch_result);
					echo '</pre>';*/

					$mutation = 'mutation fileCreate($files: [FileCreateInput!]!) {
					  fileCreate(files: $files) {
						files { __typename
							... on MediaImage{ id image{ url } }
							... on GenericFile{ id url }
						}
					  }
					}';
					$response = $gql_client->query(
						[
							"query" => $mutation,
							"variables" => [
								"files" => [
									[
										"contentType" => "FILE",
										"alt" => $_FILES['logo_file']['name'],
										"originalSource" => $stgUpldObj['resourceUrl']
									]
								]
							]
						]
					);
					$fileCreateRes = $response->getDecodedBody();
					if(isset($fileCreateRes['data']['fileCreate']['files'][0]['id'])){
						$try_count = 1;

						try_again_get_image:

						$response = $gql_client->query(
							[
								"query" => 'query getImage($id: ID!) {
							  node(id: $id) {
								... on GenericFile { id url }
								... on MediaImage{ image{ url }}
							  }
							}
							',
								"variables" => [
									"id" => $fileCreateRes['data']['fileCreate']['files'][0]['id']
								]
							]
						);
						$getImageRes = $response->getDecodedBody();

						if(isset($getImageRes['data']['node']['url']) && !empty($getImageRes['data']['node']['url'])){
							$res['success'] = 'true';
							$res['message'] = '';
							$res['data'] = [
								'url' => $getImageRes['data']['node']['url']
							];
							$logo_url = $getImageRes['data']['node']['url'];
						}else if(isset($getImageRes['data']['node']['image']['url']) && !empty($getImageRes['data']['node']['image']['url'])){
							$res['success'] = 'true';
							$res['message'] = '';
							$res['data'] = [
								'url' => $getImageRes['data']['node']['image']['url']
							];
							$logo_url = $getImageRes['data']['node']['image']['url'];
						}else {
							if($try_count<=3){
								sleep('2');
								$try_count++;
								goto try_again_get_image;
							}
							$res['success'] = 'false';
							$res['message'] = 'Something went wrong in get image data.';
							$res['data'] = $getImageRes;
						}

						if(isset($logo_url) && !empty($logo_url)){
							$ins_setting_exist = $AppModel->select_insperity_setting_by_shop($shop);
							if(isset($ins_setting_exist[0]->ss_id) && !empty($ins_setting_exist[0]->ss_id)){
								$AppModel->update_insperity_setting($ins_setting_exist[0]->ss_id,$shop,$logo_url);
							}else{
								$AppModel->insert_insperity_setting($shop,$logo_url);
							}
						}
					}
					else{
						$res['success'] = 'false';
						$res['message'] = 'Something went wrong in file creation.';
						$res['data'] = $fileCreateRes;
					}
				}else{
					if(isset($stagedUploadsCreateRes['errors'][0]['message'])){
						$res['success'] = 'false';
						$res['message'] = $stagedUploadsCreateRes['errors'][0]['message'];
					}else{
						$res['success'] = 'false';
						$res['message'] = 'Something went wrong in file upload.';
					}
				}
			}
			else{
				$res['success'] = 'false';
				$res['message'] = 'Themes are not available.';
			}
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Something is went wrong with this image.';
		}

		echo json_encode($res,1);
	}
	public function get_upload_logo(Request $request){
		$AppModel = new AppModel();
		$res = [];

		$session = $request->get('shopifySession');
		$shop = $session->getShop();

		$ins_setting_exist = $AppModel->select_insperity_setting_by_shop($shop);
		if(isset($ins_setting_exist[0]) && !empty($ins_setting_exist[0])){
			$res['success'] = 'true';
			$res['message'] = '';
			$res['data'] = $ins_setting_exist[0];
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Record is not available.';
		}

		echo json_encode($res,1);
	}
	public function export_order(Request $request){
		$AppModel = new AppModel();
		$res = [];
		//$postData = $request->getContent();
		$orders = $AppModel->select_orders_for_export($_POST['start_date'],$_POST['end_date']);
		if(!empty($orders)){
			$export_file = time() . '-export.csv';
			$upload_dir = public_path().'/..'.Config::get('constant.ASSETS_LOCATION').Config::get('constant.EXPORT_ORDER_LOCATION');
			$export_file_url = asset(Config::get('constant.EXPORT_ORDER_LOCATION').$export_file);

			$export_file_path = $upload_dir . $export_file;
			$file_for_export_data = fopen($export_file_path,"w");

			fputcsv($file_for_export_data,[
				'Order Number','Order Date','Order Sub. Total','Order Shipping Charges','Order Tax','Order Total',
				'Department 1','Custom Payment Value 1','First Approver 1','First Approver Status 1','Second Approver 1','Second Approver Status 1',
				'Department 2','Custom Payment Value 2','First Approver 2','First Approver Status 2','Second Approver 2','Second Approver Status 2',
				'Department 3','Custom Payment Value 3','First Approver 3','First Approver Status 3','Second Approver 3','Second Approver Status 3','UserF Name','UserL Name','User Email','Ship To FName','Ship To LName','Ship To Address'
			]);
			foreach($orders as $single){
				fputcsv($file_for_export_data,[
					$single->so_order_number, !empty($single->so_add_date)?date('d-M-Y',$single->so_add_date):'', !empty($single->so_order_sub_total)? '$'.$single->so_order_sub_total:'', !empty($single->so_order_shipping_charges)? '$'.$single->so_order_shipping_charges:'', !empty($single->so_order_tax)? '$'.$single->so_order_tax:'', !empty($single->so_total_price)? '$'.$single->so_total_price:'',
					$single->so_department_1, $single->so_department_amount_1, $single->so_first_approver_1, $single->so_first_approver_status_1, $single->so_second_approver_1, $single->so_second_approver_status_1,
					$single->so_department_2, $single->so_department_amount_2, $single->so_first_approver_2, $single->so_first_approver_status_2, $single->so_second_approver_2, $single->so_second_approver_status_2,
					$single->so_department_3, $single->so_department_amount_3, $single->so_first_approver_3, $single->so_first_approver_status_3, $single->so_second_approver_3, $single->so_second_approver_status_3, $single->so_user_first_name, $single->so_user_last_name, $single->so_cust_email, $single->so_ship_to_first_name, $single->so_ship_to_last_name,$single->so_ship_to_address
				]);
			}

			fclose($file_for_export_data);

			$res['success'] = 'true';
			$res['message'] = '';
			$res['export_url'] = $export_file_url;
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Orders are not available.';
		}
		echo json_encode($res,1);
	}

	public function get_shipping_zones(Request $request){
		$res = [];

		$AppModel = new AppModel();
		$InexController = new InexController();

		$session = $request->get('shopifySession');
		$shop = $session->getShop();
		$token = $session->getAccessToken();
		$rest_client = new Rest($shop, $token);
		$gql_client = new Graphql($shop, $token);

		$countries = [];
		$result = $rest_client->get('shipping_zones');
		$restDecodedBody = $result->getDecodedBody();

		if(isset($restDecodedBody['shipping_zones']) && !empty($restDecodedBody['shipping_zones'])) {
			foreach($restDecodedBody['shipping_zones'] as $sz){
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
							//$countries[$country['code']]['provinces'] = array_merge($countries[$country['code']]['provinces'], $country['provinces']);
						}

						//usort($countries[$country['code']]['provinces'], 'sortByProvince');
						$InexController->array_sort_by_column($countries[$country['code']]['provinces'], 'name');
					}
				}
			}

			$res['success'] = 'true';
			$res['message'] = '';
			$res['data'] = $countries;
		}
		else{
			$res['success'] = 'false';
			$res['message'] = 'Themes are not available.';
		}

		echo json_encode($res,1);
	}
	public function post_int_ship_address(Request $request){
		$res = [];

		$AppModel = new AppModel();

		$session = $request->get('shopifySession');
		$shop = $session->getShop();

		if(isset($_POST['isa_id']) && !empty($_POST['isa_id'])){
			$AppModel->update_int_ship_address(
				$_POST['isa_id'],
				$_POST['isa_address_1'],$_POST['isa_address_2'],$_POST['isa_city'],
				$_POST['isa_state'],$_POST['isa_state_code'],$_POST['isa_country'],
				$_POST['isa_country_code'],$_POST['isa_zipcode'],$_POST['isa_first_name'],
				$_POST['isa_last_name']
			);

			$res['success'] = 'true';
			$res['message'] = 'Shipping address updated successfully';
		}else{
			$AppModel->insert_int_ship_address(
				$shop,
				$_POST['isa_address_1'],$_POST['isa_address_2'],$_POST['isa_city'],
				$_POST['isa_state'],$_POST['isa_state_code'],$_POST['isa_country'],
				$_POST['isa_country_code'],$_POST['isa_zipcode'],$_POST['isa_first_name'],
				$_POST['isa_last_name'],'Active',time()
			);
			$res['success'] = 'true';
			$res['message'] = 'Shipping address added successfully';
		}

		echo json_encode($res,1);
	}
	public function get_int_ship_address_list(Request $request){
		$res = [];

		$AppModel = new AppModel();

		$session = $request->get('shopifySession');
		$shop = $session->getShop();

		$list = $AppModel->select_int_ship_address_by_shop($shop);
		if(!empty($list)){
			$res['success'] = 'true';
			$res['message'] = '';
			$res['data'] = $list;
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Shipping addresses are not found.';
		}

		echo json_encode($res,1);
	}
	public function get_int_ship_address(Request $request,$id){
		$res = [];
		$AppModel = new AppModel();

		/*$session = $request->get('shopifySession');
		$shop = $session->getShop();*/

		$data = $AppModel->select_int_ship_address_by_id($id);
		if(!empty($data)){
			$res['success'] = 'true';
			$res['message'] = '';
			$res['data'] = $data[0];
			$res['id'] = $id;
		}else{
			$res['success'] = 'false';
			$res['message'] = 'Invalid request. Shipping address is not found.';
		}

		echo json_encode($res,1);
	}

}