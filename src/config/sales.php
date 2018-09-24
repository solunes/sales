<?php

return [

	// GENERAL
	'after_seed' => true,
	'desk_sale' => false,
	'delivery' => false,
	'delivery_country' => false,
	'delivery_city' => false,
	'ask_address' => false,
	'ask_coordinates' => false,
	'seed_shipping' => false,
	'sales_email' => true,
	'sales_cellphone' => true,
	'sales_username' => false,
	'ask_invoice' => true,
	'credit' => false,
	'refunds' => false,
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