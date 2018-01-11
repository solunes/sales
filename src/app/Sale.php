<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model {
	
	protected $table = 'sales';
	public $timestamps = true;

    /* Sending rules */
    public static $rules_create_sale = array(
        //'currency_id'=>'required',
        //'product_id'=>'required',
    );

    /* Sending rules */
    public static $rules_send = array(
        'city_id'=>'required',
        'first_name'=>'required',
        'last_name'=>'required',
        'address'=>'required',
        'email'=>'required',
        'cellphone'=>'required',
        'shipping_id'=>'required',
        'payment_id'=>'required',
        'password'=>'required',
    );

    /* Sending auth rules */
    public static $rules_auth_send = array(
        'city_id'=>'required',
        'address'=>'required',
        'shipping_id'=>'required',
        'payment_id'=>'required',
    );

	/* Creating rules */
	public static $rules_create = array(
        'currency_id'=>'required',
        'place_id'=>'required',
        'type'=>'required',
        'status'=>'required',
        'invoice'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
        'currency_id'=>'required',
        'place_id'=>'required',
        'type'=>'required',
        'status'=>'required',
        'invoice'=>'required',
	);
      
    public function scopeFindId($query, $id) {
        return $query->where('id', $id);
    }

    public function scopeStatus($query, $status) {
        return $query->where('status', $status);
    }
        
    public function scopeCheckOwner($query) {
        if(\Auth::check()){
            $user_id = \Auth::user()->id;
        } else {
            $user_id = 0;
        }
        return $query->where('user_id', $user_id);
    }

    public function cart() {
        return $this->belongsTo('Solunes\Sales\App\Cart');
    }

    public function agency() {
        return $this->belongsTo('Solunes\Business\App\Agency');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }
		
    public function user() {
        return $this->belongsTo('App\User');
    }	

    public function sale_items() {
        return $this->hasMany('Solunes\Sales\App\SaleItem', 'parent_id');
    }
        
    public function sale_payments() {
        return $this->hasMany('Solunes\Sales\App\SalePayment', 'parent_id');
    }

    public function sale_credits() {
        return $this->hasMany('Solunes\Sales\App\SaleCredit', 'parent_id');
    }

    public function sale_deliveries() {
        return $this->hasMany('Solunes\Sales\App\SaleDelivery', 'parent_id');
    }

}