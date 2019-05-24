<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model {
	
	protected $table = 'cart_items';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'name'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'name'=>'required',
	);

    public function product_bridge() {
        return $this->belongsTo('Solunes\Business\App\ProductBridge');
    }
    
    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }
    
    public function getTotalWeightAttribute() {
        return round($this->weight*$this->quantity);
    }

    public function getTotalPriceAttribute() {
        return round($this->price*$this->quantity);
    }

}