<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbandonedCheckoutModel extends Model{

    protected $table = 'abandoned_checkout';

    public function select_by_cart_token($ac_cart_token){
        return DB::table($this->table)
            ->select('*')
            ->where('ac_cart_token',$ac_cart_token)
            ->get()->toArray();
    }
    public function select_by_token($ac_token){
        return DB::table($this->table)
            ->select('*')
            ->where('ac_token',$ac_token)
            ->get()->toArray();
    }
    public function insert_abandoned_checkout($insertArr){
        return DB::table($this->table)
            ->insertGetId($insertArr);
    }
    public function update_abandoned_checkout($ac_id,$updateArr){
        return DB::table($this->table)
            ->where('ac_id',$ac_id)
            ->update($updateArr);
    }
    public function delete_abandoned_checkout($ac_id){
        return DB::table($this->table)
            ->where('ac_id',$ac_id)
            ->delete();
    }
    public function delete_abandoned_checkout_by_token($ac_token){
        return DB::table($this->table)
            ->where('ac_token',$ac_token)
            ->delete();
    }


}
