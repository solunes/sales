<?php

namespace Solunes\Sales\App\Listeners;

class SaleUpdating {

    public function handle($event) {
    	if($event->lead_status=='quotation-request'&&!$event->quotation_file){
            // Generar cotizacion en PDF y guardarla
            $event->lead_status = 'quotation-done';
            $event->load('sale_items');
            \Sales::generateQuotationPdf($event);
    	}
        if($event->lead_status=='signing-contract'&&!$event->contract_file){
            // Generar un contrato en PDF y lo guarda
            $event->lead_status = 'signed-contract';
            $event->load('sale_items');
            \Sales::generateContractPdf($event);
        }
        return $event;

    }

}
