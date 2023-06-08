<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderModel extends Model{

    protected $table = 'shop_orders';

    public function select_by_id($so_id){
        return DB::table($this->table)
            ->select('*')
            ->where('so_id',$so_id)
            ->get()->toArray();
    }
    public function select_by_order_id($so_order_id){
        return DB::table($this->table)
            ->select('*')
            ->where('so_order_id',$so_order_id)
            ->get()->toArray();
    }
    public function insert_shop_orders($insertArr){
        return DB::table($this->table)
            ->insertGetId($insertArr);
    }
    public function update_shop_orders($so_id,$updateArr){
        return DB::table($this->table)
            ->where('so_id',$so_id)
            ->update($updateArr);
    }
    public function update_shop_orders_by_order_id($so_order_id,$updateArr){
        return DB::table($this->table)
            ->where('so_order_id',$so_order_id)
            ->update($updateArr);
    }
    public function delete_shop_orders($so_id){
        return DB::table($this->table)
            ->where('so_id',$so_id)
            ->delete();
    }


}
