<?php 

namespace Solunes\Sales\App\Helpers;

class Sales {
    public static function get_cart() {
        if($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->with('cart_items','cart_items.product')->first()){
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

    public static function add_cart_item($cart, $product, $quantity) {
        if($cart_item = $cart->cart_items->where('product_id', $product->id)->first()){
          $cart_item->quantity = $cart_item->quantity + $quantity;
        } else {
          $cart_item = new \Solunes\Sales\App\CartItem;
          $cart_item->parent_id = $cart->id;
          $cart_item->product_id = $product->id;
          $cart_item->quantity = $quantity;
        }
        $cart_item->price = $product->real_price;
        $cart_item->weight = $product->weight;
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

}