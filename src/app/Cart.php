<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model {
	
    protected $table = 'carts';
    public $timestamps = true;

    /* Sending rules */
    public static $rules_send = array(
        'product_id'=>'required',
        'quantity'=>'required',
    );

    /* Creating rules */
    public static $rules_create = array(
    );

    /* Updating rules */
    public static $rules_edit = array(
        'id'=>'required',
    );
    
    public function cart_item() {
        return $this->hasOne('Solunes\Sales\App\CartItem', 'parent_id');
    }

    public function cart_items() {
        return $this->hasMany('Solunes\Sales\App\CartItem', 'parent_id');
    }
         
    public function pricing_rule() {
        return $this->hasOne('Solunes\Business\App\PricingRule', 'coupon_code', 'coupon_code');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }
     
    public function agency() {
        return $this->belongsTo('Solunes\Business\App\Agency');
    }

    public function scopeFindId($query, $id) {
        return $query->where('id', $id);
    }

    public function scopeStatus($query, $status) {
        return $query->where('status', $status)->has('cart_items');
    }

    public function scopeCheckCart($query) {
        return $query->where('type', 'cart');
    }

    public function scopeCheckBuyNow($query) {
        return $query->where('type', 'buy-now');
    }

    public function scopeCheckOwner($query, $agency_id = NULL) {
        if(\Auth::check()){
            $query = $query->where('user_id', \Auth::user()->id);
        } else {
            $query = $query->where('session_id', \Session::getId());
        }
        if($agency_id){
            $query->where('agency_id', $agency_id);
        }
        return $query;
    }

}