<?php 

namespace Solunes\Sales\App\Helpers;

class Sales {
  public static function get_cart($agency_id = NULL) {
    if($cart = \Solunes\Sales\App\Cart::checkOwner($agency_id)->checkCart()->where('status','holding')->orderBy('updated_at','DESC')->with('cart_items','cart_items.product_bridge')->first()){
      if($cart->session_id!=session()->getId()){
        $cart->session_id = session()->getId();
        $cart->save();
      }
      $cart->touch();
    } else {
      $cart = new \Solunes\Sales\App\Cart;
      if(\Auth::check()){
        $cart->user_id = \Auth::user()->id;
      }
      if($agency_id){
        $cart->agency_id = $agency_id;
      }
      $cart->session_id = \Session::getId();
      $cart->save();
    }
    return $cart;
  }

  public static function add_cart_item($cart, $product, $quantity, $detail = NULL, $custom_price = NULL) {
    if(!$custom_price){
      $custom_price = \Business::getProductPrice($product, $quantity);
    }
    if(!$detail){
      $detail = $product->name;
    }
    //$custom_price = \Business::calculate_currency($custom_price, $main_currency, $product->currency);
    if($cart_item = $cart->cart_items()->where('product_bridge_id', $product->id)->where('detail', $detail)->where('price', $custom_price)->first()){
      $cart_item->quantity = $cart_item->quantity + $quantity;
    } else {
      $cart_item = new \Solunes\Sales\App\CartItem;
      $cart_item->parent_id = $cart->id;
      $cart_item->product_bridge_id = $product->id;
      $cart_item->currency_id = $product->currency_id;
      $cart_item->quantity = $quantity;
      $cart_item->price = $product->full_price;
      $discount = $product->full_price - $custom_price;
      if($discount>0){
        $cart_item->discount_price = $product->full_price - $custom_price;
      }
      $cart_item->detail = $detail;
    }
    if(config('sales.delivery')){
        $cart_item->weight = $product->weight;
    }
    $cart_item->save();
    $cart->touch();
    return $cart_item;
  }

  public static function generateSingleSale($user_id, $customer_id, $currency_id, $payment_method_id, $invoice, $invoice_name, $invoice_number, $detail, $amount, $product_bridge_id, $quantity = 1, $agency_id = NULL) {
    if(!$agency_id){
      $agency_id = config('business.online_store_agency_id');
    }

    $currency = \Solunes\Business\App\Currency::find($currency_id);
    $product_bridge = \Solunes\Business\App\ProductBridge::find($product_bridge_id);
    if(config('sales.product_bridge_price')){
      $custom_price = \Business::getProductPrice($product_bridge, $quantity);
    } else {
      $custom_price = $amount;
    }
    $custom_price = \Business::calculate_currency($custom_price, $currency, $product_bridge->currency);
    $amount = $custom_price * $quantity;

    $sale = new \Solunes\Sales\App\Sale;
    $sale->user_id = $user_id;
    $sale->customer_id = $customer_id;
    if(config('sales.sales_agency')){
      $sale->agency_id = $agency_id;
    }
    $sale->currency_id = $currency_id;
    $sale->name = $detail;
    $sale->amount = $amount;
    $sale->order_amount = $amount;
    $sale->invoice = $invoice;
    $sale->invoice_name = $invoice_name;
    $sale->invoice_nit = $invoice_number;
    $sale->lead_status = 'sale';
    $sale->save();
   
    $sale_item = new \Solunes\Sales\App\SaleItem;
    $sale_item->parent_id = $sale->id;
    $sale_item->product_bridge_id = $product_bridge_id;
    $sale_item->currency_id = $currency_id;
    $sale_item->detail = $detail;
    $sale_item->price = $custom_price;
    $sale_item->quantity = $quantity;
    //$sale_item->weight = $cart_item->weight;
    $sale_item->total = $sale_item->price * $sale_item->quantity;
    $sale_item->save();

    // Sale Payment
    $sale_payment = new \Solunes\Sales\App\SalePayment;
    $sale_payment->parent_id = $sale->id;
    $sale_payment->payment_method_id = $payment_method_id;
    $sale_payment->currency_id = $currency_id;
    $sale_payment->exchange = 1;
    $sale_payment->amount = $amount;
    $sale_payment->pending_amount = $amount;
    if(config('sales.delivery')){
      $sale_payment->pay_delivery = 1;
    }
    $sale_payment->detail = 'Pago por compra: '.$detail;
    $sale_payment->save();

    // Sale Delivery
    /*$sale_delivery = new \Solunes\Sales\App\SaleDelivery;
    $sale_delivery->parent_id = $sale->id;
    $sale_delivery->shipping_id = $event->shipping_id;
    $sale_delivery->currency_id = 1;
    $sale_delivery->country_code = 'BO';
    $sale_delivery->region_id = 1;
    $sale_delivery->city_id = 1;
    $sale_delivery->name = 1;
    $sale_delivery->status = 'holding';
    $sale_delivery->shipping_cost = 0;
    $sale_delivery->save();*/

    $payment = \Payments::generatePayment($sale);
    $sale->updated_at = NULL;
    $sale->save();

    return $sale;
  }

