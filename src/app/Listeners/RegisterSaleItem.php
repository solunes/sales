<?php

namespace Solunes\Sales\App\Listeners;

class RegisterSaleItem {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){
            $place = $event->parent->place;
            $name = 'Productos retirados por venta';
            $response = \Sales::inventory_movement($place, $event->product, 'move_out', $event->quantity, $name, 'register_sale_item', $event, $event->parent->transaction_code);

            return $event;
    	}

    }

}
