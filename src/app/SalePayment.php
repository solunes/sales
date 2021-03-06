<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model {
	
	protected $table = 'sale_payments';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'currency_id'=>'required',
		'payment_method_id'=>'required',
		'pending_amount'=>'required',
		'status'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'currency_id'=>'required',
		'payment_method_id'=>'required',
		'pending_amount'=>'required',
		'status'=>'required',
	);

    public function sale() {
        return $this->belongsTo('Solunes\Sales\App\Sale', 'parent_id');
    }

    public function parent() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment');
    }

    public function payment_method() {
        return $this->belongsTo('Solunes\Payments\App\PaymentMethod');
    }

    public function sale_payment_items() {
        return $this->hasMany('Solunes\Sales\App\SalePaymentItem', 'parent_id');
    }

    public function sale_payment_item() {
        return $this->hasOne('Solunes\Sales\App\SalePaymentItem', 'parent_id');
    }

    public function online_bank_deposit() {
        return $this->hasOne('Solunes\Payments\App\OnlineBankDeposit');
    }

    public function online_bank_deposits() {
        return $this->hasMany('Solunes\Payments\App\OnlineBankDeposit');
    }

    public function cash_payment() {
        return $this->hasOne('Solunes\Payments\App\CashPayment');
    }

}