  public static function generateSale($user_id, $customer_id, $currency_id, $payment_method_id, $invoice, $invoice_name, $invoice_number, $sale_details, $agency_id = NULL, $detail = NULL) {
    if(!$agency_id){
      $agency_id = config('business.online_store_agency_id');
    }

    $sale = new \Solunes\Sales\App\Sale;
    $sale->user_id = $user_id;
    $sale->customer_id = $customer_id;
    if(config('sales.sales_agency')){
      $sale->agency_id = $agency_id;
    }
    $sale->currency_id = $currency_id;
    $sale->invoice = $invoice;
    $sale->invoice_name = $invoice_name;
    $sale->invoice_nit = $invoice_number;
    $sale->lead_status = 'sale';
    if(config('sales.sales_detail')){
      $sale->detail = $detail;
    }
    $sale = \Sales::preProcessSale($sale, $sale_details, $currency_id, $agency_id);

    // Sale Payment
    $sale_payment = new \Solunes\Sales\App\SalePayment;
    $sale_payment->parent_id = $sale->id;
    $sale_payment->payment_method_id = $payment_method_id;
    $sale_payment->currency_id = $currency_id;
    $sale_payment->exchange = 1;
    $sale_payment->amount = $sale->amount;
    $sale_payment->pending_amount = $sale->amount;
    if(config('sales.delivery')){
      $sale_payment->pay_delivery = 1;
    }
    $sale_payment->detail = 'Pago por compra: '.$sale->name;
    $sale_payment->save();

    // Sale Delivery
    /*$sale_delivery = new \Solunes\Sales\App\SaleDelivery;
    $sale_delivery->parent_id = $sale->id;
    $sale_delivery->shipping_id = $event->shipping_id;
    $sale_delivery->currency_id = 1;
    $sale_delivery->country_code = 'BO';
    $sale_delivery->region_id = 1;
    $sale_delivery->city_id = 1;
    $sale_delivery->name = 1;
    $sale_delivery->status = 'holding';
    $sale_delivery->shipping_cost = 0;
    $sale_delivery->save();*/
    $sale->updated_at = NULL;
    $sale->save();

    $payment = \Payments::generatePayment($sale);

    return $sale;
  }

  public static function generateQuotation($user_id, $customer_id, $currency_id, $invoice, $sale_details, $agency_id = NULL, $detail = NULL) {
    $sale = new \Solunes\Sales\App\Sale;
    $sale->user_id = $user_id;
    $sale->customer_id = $customer_id;
    $sale->currency_id = $currency_id;
    $sale->invoice = $invoice;
    $sale->lead_status = 'quotation-request';
    if(config('sales.sales_detail')){
      $sale->detail = $detail;
    }
    $sale = \Sales::preProcessSale($sale, $sale_details, $currency_id, $agency_id);

    // Sale Delivery
    /*$sale_delivery = new \Solunes\Sales\App\SaleDelivery;
    $sale_delivery->parent_id = $sale->id;
    $sale_delivery->shipping_id = $event->shipping_id;
    $sale_delivery->currency_id = 1;
    $sale_delivery->country_code = 'BO';
    $sale_delivery->region_id = 1;
    $sale_delivery->city_id = 1;
    $sale_delivery->name = 1;
    $sale_delivery->status = 'holding';
    $sale_delivery->shipping_cost = 0;
    $sale_delivery->save();*/
    $sale->updated_at = NULL;
    $sale->save();
    return $sale;
  }

