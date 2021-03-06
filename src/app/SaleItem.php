<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model {
	
	protected $table = 'sale_items';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'product_bridge_id'=>'required',
		'currency_id'=>'required',
		'quantity'=>'required',
		'price'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'product_bridge_id'=>'required',
		'currency_id'=>'required',
		'quantity'=>'required',
		'price'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

    public function sale() {
        return $this->belongsTo('Solunes\Sales\App\Sale', 'parent_id');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function product_bridge() {
        return $this->belongsTo('Solunes\Business\App\ProductBridge');
    }

    public function product_bridge_variation_option() {
        return $this->belongsTo('Solunes\Business\App\ProductBridgeVariationOption');
    }

    public function product_bridge_variation() {
        if(config('solunes.product')){
            return $this->belongsToMany('\Solunes\Product\App\Variation', 'product_bridge_variation');
        } else {
            return $this->belongsToMany('\App\Variation', 'product_bridge_variation');
        }
    }

    public function getTotalPriceAttribute() {
        if($this->discount_price>0){
            return round(($this->price-$this->discount_price)*$this->quantity, 2);
        }
        return round($this->price*$this->quantity, 2);
    }

}