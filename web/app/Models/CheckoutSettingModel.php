<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CheckoutSettingModel extends Model{

    protected $table = 'checkout_settings';

    public function select_by_shop($cs_shop){
        return DB::table($this->table)
            ->select('*')
            ->where('cs_shop',$cs_shop)
            ->get()->toArray();
    }
    public function insert_abandoned_checkout($insertArr){
        return DB::table($this->table)
            ->insertGetId($insertArr);
    }
    public function update_abandoned_checkout($cs_id,$updateArr){
        return DB::table($this->table)
            ->where('cs_id',$cs_id)
            ->update($updateArr);
    }
    public function delete_abandoned_checkout($cs_id){
        return DB::table($this->table)
            ->where('cs_id',$cs_id)
            ->delete();
    }


}
