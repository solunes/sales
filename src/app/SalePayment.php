<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model {
	
	protected $table = 'sale_payments';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'product_id'=>'required',
		'currency_id'=>'required',
		'quantity'=>'required',
		'price'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'product_id'=>'required',
		'currency_id'=>'required',
		'quantity'=>'required',
		'price'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function payment() {
        return $this->belongsTo('Solunes\Sales\App\Payment');
    }

}