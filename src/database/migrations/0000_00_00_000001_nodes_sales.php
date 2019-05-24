<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NodesSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Módulo de Ventas
        Schema::table('users', function (Blueprint $table) {
            if(config('sales.delivery')){
                if(config('sales.ask_coordinates')){
                    $table->string('latitude')->nullable()->after('username');
                    $table->string('longitude')->nullable()->after('username');
                }
                if(config('sales.ask_address')){
                    $table->string('address_extra')->nullable()->after('username');
                    $table->string('address')->nullable()->after('username');
                }
                if(config('sales.delivery_city')){
                    $table->string('city_other')->nullable()->after('username');
                    $table->integer('city_id')->nullable()->after('username');
                }
            }
            if(config('sales.ask_invoice')){
                $table->string('nit_name')->nullable()->after('username');
                $table->string('nit_number')->nullable()->after('username');
            }
            $table->string('last_name')->nullable()->after('username');
            $table->string('first_name')->nullable()->after('username');
        });
        if(config('sales.delivery')){
            Schema::create('shippings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order')->nullable()->default(0);
                $table->string('name')->nullable();
                $table->integer('city_id')->unsigned();
                $table->boolean('active')->nullable()->default(1);
                $table->text('content')->nullable();
                $table->timestamps();
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            });
            Schema::create('shipping_cities', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->unsigned();
                $table->integer('city_id')->unsigned();
                $table->integer('shipping_days')->nullable();
                $table->decimal('shipping_cost', 10, 2)->nullable();
                $table->decimal('shipping_cost_extra', 10, 2)->nullable();
                $table->foreign('parent_id')->references('id')->on('shippings')->onDelete('cascade');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            });
        }
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->enum('type', ['cart','buy-now'])->default('cart');
            $table->enum('status', ['holding','sale'])->default('holding');
            $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('product_bridge_id')->unsigned();
            $table->integer('currency_id')->nullable()->default(1);
            $table->integer('quantity')->default(1);
            $table->text('detail')->nullable();
            $table->decimal('price', 10, 2);
            if(config('sales.delivery')){
                $table->decimal('weight', 10, 2)->nullable();
            }
            if(config('payments.sfv_version')>1||config('payments.discounts')){
                $table->decimal('discount_price', 10, 2);
            }
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('product_bridge_id')->references('id')->on('product_bridges')->onDelete('cascade');
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('customer_id')->nullable();
            $table->integer('agency_id')->unsigned();
            if(config('sales.company_relation')){
                $table->integer('company_id')->nullable();
            }
            if(config('sales.contact_relation')){
                $table->integer('contact_id')->nullable();
            }
            $table->integer('currency_id')->unsigned();
            if(config('sales.desk_sale')){
                $table->decimal('order_amount', 10, 2)->nullable();
                $table->decimal('change', 10, 2)->nullable()->default(0);
            }
            $table->string('name')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('status', ['holding','paid','accounted','cancelled','pending-delivery','delivered'])->nullable()->default('holding');
            $table->boolean('invoice')->default(0);
            $table->string('invoice_name')->nullable();
            $table->string('invoice_nit')->nullable();
            if(config('sales.desk_sale')){
                $table->enum('type', ['normal','online'])->nullable()->default('normal');
            }
            $table->string('transaction_code')->nullable();
            $table->string('proposal_file')->nullable();
            if(config('sales.solunes_project')){
                $table->boolean('solunes_project')->default(1);
            }
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
        Schema::create('sale_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('product_bridge_id')->unsigned();
            /*if(config('business.product_variations')){
                $table->integer('product_bridge_variation_option_id')->nullable();
            }*/
            $table->integer('currency_id')->unsigned();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->nullable();
            $table->text('detail')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            if(config('sales.delivery')){
                $table->decimal('weight', 10, 2)->default(0);
            }
            if(config('payments.sfv_version')>1){
                $table->string('economic_sin_activity')->nullable();
                $table->string('product_sin_code')->nullable();
                $table->string('product_internal_code')->nullable();
                $table->string('product_serial_number')->nullable(); // Para linea blanca y celulares
            }
            if(config('payments.sfv_version')>1||config('payments.discounts')){
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->decimal('discount_amount', 10, 2)->nullable();
            }
            $table->foreign('parent_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_bridge_id')->references('id')->on('product_bridges')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->integer('payment_id')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            if(config('payments.sfv_version')>1||config('payments.discounts')){
                $table->decimal('discount_amount', 10, 2)->nullable();
            }
            $table->decimal('pending_amount', 10, 2)->default(0);
            $table->enum('status', ['holding','to-pay','paid','accounted','frozen','cancelled'])->nullable()->default('holding');
            $table->text('detail')->nullable();
            $table->boolean('pay_delivery')->default(0);
            $table->decimal('exchange', 10, 2)->default(1);
            if(config('payments.sfv_version')>1){
                $table->string('commerce_user_code')->nullable();
                $table->string('customer_code')->nullable();
                $table->string('customer_ci_number')->nullable();
                $table->string('customer_ci_extension')->nullable();
                $table->string('customer_ci_expedition')->nullable();
                $table->string('invoice_type')->nullable();
                $table->string('payment_type_code')->nullable();
                $table->string('card_number')->nullable();
            }
            $table->foreign('parent_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
        Schema::create('sale_payment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->integer('sale_item_id')->unsigned();
            $table->decimal('pending_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->foreign('parent_id')->references('id')->on('sale_payments')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
        });
        if(config('sales.delivery')){
            Schema::create('sale_deliveries', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->unsigned();
                $table->integer('shipping_id')->unsigned();
                $table->integer('currency_id')->unsigned();
                $table->string('country_code')->nullable()->default('BO');
                $table->integer('region_id')->unsigned();
                $table->string('region_other')->nullable();
                $table->integer('city_id')->unsigned();
                $table->string('city_other')->nullable();
                $table->string('name')->nullable();
                $table->enum('status', ['holding','confirmed','paid','delivered'])->default('holding');
                $table->string('postal_code')->nullable();
                $table->string('address')->nullable();
                $table->string('address_extra')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('phone')->nullable();
                $table->string('delivery_time')->nullable();
                $table->decimal('total_weight', 10, 2)->nullable();
                $table->decimal('shipping_cost', 10, 2)->nullable();
                $table->foreign('parent_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('shipping_id')->references('id')->on('shippings')->onDelete('cascade');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            });
        }
        if(config('sales.credit')){
            Schema::create('sale_credits', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->unsigned();
                $table->date('due_date')->nullable();
                $table->string('detail')->nullable();
                $table->integer('currency_id')->unsigned();
                $table->decimal('amount', 10, 2)->default(0);
                $table->foreign('parent_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            });
        }
        if(config('sales.refunds')){
            Schema::create('refunds', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('sale_id')->unsigned();
                $table->integer('agency_id')->unsigned();
                $table->integer('currency_id')->unsigned();
                $table->decimal('amount', 10, 2)->nullable();
                $table->string('reference')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            });
            Schema::create('refund_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->unsigned();
                $table->integer('product_bridge_id')->unsigned();
                $table->integer('currency_id')->unsigned();
                $table->integer('initial_quantity')->nullable();
                $table->decimal('initial_amount', 10, 2)->nullable();
                $table->integer('refund_quantity')->nullable();
                $table->decimal('refund_amount', 10, 2)->nullable();
                $table->integer('sale_item_id')->nullable();
                $table->foreign('parent_id')->references('id')->on('refunds')->onDelete('cascade');
                $table->foreign('product_bridge_id')->references('id')->on('product_bridges')->onDelete('cascade');
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Módulo General de Negocio
        Schema::dropIfExists('refund_items');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('sale_credits');
        Schema::dropIfExists('sale_deliveries');
        Schema::dropIfExists('sale_payment_items');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('shipping_cities');
        Schema::dropIfExists('shippings');
    }
}