  public static function preProcessSale($sale, $sale_details, $currency_id, $agency_id) {
    $total = 0;
    foreach($sale_details as $sale_detail){
      if(isset($sale_detail['quantity'])&&$sale_detail['quantity']>0){
        $quantity = $sale_detail['quantity'];
      } else {
        $quantity = 1;
      }
      $total += $sale_detail['amount'] * $quantity;
    }
    if(count($sale_details)>1){
      $name = 'Pago general';
    } else if(count($sale_details)==1) {
      $name = $sale_details[0]['detail'];
    } else {
      $name = 'Sin detalle';
    }
    if(!$agency_id){
      $agency_id = config('business.online_store_agency_id');
    }

    $sale->agency_id = $agency_id;
    $sale->name = $name;
    $sale->amount = $total;
    $sale->order_amount = $total;
    $sale->save();

    foreach($sale_details as $sale_detail){
      $product_bridge = \Solunes\Business\App\ProductBridge::find($sale_detail['product_bridge_id']);
      if(isset($sale_detail['quantity'])&&$sale_detail['quantity']>0){
        $quantity = $sale_detail['quantity'];
      } else {
        $quantity = 1;
      }
      if(config('sales.product_bridge_price')){
        $custom_price = \Business::getProductPrice($product_bridge, $quantity);
      } else {
        $custom_price = $sale_detail['amount'];
      }
      $custom_price = \Business::calculate_currency($custom_price, $sale->currency, $product_bridge->currency);
      $sale_item = new \Solunes\Sales\App\SaleItem;
      $sale_item->parent_id = $sale->id;
      $sale_item->product_bridge_id = $product_bridge->id;
      $sale_item->currency_id = $currency_id;
      $sale_item->detail = $sale_detail['detail'];
      $sale_item->price = $custom_price;
      $sale_item->quantity = $quantity;
      $sale_item->total = $sale_item->price * $sale_item->quantity;
      //$sale_item->weight = $cart_item->weight;
      $sale_item->save();
    }

    return $sale;
  }

  public static function register_sale_payment($sale, $payment_method_id, $currency_id, $status, $amount, $detail, $exchange = 1) {
    $sale_payment = new \Solunes\Sales\App\SalePayment;
    $sale_payment->parent_id = $sale->id;
    $sale_payment->payment_method_id = $payment_method_id;
    $sale_payment->currency_id = $currency_id;
    $sale_payment->status = $status;
    $sale_payment->amount = $amount;
    $sale_payment->detail = $detail;
    $sale_payment->exchange = $exchange;
    if(config('sales.delivery')){
      $sale_payment->pay_delivery = 1;
    }
    $sale_payment->save();
    return $sale_payment;
  }

