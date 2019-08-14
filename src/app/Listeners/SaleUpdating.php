<?php

namespace Solunes\Sales\App\Listeners;

class SaleUpdating {

    public function handle($event) {
    	if($event->lead_status=='quotation-request'&&!$event->quotation_file){
            // Generar cotizacion en PDF y guardarla
            $event = \Sales::generateQuotationPdf($event);
            $event->lead_status = 'quotation-done';
    	}
        if($event->lead_status=='signing-contract'&&!$event->contract_file){
            // Generar un contrato en PDF y lo guarda
            $event = \Sales::generateContractPdf($event);
            $event->lead_status = 'signed-contract';
        }
        return $event;

    }

}
