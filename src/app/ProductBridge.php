<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class ProductBridge extends Model {
	
	protected $table = 'product_bridges';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'category_id'=>'required',
        'currency_id'=>'required',
        'external_currency_id'=>'required',
        'partner_id'=>'required',
        'partner_transport_id'=>'required',
        'barcode'=>'required',
        'name'=>'required',
        'cost'=>'required',
        'price'=>'required',
        'no_invoice_price'=>'required',
        'printed'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
        'category_id'=>'required',
        'currency_id'=>'required',
        'external_currency_id'=>'required',
        'partner_id'=>'required',
        'partner_transport_id'=>'required',
        'barcode'=>'required',
        'name'=>'required',
        'cost'=>'required',
        'price'=>'required',
        'no_invoice_price'=>'required',
        'printed'=>'required',
	);

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

}