<?php

return [

	// GENERAL
	'after_seed' => true,
	'desk_sale' => false,
	'delivery' => false,
	'delivery_country' => false,
	'default_country' => 1,
	'delivery_region' => false,
	'default_region' => 1,
	'delivery_city' => false,
	'default_city' => 1,
	'ask_address' => false,
	'ask_coordinates' => false,
	'delivery_select_day' => false,
	'delivery_select_hour' => false,
	'seed_shipping' => false,
	'sales_email' => true,
	'sales_cellphone' => true,
	'sales_username' => false,
	'sales_detail' => false,
	'ask_invoice' => true,
	'generate_invoice_pagostt' => true,
	'sale_edit_invoice' => true,
	'credit' => false,
	'refunds' => false,
	'company_relation' => false,
	'contact_relation' => false,
	'redirect_to_payment' => false,
	'send_confirmation_purchase_email' => false,
	'check_cart_stock' => true,
	'quotation' => true,
	'cart_quotation' => false,
	'sales_agency' => false,
	'sign_contract' => false,
	'editable_price' => false,
	'product_bridge_price' => false,
	'sale_duration_hours' => null, // Se debe definir si las ventas tienen una cantidad de horas antes de que expiren.

	// INTEGRATIONS
	'solunes_project' => false,

	// ACTIVE SHIPPING METHODS
	'own-office' => false,
	'unibol' => false,
	'ocs' => false,
	'dhl' => false,
	
	// CUSTOM SALES
    'custom_add_cart' => false, // Reglas de revisión al añadir producto personalizadas
    'custom_add_cart_fix' => false, // Reglas de revisión al añadir producto personalizadas
    'custom_add_cart_detail' => false, // DEtalle extra para añadir al carro de compras
    'custom_add_cart_extra_price' => false, // Precio extra para añadir al carro de compras

];