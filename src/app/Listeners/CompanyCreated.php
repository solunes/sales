<?php

namespace Solunes\Sales\App\Listeners;

class CompanyCreated {

    public function handle($event) {
    	// Revisar que no esté de manera externa
    	if($event&&!$event->external_code){
            $event = \Solunes\Sales\App\Controllers\Integrations\HubspotController::exportCompanyCreated($event);
            return $event;
    	}
    }

}
