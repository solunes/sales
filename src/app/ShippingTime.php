<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class ShippingTime extends Model {
	
	protected $table = 'shipping_times';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'time_in'=>'required',
		'time_out'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'time_in'=>'required',
		'time_out'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Shipping', 'parent_id');
    }

    /*public function setNameAttribute() {

    }*/

}