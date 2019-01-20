<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SalePaymentItem extends Model {
	
	protected $table = 'sale_payment_items';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'sale_item_id'=>'required',
		'amount'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'sale_item_id'=>'required',
		'amount'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\SalePayment');
    }

}