  public static function calculate_shipping_cost($shipping_id, $country_id, $city_id, $weight, $map_coordinates, $agency_id = NULL) {
    $shipping = \Solunes\Sales\App\Shipping::find($shipping_id);
    $shipping_cost = 0;
    $shipping_time = 0;
    $shipping_dates = [];
    $shipping_times = [];
    if($shipping->script){
      $agency = \Solunes\Business\App\Agency::find($agency_id);
      return \CustomFunc::calculate_shipping_cost($shipping, $country_id, $city_id, $weight, $map_coordinates, $agency);
    } else {
      if(config('sales.delivery_country')){
          $country = \Solunes\Business\App\Country::find($country_id);
          $shipping_cities = $shipping->shipping_cities()->where('country_id', $country_id)->with('city')->get();
          $shipping_city = $shipping->shipping_cities()->where('country_id', $country_id)->where('city_id', $city_id)->first();
          if(!$shipping_city){
            $shipping_city = $shipping->shipping_cities()->where('country_id', $country_id)->first();
            $city_id = $shipping_city->city_id;
          }
        } else {
          $shipping_cities = $shipping->shipping_cities()->with('city')->get();
          $shipping_city = $shipping->shipping_cities()->where('city_id', $city_id)->first();
        }
        $shipping_cities_array = [];
        if(config('sales.delivery_select_hour')){
          $shipping_times = $shipping->shipping_times()->lists('id','name')->toArray();
        }

        foreach($shipping_cities as $shipping_city_item){
          $shipping_cities_array[$shipping_city_item->city->name] = $shipping_city_item->city_id;
        }
        $other_city = false;
        if($shipping_city){
          $shipping_cost = $shipping_city->shipping_cost;
          $shipping_time = $shipping_city->shipping_time;
          if($shipping_city->city->other_city){
            $other_city = true;
          }
          $weight = $weight-1;
          if($weight>0){
              $shipping_cost += ceil($weight)*$shipping_city->shipping_cost_extra;
          }
          if(config('sales.delivery_select_day')){
            $shipping_dates = \Sales::getShippingDates($shipping, $shipping_time);
          }
          return ['shipping'=>true, 'shipping_cities'=>$shipping_cities_array, 'shipping_city'=>$shipping_city->city_id, 'other_city'=>$other_city, 'shipping_cost'=>$shipping_cost, 'shipping_time'=>$shipping_time, 'shipping_dates'=>$shipping_dates, 'shipping_times'=>$shipping_times];
        } else {
          $new_shipping_id = 2;
          return ['shipping'=>false,  'shipping_cities'=>$shipping_cities_array, 'shipping_city'=>1, 'other_city'=>$other_city, 'shipping_cost'=>0, 'shipping_time'=>1, 'new_shipping_id'=>$new_shipping_id, 'shipping_dates'=>$shipping_dates, 'shipping_times'=>$shipping_times];
        }
    }
  }

  public static function getShippingDates($shipping, $shipping_time) {
    $dates_array = [];
    $today = date('Y-m-d');
    $first_date = date('Y-m-d' , strtotime('+'.$shipping_time.' days'));
    $end_time = $shipping_time + $shipping->days_max;
    $last_date = date('Y-m-d' , strtotime('+'.$end_time.' days'));
    $period = new \DatePeriod(
         new \DateTime($first_date),
         new \DateInterval('P1D'),
         new \DateTime($last_date)
    );
    $dates_array = [];
    foreach ($period as $key => $value) {
      $day = strtolower($value->format('l'));
      $tag = 'enable_'.$day;
      if($shipping->$tag){
        $dates_array[] = $value->format('Y-m-d');
      }
    }
    return $dates_array;
  }

  public static function userRegistration($request) {
    $new_user = false;
    if(\Auth::check()) {
      $user = \Auth::user();
    } else {
      $new_user = true;
      if(config('sales.sales_email')&&\App\User::where('email', $request->input('email'))->first()){
        return 'El correo introducido ya fue registrado.';
      }
      if(config('sales.sales_cellphone')&&\App\User::where('cellphone', $request->input('cellphone'))->first()){
        return 'El teléfono introducido ya fue registrado.';
      }
      if(config('sales.sales_username')&&\App\User::where('username', $request->input('username'))->first()){
        return 'El carnet de identidad ya fue registrado.';
      }
      $first_name = $request->input('first_name');
      $last_name = $request->input('last_name');
      $user = new \App\User;
      $user->name = $first_name.' '.$last_name;
      if(config('sales.sales_email')){
        $user->email = $request->input('email');
      } else {
        $user->email = rand(10000000000,99999999999).'@noemail.com';
      }
      if(config('sales.sales_cellphone')){
        $user->cellphone = $request->input('cellphone');
      }
      if(config('sales.sales_username')){
        $user->username = $request->input('username');
      }
      $user->first_name = $first_name;
      $user->last_name = $last_name;
      $user->password = $request->input('password');
    }
    if(config('sales.delivery')){
      if(config('sales.delivery_city')){
        $city = \Solunes\Business\App\City::find($request->input('city_id'));
        $user->city_id = $city->id;
        $user->city_other = $request->input('city_other');
      }
      if(config('sales.ask_address')){
        $user->address = $request->input('address');
        $user->address_extra = $request->input('address_extra');
      }
      if(config('sales.ask_coordinates')){
        $user->latitude = $request->input('latitude');
        $user->longitude = $request->input('longitude');
      }
    }
    if(config('sales.ask_invoice')){
      $user->nit_number = $request->input('nit_number');
      $user->nit_name = $request->input('nit_social');
    }
    $user->save();
    if($new_user){
      $member = \Solunes\Master\App\Role::where('name', 'member')->first();
      $user->role_user()->attach([$member->id]);
      \Auth::loginUsingId($user->id);
    }
    return $customer;
  }

