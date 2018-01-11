<?php

namespace Solunes\Sales\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class MasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Módulo de Ventas
        $node_product_bridge = \Solunes\Master\App\Node::create(['name'=>'product-bridge', 'location'=>'sales', 'folder'=>'company']);
        $node_payment = \Solunes\Master\App\Node::create(['name'=>'payment', 'location'=>'sales', 'folder'=>'company']);
        $node_shipping = \Solunes\Master\App\Node::create(['name'=>'shipping', 'location'=>'sales', 'folder'=>'company']);
        $node_shipping_city = \Solunes\Master\App\Node::create(['name'=>'shipping-city', 'table_name'=>'shipping_cities', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_shipping->id]);
        $node_cart = \Solunes\Master\App\Node::create(['name'=>'cart', 'location'=>'sales', 'folder'=>'sales']);
        $node_cart_item = \Solunes\Master\App\Node::create(['name'=>'cart-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_cart->id]);
        $node_sale = \Solunes\Master\App\Node::create(['name'=>'sale', 'location'=>'sales', 'folder'=>'sales']);
        $node_sale_item = \Solunes\Master\App\Node::create(['name'=>'sale-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_sale_payment = \Solunes\Master\App\Node::create(['name'=>'sale-payment', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_sale_delivery = \Solunes\Master\App\Node::create(['name'=>'sale-delivery', 'table_name'=>'sale_deliveries', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_sale_credit = \Solunes\Master\App\Node::create(['name'=>'sale-credit', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_refund = \Solunes\Master\App\Node::create(['name'=>'refund', 'location'=>'sales', 'folder'=>'sales']);
        $node_refund_item = \Solunes\Master\App\Node::create(['name'=>'refund-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_refund->id]);
        $node_sp_bank_deposit = \Solunes\Master\App\Node::create(['name'=>'sp-bank-deposit', 'location'=>'sales', 'folder'=>'company']);

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        $sales_perm = \Solunes\Master\App\Permission::create(['name'=>'sales', 'display_name'=>'Negocio']);
        $admin->permission_role()->attach([$sales_perm->id]);

    }
}