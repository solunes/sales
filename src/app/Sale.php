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
        'username'=>'required',
        'nit_number'=>'required',
        'nit_social'=>'required',
        'shipping_id'=>'required',
        'payment_method_id'=>'required',
        'password'=>'required',
    );

    /* Sending auth rules */
    public static $rules_auth_send = array(
        'city_id'=>'required',
        'address'=>'required',
        'nit_number'=>'required',
        'nit_social'=>'required',
        'shipping_id'=>'required',
        'payment_method_id'=>'required',
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

    public function customer() {
        if(config('solunes.todotix-customer')){
            return $this->belongsTo('Todotix\Customer\App\Customer');
        } else if(config('solunes.customer')){
            return $this->belongsTo('Solunes\Customer\App\Customer');
        } else {
            return $this->belongsTo('App\Customer');
        }
    }   

    public function company() {
        return $this->belongsTo('Solunes\Business\App\Company');
    }   

    public function contact() {
        return $this->belongsTo('Solunes\Business\App\Contact');
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

    public function getDeliveriesAttribute() {
        if(config('sales.delivery')){
            return $this->sale_deliveries;
        } else {
            return [];
        }
    }

    public function sale_deliveries() {
        return $this->hasMany('Solunes\Sales\App\SaleDelivery', 'parent_id');
    }

    public function getTotalPendingAttribute() {
        $total = 0;
        foreach($this->sale_payments()->where('status','!=','paid')->get() as $payment){
            $total += $payment->amount;
        }
        return $total;
    }

    public function getTotalPaidAttribute() {
        $total = 0;
        foreach($this->sale_payments()->where('status','paid')->get() as $payment){
            $total += $payment->amount;
        }
        return $total;
    }

    public function project() {
        return $this->hasOne('Solunes\Project\App\Project');
    }

}