  public static function customerRegistration($request) {
    $new_user = false;
    $user = NULL;
    $external_id = NULL;
    if(\Auth::check()) {
      $user = \Auth::user();
      $customer = $user->customer;
      if(!$customer){
        $name = \External::reduceName($user->name);
        $customer = new \Solunes\Customer\App\Customer;
        $customer->user_id = $user->id;
        $customer->email = $user->email;
        $customer->ci_number = $user->username;
        $customer->cellphone = $user->cellphone;
        $customer->email = $user->email;
        $customer->first_name = $name['first_name'];
        $customer->last_name = $name['last_name'];
        $customer->nit_number = $customer->ci_number;
        $customer->nit_name = $name['last_name'];
        if(config('sales.delivery_city')){
          if($user->city_id){
            $customer->city_id = $user->city_id;
          } else {
            if($first_city = \Solunes\Business\App\City::first()){
              $customer->city_id = $first_city->id;
              $user->city_id = $first_city->id;
            }
          }
        }
        $user->save();
        $customer->save();
        $user->load('customer');
      }
    } else {
      $new_user = true;
      if(config('solunes.customer')&&config('customer.api_slave')){
        $external_response = \Customer::checkExternalCustomerByParameters($request);
      } else {
        $external_response = false;
      }
      if(config('sales.sales_email')&&\Solunes\Customer\App\Customer::where('email', $request->input('email'))->first()&&$external_response){
        return 'El correo introducido ya fue registrado.';
      }
      if(config('sales.sales_cellphone')&&\Solunes\Customer\App\Customer::where('cellphone', $request->input('cellphone'))->first()&&$external_response){
        return 'El teléfono introducido ya fue registrado.';
      }
      if(config('sales.sales_username')&&\Solunes\Customer\App\Customer::where('ci_number', $request->input('username'))->first()&&$external_response){
        return 'El carnet de identidad ya fue registrado.';
      }
      $first_name = $request->input('first_name');
      $last_name = $request->input('last_name');
      if(config('solunes.customer')&&config('customer.api_slave')){
        $external_id = \Customer::registerExternalCustomer($request);
      }
      $customer = new \Solunes\Customer\App\Customer;
      $customer->name = $first_name.' '.$last_name;
      if(config('sales.sales_email')){
        $customer->email = $request->input('email');
      } else {
        $customer->email = rand(10000000000,99999999999).'@noemail.com';
      }
      if(config('sales.sales_cellphone')){
        $customer->cellphone = $request->input('cellphone');
      }
      if(config('sales.sales_username')){
        $customer->ci_number = $request->input('username');
      }
      $customer->first_name = $first_name;
      $customer->last_name = $last_name;
      $customer->password = $request->input('password');
    }
    if(config('sales.delivery')){
      if(config('sales.delivery_city')){
        $city = \Solunes\Business\App\City::find($request->input('city_id'));
        if($city){
          $customer->city_id = $city->id;
        }
        $customer->city_other = $request->input('city_other');
      }
      if(config('sales.ask_address')){
        $customer->address = $request->input('address');
        $customer->address_extra = $request->input('address_extra');
      }
      if(config('sales.ask_cellphone')){
        $customer->cellphone = $request->input('cellphone');
      }
      if(config('sales.ask_coordinates')&&$request->has('map_coordinates')){
        $coordinates = explode(';',$request->input('map_coordinates'));
        if(isset($coordinates[0])&&isset($coordinates[1])&&!$customer->latitude&&!$customer->longitude){
          $customer->latitude = $coordinates[0];
          $customer->longitude = $coordinates[1];
        }
      }
    }
    if(config('sales.ask_invoice')){
      $customer->nit_number = $request->input('nit_number');
      $customer->nit_name = $request->input('nit_social');
    }
    if(config('customer.api_slave')){
      $customer->external_id = $external_id;
    }
    $customer->save();
    if($new_user){
      $user = $customer->user;
      \Auth::loginUsingId($user->id);
    }
    return $customer;
  }

