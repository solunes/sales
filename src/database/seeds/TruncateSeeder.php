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
        \Solunes\Sales\App\SpBankDeposit::truncate();
        \Solunes\Sales\App\RefundItem::truncate();
        \Solunes\Sales\App\Refund::truncate();
        \Solunes\Sales\App\SaleCredit::truncate();
        \Solunes\Sales\App\SaleDelivery::truncate();
        \Solunes\Sales\App\SalePayment::truncate();
        \Solunes\Sales\App\SaleItem::truncate();
        \Solunes\Sales\App\Sale::truncate();
        \Solunes\Sales\App\CartItem::truncate();
        \Solunes\Sales\App\Cart::truncate();
        \Solunes\Sales\App\ShippingCity::truncate();
        \Solunes\Sales\App\Shipping::truncate();
        \Solunes\Sales\App\Payment::truncate();
        \Solunes\Sales\App\ProductBridge::truncate();
    }
}