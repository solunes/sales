<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SaleDelivery extends Model {
	
	protected $table = 'sale_deliveries';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'shipping_id'=>'required',
		'currency_id'=>'required',
		'city_id'=>'required',
		'name'=>'required',
		'status'=>'required',
		'shipping_cost'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'shipping_id'=>'required',
		'currency_id'=>'required',
		'city_id'=>'required',
		'name'=>'required',
		'status'=>'required',
		'shipping_cost'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function shipping() {
        return $this->belongsTo('Solunes\Sales\App\Shipping');
    }

}