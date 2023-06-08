<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItemModel extends Model{

    protected $table = 'shop_order_items';

    public function select_by_id($soi_id){
        return DB::table($this->table)
            ->select('*')
            ->where('soi_id',$soi_id)
            ->get()->toArray();
    }
    public function insert_shop_order_items($insertArr){
        return DB::table($this->table)
            ->insertGetId($insertArr);
    }
    public function update_shop_order_items($soi_id,$updateArr){
        return DB::table($this->table)
            ->where('soi_id',$soi_id)
            ->update($updateArr);
    }
    public function delete_shop_order_items($soi_id){
        return DB::table($this->table)
            ->where('soi_id',$soi_id)
            ->delete();
    }


}
