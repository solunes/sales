<?php

namespace Solunes\Sales\App\Listeners;

class SaleUpdating {

    public function handle($event) {
    	if($event->lead_status=='quotation-request'&&!$event->quotation_file){
            // Generar cotizacion en PDF y guardarla
            $event->load('sale_items');
            if(config('sales.delivery')){
                $event->load('sale_deliveries');
            }
            if(count($event->sale_items)>0){
                $event->lead_status = 'quotation-done';
                \Sales::generateQuotationPdf($event);
            }
    	}
        if($event->lead_status=='signing-contract'&&!$event->contract_file){
            // Generar un contrato en PDF y lo guarda
            $event->load('sale_items');
            if(config('sales.delivery')){
                $event->load('sale_deliveries');
            }
            if(count($event->sale_items)>0){
                $event->lead_status = 'signed-contract';
                \Sales::generateContractPdf($event);
            }
        }
        return $event;

    }

}
