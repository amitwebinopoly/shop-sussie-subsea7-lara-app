<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppModel extends Model{

    public function select_insperity_setting_by_shop($ss_shop){
        return DB::table('insperity-setting')
            ->select('ss_id','ss_logo')
            ->where('ss_shop',$ss_shop)
            ->get()->toArray();
    }
    public function insert_insperity_setting($ss_shop,$ss_logo){
        return DB::table('insperity-setting')
            ->insertGetId([
                'ss_shop' => $ss_shop,
                'ss_logo' => $ss_logo
            ]);
    }
    public function update_insperity_setting($ss_id,$ss_shop,$ss_logo){
        return DB::table('insperity-setting')
            ->where('ss_id',$ss_id)
            ->update([
                'ss_shop' => $ss_shop,
                'ss_logo' => $ss_logo
            ]);
    }
    public function delete_insperity_setting($ss_id){
        return DB::table('insperity-setting')
            ->where('ss_id',$ss_id)
            ->delete();
    }

    public function select_orders_for_export($start_date,$end_date){
        $cond_start_date = '';
        if(isset($start_date) && !empty($start_date)){
            $sd_ts = strtotime($start_date.' 0:0');
            $cond_start_date = ' AND so_add_date>='.$sd_ts;
        }
        $cond_end_date = '';
        if(isset($end_date) && !empty($end_date)){
            $sd_ts = strtotime($end_date.' 23:59');
            $cond_end_date = ' AND so_add_date<='.$sd_ts;
        }

        $sql = 'SELECT so_order_number, so_add_date, so_order_sub_total, so_order_shipping_charges, so_order_tax, so_total_price, so_cust_email,
          so_department_1, so_department_amount_1, so_first_approver_1, so_first_approver_status_1, so_second_approver_1, so_second_approver_status_1,
          so_department_2, so_department_amount_2, so_first_approver_2, so_first_approver_status_2, so_second_approver_2, so_second_approver_status_2,
          so_department_3, so_department_amount_3, so_first_approver_3, so_first_approver_status_3, so_second_approver_3, so_second_approver_status_3, so_user_first_name, so_user_last_name, so_ship_to_first_name, so_ship_to_last_name, so_ship_to_address
        FROM shop_orders
        WHERE 1
        '.$cond_start_date.'
        '.$cond_end_date.'
        ';
        $results = DB::select( DB::raw($sql) );
        return $results;
    }

    public function select_int_ship_address_by_shop($isa_shop){
        return DB::table('international_shipping_addresses')
            ->select('*')
            ->where('isa_shop',$isa_shop)
            ->get()->toArray();
    }
    public function select_int_ship_address_by_id($isa_id){
        return DB::table('international_shipping_addresses')
            ->select('*')
            ->where('isa_id',$isa_id)
            ->get()->toArray();
    }
    public function insert_int_ship_address($isa_shop, $isa_address_1, $isa_address_2, $isa_city, $isa_state, $isa_state_code, $isa_country, $isa_country_code, $isa_zipcode, $isa_first_name, $isa_last_name, $isa_status, $isa_add_date){
        return DB::table('international_shipping_addresses')
            ->insertGetId([
                'isa_shop' => $isa_shop,
                'isa_address_1' => $isa_address_1,
                'isa_address_2' => $isa_address_2,
                'isa_city' => $isa_city,
                'isa_state' => $isa_state,
                'isa_state_code' => $isa_state_code,
                'isa_country' => $isa_country,
                'isa_country_code' => $isa_country_code,
                'isa_zipcode' => $isa_zipcode,
                'isa_first_name' => $isa_first_name,
                'isa_last_name' => $isa_last_name,
                'isa_status' => $isa_status,
                'isa_add_date' => $isa_add_date
            ]);
    }
    public function update_int_ship_address($isa_id, $isa_address_1, $isa_address_2, $isa_city, $isa_state, $isa_state_code, $isa_country, $isa_country_code, $isa_zipcode, $isa_first_name, $isa_last_name){
        return DB::table('international_shipping_addresses')
            ->where('isa_id',$isa_id)
            ->update([
                'isa_address_1' => $isa_address_1,
                'isa_address_2' => $isa_address_2,
                'isa_city' => $isa_city,
                'isa_state' => $isa_state,
                'isa_state_code' => $isa_state_code,
                'isa_country' => $isa_country,
                'isa_country_code' => $isa_country_code,
                'isa_zipcode' => $isa_zipcode,
                'isa_first_name' => $isa_first_name,
                'isa_last_name' => $isa_last_name,
            ]);
    }
    public function delete_int_ship_address($isa_id){
        return DB::table('international_shipping_addresses')
            ->where('isa_id',$isa_id)
            ->delete();
    }
}
