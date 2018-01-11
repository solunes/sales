<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SaleCredit extends Model {
	
	protected $table = 'sale_credits';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'due_date'=>'required',
		'detail'=>'required',
		'currency_id'=>'required',
		'amount'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'due_date'=>'required',
		'detail'=>'required',
		'currency_id'=>'required',
		'amount'=>'required',
	);

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

}