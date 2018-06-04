<?php

namespace Solunes\Sales\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class TruncateSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(config('sales.refunds')){
            \Solunes\Sales\App\RefundItem::truncate();
            \Solunes\Sales\App\Refund::truncate();
        }
        if(config('sales.credit')){
            \Solunes\Sales\App\SaleCredit::truncate();
        }
        if(config('sales.delivery')){
            \Solunes\Sales\App\SaleDelivery::truncate();
        }
        \Solunes\Sales\App\SalePayment::truncate();
        \Solunes\Sales\App\SaleItem::truncate();
        \Solunes\Sales\App\Sale::truncate();
        \Solunes\Sales\App\CartItem::truncate();
        \Solunes\Sales\App\Cart::truncate();
        if(config('sales.delivery')){
            \Solunes\Sales\App\ShippingCity::truncate();
            \Solunes\Sales\App\Shipping::truncate();
        }
    }
}