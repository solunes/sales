<?php

namespace Solunes\Sales\App\Listeners;

class SavingSalePayment {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){
    		if($event->status=='paid'){
    			if($event->currency_id!=1){
    				$exchange = $event->exchange;
    			} else {
    				$exchange = NULL;
    			}
            	$arr[] = \Sales::register_account($event->parent->place_id, 'debit', $event->payment->account_id, $event->currency_id, $event->amount, $event->detail, $exchange);
            	\Sales::register_account_array($arr, $event->created_at, $event->parent->transaction_code);   			
    			$event->status = 'accounted';
    		}
            return $event;
    	}

    }

}
