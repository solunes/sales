<?php

namespace Solunes\Sales\App\Listeners;

class CartItemCreated {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){

            $event->transaction_code = \Sales::generate_code('sale');
            return $event;
    	}

    }

}
