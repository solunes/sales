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
        if(config('sales.delivery')){
            $node_shipping = \Solunes\Master\App\Node::create(['name'=>'shipping', 'location'=>'sales', 'folder'=>'parameters']);
            $node_shipping_city = \Solunes\Master\App\Node::create(['name'=>'shipping-city', 'table_name'=>'shipping_cities', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_shipping->id]);
        }
        $node_cart = \Solunes\Master\App\Node::create(['name'=>'cart', 'location'=>'sales', 'folder'=>'parameters']);
        $node_cart_item = \Solunes\Master\App\Node::create(['name'=>'cart-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_cart->id]);
        $node_sale = \Solunes\Master\App\Node::create(['name'=>'sale', 'location'=>'sales', 'folder'=>'sales']);
        $node_sale_item = \Solunes\Master\App\Node::create(['name'=>'sale-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_sale_payment = \Solunes\Master\App\Node::create(['name'=>'sale-payment', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        $node_sale_payment_item = \Solunes\Master\App\Node::create(['name'=>'sale-payment-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_sale_payment->id]);
        if(config('sales.delivery')){
            $node_sale_delivery = \Solunes\Master\App\Node::create(['name'=>'sale-delivery', 'table_name'=>'sale_deliveries', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        }
        if(config('sales.credit')){
            $node_sale_credit = \Solunes\Master\App\Node::create(['name'=>'sale-credit', 'type'=>'child', 'location'=>'sales', 'parent_id'=>$node_sale->id]);
        }
        if(config('sales.refunds')){
            $node_refund = \Solunes\Master\App\Node::create(['name'=>'refund', 'location'=>'sales', 'folder'=>'sales']);
            $node_refund_item = \Solunes\Master\App\Node::create(['name'=>'refund-item', 'type'=>'subchild', 'location'=>'sales', 'parent_id'=>$node_refund->id]);
        }   
        if(config('business.seed_regions')&&config('sales.delivery')&&config('sales.seed_shipping')){
            if(config('sales.own-office')){
                $shipping_office = \Solunes\Sales\App\Shipping::create(['name'=>'Recógela de Nuestra Oficina','city_id'=>1,'content'=>'<p>Si vives en La Paz, puedes recoger el producto directamente de nuestras oficinas ubicadas en "Dirección de ejemplo" sin costo adicional.</p>']);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_office->id,'city_id'=>1,'shipping_days'=>0,'shipping_cost'=>0,'shipping_cost_extra'=>0]);
            }

            if(config('sales.unibol')){
                $shipping_unibol = \Solunes\Sales\App\Shipping::create(['name'=>'Unibol Courier','city_id'=>1,'content'=>'<p>Unibol Courier realiza envíos a todo el país entre 1 día y 3 días después de realizar el pedido, dependiendo a que ciudad o provincia vaya destinado. Se tienen costos distintos según peso y ciudad.</p>']);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>1,'shipping_days'=>1,'shipping_cost'=>15,'shipping_cost_extra'=>5]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>2,'shipping_days'=>1,'shipping_cost'=>15,'shipping_cost_extra'=>5]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>3,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>4,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>5,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>6,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>7,'shipping_days'=>2,'shipping_cost'=>25,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>8,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>9,'shipping_days'=>2,'shipping_cost'=>25,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>10,'shipping_days'=>2,'shipping_cost'=>20,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>11,'shipping_days'=>3,'shipping_cost'=>35,'shipping_cost_extra'=>20]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>12,'shipping_days'=>3,'shipping_cost'=>35,'shipping_cost_extra'=>20]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_unibol->id,'city_id'=>13,'shipping_days'=>3,'shipping_cost'=>35,'shipping_cost_extra'=>20]);
            }

            if(config('sales.ocs')){
                $shipping_ocs = \Solunes\Sales\App\Shipping::create(['name'=>'Overseas Courier Service','city_id'=>1,'content'=>'<p>OCS realiza envíos a todo el país entre 1 día y 4 días después de realizar el pedido, dependiendo a que ciudad o provincia vaya destinado. Se tienen costos distintos según peso y ciudad.</p>']);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>1,'shipping_days'=>1,'shipping_cost'=>5,'shipping_cost_extra'=>5]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>2,'shipping_days'=>1,'shipping_cost'=>10,'shipping_cost_extra'=>10]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>3,'shipping_days'=>1,'shipping_cost'=>18,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>4,'shipping_days'=>1,'shipping_cost'=>18,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>5,'shipping_days'=>1,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>6,'shipping_days'=>1,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>7,'shipping_days'=>2,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>8,'shipping_days'=>1,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>9,'shipping_days'=>3,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>10,'shipping_days'=>2,'shipping_cost'=>15,'shipping_cost_extra'=>15]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>11,'shipping_days'=>3,'shipping_cost'=>25,'shipping_cost_extra'=>20]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>12,'shipping_days'=>3,'shipping_cost'=>30,'shipping_cost_extra'=>25]);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_ocs->id,'city_id'=>13,'shipping_days'=>4,'shipping_cost'=>40,'shipping_cost_extra'=>25]);
            }

            if(config('sales.dhl')){
                $shipping_dhl = \Solunes\Sales\App\Shipping::create(['name'=>'DHL','city_id'=>1,'content'=>'<p>Puede realizar envíos a cualquier país del mundo por DHL.</p>']);
                \Solunes\Sales\App\ShippingCity::create(['parent_id'=>$shipping_dhl->id,'city_id'=>1,'shipping_days'=>20,'shipping_cost'=>50,'shipping_cost_extra'=>30]);
            }
        }

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        $sales_perm = \Solunes\Master\App\Permission::create(['name'=>'sales', 'display_name'=>'Ventas']);
        $admin->permission_role()->attach([$sales_perm->id]);

    }
}