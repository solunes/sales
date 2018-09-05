<?php

return [

	// GENERAL
	'after_seed' => true,
	'desk_sale' => false,
	'delivery' => false,
	'ask_address' => false,
	'ask_coordinates' => false,
	'credit' => false,
	'refunds' => false,
	'seed_shipping' => false,
	'company_relation' => false,
	'contact_relation' => false,
	
	// INTEGRATIONS
	'solunes_project' => false,

	// ACTIVE SHIPPING METHODS
	'own-office' => false,
	'unibol' => false,
	'dhl' => false,
	
	// CUSTOM FORMS
    'item_get_after_vars' => ['purchase','product'], // array de nodos: 'node'
    'item_child_after_vars' => ['product'],
    'item_remove_scripts' => ['purchase'=>['leave-form']],
    'item_add_script' => ['purchase'=>['barcode-product'], 'product'=>['product']],

];