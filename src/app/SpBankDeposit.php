<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class SpBankDeposit extends Model {
	
	protected $table = 'sp_bank_deposits';
	public $timestamps = true;

    /* Creating rules */
    public static $rules_send = array(
        'sale_id'=>'required',
        'image'=>'required',
    );

	/* Creating rules */
	public static $rules_create = array(
		'sale_id'=>'required',
		'status'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
        'sale_id'=>'required',
        'status'=>'required',
	);
                        
    public function sale() {
        return $this->belongsTo('Solunes\Sales\App\Sale');
    }

}