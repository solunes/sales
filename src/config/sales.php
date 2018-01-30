<?php

return [

	// GENERAL
	'after_seed' => true,
	'seed_shipping' => true,
	
	// ACTIVE SHIPPING METHODS
	'own-office' => true,
	'unibol' => true,
	'dhl' => false,
	
	// CUSTOM FORMS
    'item_get_after_vars' => ['purchase','product'], // array de nodos: 'node'
    'item_child_after_vars' => ['product'],
    'item_remove_scripts' => ['purchase'=>['leave-form']],
    'item_add_script' => ['purchase'=>['barcode-product'], 'product'=>['product']],

];