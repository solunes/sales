<?php

namespace Solunes\Sales\App\Listeners;

class RegisterRefundItem {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){

            /* Crear movimiento de inventario */
            $place = $event->parent->sale->place;
            $name = 'Productos recibidos por devolución';
            $response = \Sales::inventory_movement($place, $event->product, 'move_in', $event->refund_quantity, $name, 'register_refund_item', $event);
            
            return $event;
    	}

    }

}
