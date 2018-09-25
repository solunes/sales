<?php

namespace Solunes\Sales\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use Validator;
use Asset;
use AdminList;
use AdminItem;
use PDF;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProcessController extends Controller {

  protected $request;
  protected $url;

  public function __construct(UrlGenerator $url) {
    $this->prev = $url->previous();
  }

  /* Ruta para calcular precio de envio en AJAX segun envio, ciudad y peso */
  public function getCalculateShipping($shipping_id, $city_id, $weight) {
    $shipping_array = \Sales::calculate_shipping_cost($shipping_id, $city_id, $weight);
    return $shipping_array;
  }

  /* Ruta GET para añadir un item de carro de compras. Cantidad: 1 */
  public function getAddCartItem($product_id) {
    if($product = \Solunes\Business\App\ProductBridge::find($product_id)){
      $cart = \Sales::get_cart();
      \Sales::add_cart_item($cart, $product, 1);
      return redirect($this->prev)->with('message_success', 'Se añadió su producto al carro de compras.');
    } else {
      return redirect($this->prev)->with('message_error', 'Debe seleccionar un producto existente.');
    }
  }

  /* Ruta POST para añadir un carro de compras. Cantidad: Definida en input */
  public function postAddCartItem(Request $request) {
    if($product = \Solunes\Business\App\ProductBridge::find($request->input('product_id'))){
      if($request->input('quantity')>0){
        $cart = \Sales::get_cart();
        $detail = NULL;
        $count = 0;
        $custom_price = $product->real_price;
        foreach($product->product_bridge_variation as $product_bridge_variation){
          if($request->has('variation_'.$product_bridge_variation->id)){
            $count++;
            if($count>1){
              $detail .= ' | ';
            }
            $detail .= $product_bridge_variation->name.': ';
            $subarray = $request->input('variation_'.$product_bridge_variation->id);
            if(!is_array($subarray)){
              $subarray = [$subarray];
            }
            foreach($product_bridge_variation->variation_options()->whereIn('id', $subarray)->get() as $key => $option){
              if($key>0){
                $detail .= ', ';
              }
              $detail .= $option->name.' ';
              $custom_price += $option->extra_price;
            }
          }
        }
        if($request->has('detail')){
          $detail .= ' | Detalle: '.$request->input('detail');
        }
        \Sales::add_cart_item($cart, $product, $request->input('quantity'), $detail, $custom_price);
        return redirect($this->prev)->with('message_success', 'Se añadió su producto al carro de compras.');
      } else {
        return redirect($this->prev)->with('message_error', 'Debe seleccionar una cantidad positiva.');
      }
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al agregar el producto, intente nuevamente.');
    }
  }

  /* Ruta GET para borrar un item de carro de compras */
  public function getDeleteCartItem($cart_item_id) {
    if($cart_item = \Solunes\Sales\App\CartItem::find($cart_item_id)){
      $cart_item->delete();
    }
    return redirect($this->prev);
  }

  /* Ruta GET para hacer el comprar ahora de un producto */
  public function getBuyNow($slug) {
    if($item_trans = \Solunes\Business\App\ProductBridgeTranslation::findBySlug($slug)){
      $item = $item_trans->product_bridge;
      $page = \Solunes\Master\App\Page::find(2);
      $view = 'process.comprar-ahora';
      if(!view()->exists($view)){
        $view = 'sales::'.$view;
      }
      return view($view, ['product'=>$item, 'page'=>$page]);
    } else {
      return redirect('')->with('message_error', 'No se encuentra el producto para ser comprado.');
    }
  }

  /* Ruta POST para comprar ahora un producto */
  public function postBuyNow(Request $request) {
    $validator = \Validator::make($request->all(), \Solunes\Sales\App\Cart::$rules_send);
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else if($request->input('quantity')>0&&$product = \Solunes\Business\App\ProductBridge::find($request->input('product_id'))){
      $cart = new \Solunes\Sales\App\Cart;
      if(\Auth::check()){
        $cart->user_id = \Auth::user()->id;
      }
      $cart->session_id = \Session::getId();
      $cart->type = 'buy-now';
      $cart->save();

      $cart_item = new \Solunes\Sales\App\CartItem;
      $cart_item->parent_id = $cart->id;
      $cart_item->product_bridge_id = $product->id;
      $cart_item->quantity = $request->input('quantity');
      $cart_item->price = $product->real_price;
      $cart_item->weight = $product->weight;
      $cart_item->save();

      return redirect('process/finalizar-compra/'.$cart->id)->with('message_success', 'Ahora puede confirmar los datos de su pedido.');
    } else {
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    }
  }

  /* Ruta GET para revisar el carro de compras */
  public function getCheckCart($type) {
    if($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->first()){
      $page = \Solunes\Master\App\Page::find(2);
      $view = 'process.confirmar-compra';
      if(!view()->exists($view)){
        $view = 'sales::'.$view;
      }
      $total = 0;
      foreach($cart->cart_items as $cart_item){
        $total += $cart_item->total_price;
      }
      return view($view, ['cart'=>$cart, 'page'=>$page, 'total'=>$total]);
    } else {
      return redirect('')->with('message_error', 'No se encontró un carro de compras abierto en su sesión.');
    }
  }

  /* Ruta POST para editar el carro de compras */
  public function postUpdateCart(Request $request) {
    if($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->first()){
      $cart->touch();
      foreach($cart->cart_items as $item){
        if(isset($request->input('product_id')[$item->id])&&$request->input('quantity')[$item->id]>0){
          $item->quantity = $request->input('quantity')[$item->id];
          $item->save();
        } else {
          $item->delete();
        }
      }
      return redirect($this->prev)->with('message_success', 'Se actualizó su carro de compras correctamente.');
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al actualizar su carro de compras.');
    }
  }

  /* Ruta GET para finalizar la compra */
  public function getFinishSale($cart_id = NULL) {
    if(($cart_id&&$cart = \Solunes\Sales\App\Cart::findId($cart_id)->checkBuyNow()->checkOwner()->status('holding')->first())||($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->first())){
      if(\Auth::check()){
        $user = \Auth::user();
        $array['auth'] = true;
        $array['city_id'] = 1;
        $array['address'] = $user->address;
        $array['address_extra'] = $user->address_extra;
      } else {
        session()->set('url.intended', url()->current());
        $array['auth'] = false;
        $array['city_id'] = 1;
        $array['address'] = NULL;
        $array['address_extra'] = NULL;
      }
      $array['cart'] = $cart;
      $array['cities'] = \Solunes\Business\App\City::get()->lists('name','id')->toArray();
      if(config('sales.delivery')){
        $array['shipping_options'] = \Solunes\Sales\App\Shipping::active()->order()->lists('name','id');
        $array['shipping_descriptions'] = \Solunes\Sales\App\Shipping::active()->order()->get();
      }
      $array['payment_options'] = \Solunes\Payments\App\PaymentMethod::active()->order()->lists('name','id');
      $array['payment_descriptions'] = \Solunes\Payments\App\PaymentMethod::active()->order()->get();
      $array['page'] = \Solunes\Master\App\Page::find(2);
      $total = 0;
      $weight = 0;
      foreach($cart->cart_items as $cart_item){
        $total += $cart_item->total_price;
        $weight += $cart_item->total_weight;
      }
      $array['total'] = $total;
      $array['weight'] = $weight;
      $view = 'process.finalizar-compra';
      if(!view()->exists($view)){
        $view = 'sales::'.$view;
      }
      return view($view, $array);
    } else {
      return redirect('')->with('message_error', 'No se encuentra el producto para ser comprado.');
    }
  }

  /* Ruta POST para confirmar su compra */
  public function postFinishSale(Request $request) {
    $cart_id = $request->input('cart_id');
    if(auth()->check()){
      $rules = \Solunes\Sales\App\Sale::$rules_auth_send;
    } else {
      $rules = \Solunes\Sales\App\Sale::$rules_send;
    }
    if(config('sales.delivery')){
      if(!config('sales.delivery_city')){
        unset($rules['city_id']);
      }
      if(!config('sales.ask_address')){
        unset($rules['address']);
      }
      if(!config('sales.sales_email')){
        unset($rules['email']);
      }
      if(!config('sales.sales_cellphone')){
        unset($rules['cellphone']);
      }
      if(!config('sales.sales_username')){
        unset($rules['username']);
      }
      unset($rules['shipping_id']);
    }
    if(!config('sales.ask_invoice')){
      unset($rules['nit_number']);
      unset($rules['nit_social']);
    }
    $validator = \Validator::make($request->all(), $rules);
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else if($cart_id&&$cart = \Solunes\Sales\App\Cart::findId($cart_id)->checkOwner()->status('holding')->first()){
      $new_user = false;

      $order_cost = 0;
      $order_weight = 0;
      foreach($cart->cart_items as $item){
        $order_cost += $item->total_price;
        $order_weight += $item->total_weight;
      }
      if(config('sales.delivery')){
        $shipping_array = \Sales::calculate_shipping_cost($request->input('shipping_id'), $request->input('city_id'), $order_weight);
        if($shipping_array['shipping']===false){
          return redirect($this->prev)->with('message_error', 'No se encontró el método de envío para esta ciudad, seleccione otro.')->withInput();
        }
        $shipping_cost = $shipping_array['shipping_cost'];
      } else {
        $shipping_cost = 0;
      }

      // User
      $user = \Sales::userRegistration($request);
      if(is_string($user)){
        return redirect($this->prev)->with('message_error', 'Hubo un error al finalizar su registro: '.$user);
      }
      
      // Sale
      $total_cost = $order_cost + $shipping_cost;
      $agency = \Solunes\Business\App\Agency::find(1); // Parametrizar tienda en config
      $currency = \Solunes\Business\App\Currency::find(1); // Parametrizar tienda en config
      $sale = new \Solunes\Sales\App\Sale;
      $sale->user_id = $user->id;
      $sale->agency_id = $agency->id;
      $sale->currency_id = $currency->id;
      //$sale->order_amount = $order_cost;
      $sale->amount = $total_cost;
      if(config('sales.ask_invoice')){
        $sale->invoice = true;
        $sale->invoice_nit = $request->input('nit_number');
        $sale->invoice_name = $request->input('nit_social');
      } else {
        $sale->invoice = false;
      }
      //$sale->type = 'online';
      $sale->save();
      $sale->name = 'Venta Online: #'.$sale->id;
      $sale->save();

      // Sale Payment
      $sale_payment = new \Solunes\Sales\App\SalePayment;
      $sale_payment->parent_id = $sale->id;
      $sale_payment->payment_id = $request->input('payment_id');
      $sale_payment->currency_id = $currency->id;
      $sale_payment->exchange = $currency->main_exchange;
      $sale_payment->amount = $total_cost;
      $sale_payment->pending_amount = $total_cost;
      $sale_payment->detail = 'Pago por compra online: #'.$sale_payment->id;
      $sale_payment->save();

      // Sale Delivery
      if(config('sales.delivery')){
        $sale_delivery = new \Solunes\Sales\App\SaleDelivery;
        $sale_delivery->parent_id = $sale->id;
        $sale_delivery->shipping_id = $request->input('shipping_id');
        $sale_delivery->currency_id = $sale->currency_id;
        if(config('sales.delivery_city')){
          $sale_delivery->region_id = $user->city->region_id;
          $sale_delivery->city_id = $user->city->id;
          if($request->has('city_other')){
            $sale_delivery->city_other = $request->input('city_other');
          }
          if($request->has('region_other')){
            $sale_delivery->region_other = $request->input('region_other');
          }
        } else {
          $sale_delivery->region_id = 1;
          $sale_delivery->city_id = 1;
        }
        $sale_delivery->name = 'Pedido de venta en linea';
        $sale_delivery->address = $request->input('address');
        $sale_delivery->address_extra = $request->input('address_extra');
        $sale_delivery->postal_code = 'LP01';
        $sale_delivery->phone = $user->cellphone;
        $sale_delivery->total_weight = $order_weight;
        $sale_delivery->shipping_cost = $shipping_cost;
        $sale_delivery->save();
      }

      // Sale Items
      foreach($cart->cart_items as $cart_item){
        $sale_item = new \Solunes\Sales\App\SaleItem;
        $sale_item->parent_id = $sale->id;
        $sale_item->product_bridge_id = $cart_item->product_bridge_id;
        //$sale_item->name = $cart_item->product_bridge->name;
        $sale_item->currency_id = $currency->id;
        $sale_item->detail = $cart_item->detail;
        $sale_item->price = $cart_item->price;
        $sale_item->quantity = $cart_item->quantity;
        //$sale_item->weight = $cart_item->weight;
        $sale_item->save();
      }

      $cart->status = 'sale';
      $cart->user_id = $user->id;
      $cart->save();

      // Send Email
      $vars = ['@name@'=>$user->name, '@total_cost@'=>$sale->total_cost, '@sale_link@'=>url('process/sale/'.$sale->id)];
      \FuncNode::make_email('new-sale', [$user->email], $vars);

      $redirect = 'process/sale/'.$sale->id;

      // Revisar redirección a método de pago antes.
      /*if(config('solunes.payments')){
        $model = '\\'.$sale_payment->payment->model;
        return \Payments::generateSalePayment($sale, $model, $redirect);
      }*/

      return redirect($redirect)->with('message_success', 'Su compra fue confirmada correctamente, ahora debe proceder al pago para finalizarla.');
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al actualizar su carro de compras.');
    }
  }

  /* Ruta GET para revisar venta pendiente */
  public function getSale($sale_id) {
    if($sale = \Solunes\Sales\App\Sale::findId($sale_id)->checkOwner()->with('cart','cart.cart_items')->first()){
      $array['page'] = \Solunes\Master\App\Page::find(2);
      $array['sale'] = $sale;
      $array['sale_payments'] = $sale->sale_payments;
      $view = 'process.sale';
      if(!view()->exists($view)){
        $view = 'sales::'.$view;
      }
      return view($view, $array);
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al encontrar su compra.');
    }
  }

  /* Ruta POST para deposito bancario */
  public function postSpBankDeposit(Request $request) {
    $sale_id = $request->input('sale_id');
    $validator = \Validator::make($request->all(), \Solunes\Sales\App\SpBankDeposit::$rules_send);
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else if($sale_id&&$sale = \Solunes\Sales\App\Sale::findId($sale_id)->checkOwner()->status('holding')->first()){
      if(count($sale->payment_receipts)>0){
        $payment_receipt = $sale->payment_receipts->first();
      } else {
        $payment_receipt = new \Solunes\Sales\App\SpBankDeposit;
        $payment_receipt->sale_id = $sale->id;
        $payment_receipt->sale_payment_id = $sale->sale_payments()->first()->id;
        $payment_receipt->status = 'holding';
      }
      $payment_receipt->image = \Asset::upload_image($request->file('image'), 'sp-bank-deposit-image');
      $payment_receipt->save();

      return redirect($this->prev)->with('message_success', 'Su pago fue recibido, sin embargo aún debe ser confirmado por nuestros administradores.');
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al encontrar su compra.');
    }
  }

}