<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class ShippingCity extends Model {
	
	protected $table = 'shipping_cities';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'city_id'=>'required',
		'shipping_days'=>'required',
		'shipping_cost'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'city_id'=>'required',
		'shipping_days'=>'required',
		'shipping_cost'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Shipping', 'parent_id');
    }

    public function city() {
        return $this->belongsTo('Solunes\Business\App\City');
    }

}