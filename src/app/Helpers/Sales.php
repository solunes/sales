<?php 

namespace Solunes\Sales\App\Helpers;

class Sales {
    public static function get_cart() {
        if($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->with('cart_items','cart_items.product_bridge')->first()){
          $cart->touch();
        } else {
          $cart = new \Solunes\Sales\App\Cart;
          if(\Auth::check()){
            $cart->user_id = \Auth::user()->id;
          }
          $cart->session_id = \Session::getId();
          $cart->save();
        }
        return $cart;
    }

    public static function add_cart_item($cart, $product, $quantity, $detail = NULL, $custom_price = NULL) {
        if($cart_item = $cart->cart_items()->where('product_bridge_id', $product->id)->where('detail', $detail)->where('price', $custom_price)->first()){
          $cart_item->quantity = $cart_item->quantity + $quantity;
        } else {
          $cart_item = new \Solunes\Sales\App\CartItem;
          $cart_item->parent_id = $cart->id;
          $cart_item->product_bridge_id = $product->id;
          $cart_item->quantity = $quantity;
          if($custom_price){
            $cart_item->price = $custom_price;
          } else {
            $cart_item->price = $product->real_price;
          }
          $cart_item->detail = $detail;
        }
        if(config('sales.delivery')){
            $cart_item->weight = $product->weight;
        }
        $cart_item->save();
        return $cart_item;
    }

    public static function register_sale_payment($sale, $payment_id, $currency_id, $status, $amount, $detail, $exchange = 1) {
        $sale_payment = new \Solunes\Sales\App\SalePayment;
        $sale_payment->parent_id = $sale->id;
        $sale_payment->payment_id = $payment_id;
        $sale_payment->currency_id = $currency_id;
        $sale_payment->status = $status;
        $sale_payment->amount = $amount;
        $sale_payment->detail = $detail;
        $sale_payment->exchange = $exchange;
        $sale_payment->save();
        return $sale_payment;
    }

    public static function calculate_shipping_cost($shipping_id, $city_id, $weight) {
        $shipping = \Solunes\Sales\App\Shipping::find($shipping_id);
        $shipping_city = $shipping->shipping_cities()->where('city_id', $city_id)->first();
        if($shipping_city){
            $shipping_cost = $shipping_city->shipping_cost;
            $weight = $weight-1;
            if($weight>0){
                $shipping_cost += ceil($weight)*$shipping_city->shipping_cost_extra;
            }
            return ['shipping'=>true, 'shipping_cost'=>$shipping_cost];
        } else {
            $new_shipping_id = 2;
            return ['shipping'=>false, 'shipping_cost'=>0, 'new_shipping_id'=>$new_shipping_id];
        }
    }

    public static function create_sale_payment($payment, $sale, $amount, $detail) {
        $sale_payment = new \Solunes\Sales\App\SalePayment;
        $sale_payment->parent_id = $sale->id;
        $sale_payment->payment_id = $payment->id;
        $sale_payment->currency_id = $sale->currency_id;
        $sale_payment->exchange = $sale->exchange;
        $sale_payment->amount = $amount;
        $sale_payment->pending_amount = $amount;
        $sale_payment->detail = $detail;
        $sale_payment->save();
        return $sale_payment;
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
          return 'Su usuario no tiene un cliente asociado.';
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
          $customer->city_id = $city->id;
          $customer->city_other = $request->input('city_other');
        }
        if(config('sales.ask_address')){
          $customer->address = $request->input('address');
          $customer->address_extra = $request->input('address_extra');
        }
        if(config('sales.ask_coordinates')){
          $customer->latitude = $request->input('latitude');
          $customer->longitude = $request->input('longitude');
        }
      }
      if(config('sales.ask_invoice')){
        $customer->nit_number = $request->input('nit_number');
        $customer->nit_name = $request->input('nit_social');
      }
      $customer->external_id = $external_id;
      $customer->save();
      if($new_user){
        $user = $customer->user;
        \Auth::loginUsingId($user->id);
      }
      return $user;
    }

}