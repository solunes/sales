<?php

namespace Solunes\Sales\App\Listeners;

class SavingSale {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){

            if($event->status=='paid'){
                /* Crear cuentas de ventas */
                $asset_cash = \Solunes\Sales\App\Account::getCode('asset_cash_small')->id;
                $income_sale = \Solunes\Sales\App\Account::getCode('income_sale')->id;
                $name = 'Venta de mercaderia';
                $arr = [];
                if($event->paid_amount>$event->amount){
                    $sale_amount = $event->amount;
                } else {
                    $sale_amount = $event->paid_amount;
                }
                $taxes = 0;
                // Ajustes si incluye factura, no descuenta venta, se asigna como costo de impuestos.
                if($event->invoice){
                    $taxes = $sale_amount * 0.16;
                    //$sale_amount -= $taxes;
                }
                if($event->change>0){
                    $arr[] = \Sales::register_account($event->place_id, 'credit', $asset_cash, 1, $event->change, $name.': Cambio devuelto');
                }
                if($taxes>0){
                    $expense_sale_tax = \Solunes\Sales\App\Account::getCode('expense_sale_tax')->id;
                    $liability_ctp = \Solunes\Sales\App\Account::getCode('liability_ctp')->id;
                    $arr[] = \Sales::register_account($event->place_id, 'credit', $liability_ctp, 1, $taxes, $name);
                    $arr[] = \Sales::register_account($event->place_id, 'debit', $expense_sale_tax, 1, $taxes, $name);
                }
                $arr[] = \Sales::register_account($event->place_id, 'credit', $income_sale, $event->currency_id, $sale_amount, $name);
                \Sales::register_account_array($arr, $event->created_at, $event->transaction_code);
                /* Crear cuentas de costo de envío */
                /*if($event->shipping_cost>0){
                    $concept = \Solunes\Sales\App\Account::getCode('expense_operating_com')->id;
                    \Sales::register_account($event->place_id, 'credit', $income_sale, $event->currency_id, $event->amount, $name);
                }*/
                $event->status = 'accounted';
            }
            return $event;
    	}

    }

}
