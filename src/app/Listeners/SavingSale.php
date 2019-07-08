<?php

namespace Solunes\Sales\App\Listeners;

class SavingSale {

    public function handle($event) {
    	if($event->lead_status=='quotation-done'&&!$event->quotation_file){
            // Generar cotizacion en PDF y guardarla
            $event = \Sales::generateQuotationPdf($event);
    	}
        if($event->lead_status=='signed-contract'&&!$event->contract_file){
            // Generar un contrato en PDF y lo guarda
            $event = \Sales::generateContractPdf($event);
        }
        return $event;

    }

}