  public static function cancellExpiredSales() {
    $datetime = date("Y-m-d H:i:s", strtotime("-2 days"));
    $expired_sales = \Solunes\Sales\App\Sale::where('status','holding')->where('created_at','<',$datetime)->get();
    foreach($expired_sales as $expired_sale){
      $expired_sale->status = 'cancelled';
      $expired_sale->save();
      $store_agency = \Solunes\Business\App\Agency::find(config('business.online_store_agency_id'));
      foreach($sale->sale_items as $sale_item){
        \Inventory::increase_inventory($store_agency, $sale_item->product_bridge, $sale_item->quantity);
      }
    }
  }

  public static function generateQuotationPdf($sale) {
    $array['item'] = $sale;
    $pdf = \PDF::loadView('sales::pdf.quotation-file', $array);
    $pdf = \Asset::apply_pdf_template($pdf, 'COTIZACIÓN');
    $sale->quotation_file = \Asset::upload_pdf_template($pdf, 'sale', 'quotation_file');
    return $sale;
  }
  
  public static function generateContractPdf($sale) {
    $array['item'] = $sale;
    $pdf = \PDF::loadView('sales::pdf.contract-file', $array);
    $pdf = \Asset::apply_pdf_template($pdf, 'CONTRATO');
    $sale->contract_file = \Asset::upload_pdf_template($pdf, 'sale', 'contract_file');
    return $sale;
  }

  public static function customerSuccessfulPayment($sale, $customer) {
    $send_custom_emails = false;
    $send_reservation = false;
    $send_ticket = false;
    $send_subscription = false;
    foreach($sale->sale_items as $sale_item){
      $product_bridge = $sale_item->product_bridge;
      if($product_bridge->delivery_type=='normal'){
        if(config('solunes.inventory')&&config('inventory.reduce_inventory_after_purchase')&&$sale_item->product_bridge->stockable){
          \Inventory::reduce_inventory($sale->agency, $sale_item->product_bridge, $sale_item->quantity);
        }
      } else if($product_bridge->delivery_type!='normal'){
        if($product_bridge->delivery_type=='digital'){
          \Sales::sendEmailDigitalGood($sale, $customer);
        } else if($product_bridge->delivery_type=='credit'){
          \Sales::sendEmailWalletCredit($sale, $customer);
          if(config('customer.credit_wallet')){
            \Customer::increaseCreditWallet($customer['id'], $sale_item->id, $sale_item->total);
          } else {
            // TODO: Notificación de Error, activar credit_wallet
          }
        } else if($product_bridge->delivery_type=='subscription'){
          $customer_subscription_month = \Solunes\Customer\App\CustomerSubscriptionMonth::where('sale_id', $sale->id)->where('status','pending')->first();
          if(!$customer_subscription_month){
            // TODO: Notificar con error de suscripción
          }
          $customer_subscription_month->status = 'paid';
          $customer_subscription_month->processing = 1;
          $customer_subscription_month->invoice_url = $customer_subscription_month->sale->sale_payment->payment->invoice_url;
          $customer_subscription_month->save();
        } else if($product_bridge->delivery_type=='reservation'){
          $send_reservation = true;
          $reservation = \Solunes\Reservation\App\Reservation::where('sale_id', $sale->id)->first();
          $reservation = \Reservation::generateReservationPdf($reservation);
          foreach($reservation->reservation_users as $reservation_user){
            $reservation_user->ticket_code = \Reservation::generateTicketCode();
            $reservation_user->manual_ticket_code = \Reservation::generateManualTicketCode();
            $reservation_user->status = 'paid';
            $reservation_user->save();
          }
          $reservation = \Reservation::generateVoucherPdf($reservation);
          $reservation->status = 'paid';
          $reservation->save();
        } else if($product_bridge->delivery_type=='ticket'){
          $send_ticket = true;
          $reservation = \Solunes\Reservation\App\Reservation::where('sale_id', $sale->id)->first();
          foreach($reservation->reservation_users as $reservation_user){
            $reservation_user->ticket_code = \Reservation::generateTicketCode();
            $reservation_user->manual_ticket_code = \Reservation::generateManualTicketCode();
            $reservation_user->status = 'paid';
            $reservation_user->save();
          }
          $reservation = \Reservation::generateVoucherPdf($reservation);
          $reservation->status = 'paid';
          $reservation->save();
        }
        $send_custom_emails = true;
      }
    }
    if(config('payments.notify_agency_on_payment')&&$sale->agency){
        \FuncNode::make_email('successful-payment', [$sale->agency->email], []);
    }
    if(config('payments.payment_blocks')){
        $payment_check = \Solunes\Payments\App\Payment::where('payment_check_id',$sale->sale_payment->payment_id)->first();
        if($payment_check){
            \Log::info('payment_check'.$payment_check->id);
            $payment_check->payment_check_id = NULL;
            $payment_check->save();
        }
    }
    if(config('solunes.inventory')){
        \Inventory::successful_sale($sale, $sale->sale_payment);
    }
    if(config('sales.sales_email')&&filter_var($customer['email'], FILTER_VALIDATE_EMAIL)){
      if($send_subscription){
        //\Sales::sendEmailSubscription($sale, $customer);
      }
      if($send_reservation){
        \Sales::sendEmailReservation($sale, $customer);
      }
      if($send_ticket){
        \Sales::sendEmailTicket($sale, $customer);
      }
      if(config('sales.send_confirmation_purchase_email')&&!$send_custom_emails){
        \Sales::sendConfirmationSaleEmail($sale, $customer);
      }
    }
    return $send_custom_emails;
  }

  public static function sendEmailDigitalGood($sale, $customer) {
    \Mail::send('sales::emails.successful-digital-good', ['sale'=>$sale, 'email'=>$customer['email']], function($m) use($customer) {
      if($customer['name']){
        $name = $customer['name'];
      } else {
        $name = 'Cliente';
      }
      $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('sales::mail.successful_digital_good_title'));
    });
  }

  public static function sendEmailWalletCredit($sale, $customer) {
    \Mail::send('sales::emails.successful-wallet-credit', ['sale'=>$sale, 'email'=>$customer['email']], function($m) use($customer) {
      if($customer['name']){
        $name = $customer['name'];
      } else {
        $name = 'Cliente';
      }
      $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('sales::mail.successful_wallet_credit_title'));
    });
  }

  public static function sendEmailReservation($sale, $customer) {
    \Mail::send('sales::emails.successful-reservation', ['sale'=>$sale, 'email'=>$customer['email']], function($m) use($customer) {
      if($customer['name']){
        $name = $customer['name'];
      } else {
        $name = 'Cliente';
      }
      $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('sales::mail.successful_reservation_title'));
    });
  }

  public static function sendEmailTicket($sale, $customer) {
    $reservation = \Solunes\Reservation\App\Reservation::where('sale_id', $sale->id)->first();
    \Mail::send('sales::emails.successful-ticket', ['sale'=>$sale, 'email'=>$customer['email'], 'reservation'=>$reservation], function($m) use($customer) {
      if($customer['name']){
        $name = $customer['name'];
      } else {
        $name = 'Cliente';
      }
      $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('sales::mail.successful_ticket_title'));
    });
  }

  public static function sendConfirmationSaleEmail($sale, $customer) {
    \Mail::send('sales::emails.successful-sale', ['sale'=>$sale, 'email'=>$customer['email']], function($m) use($customer) {
      if($customer['name']){
        $name = $customer['name'];
      } else {
        $name = 'Cliente';
      }
      $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('sales::mail.successful_sale_title'));
    });
  }

}