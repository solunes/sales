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

  /* Ruta para ver todos los productos */
  public function getProducts() {
    $items = \Solunes\Business\App\ProductBridge::whereNull('variation_id')->get();
    $page = \Solunes\Master\App\Page::find(3);
    return view('sales::content.products', ['page'=>$page,'items'=>$items]);
  }

  /* Ruta para producto */
  public function findProduct($slug) {
    $item = \Solunes\Business\App\ProductBridge::whereTranslation('slug', $slug)->first();
    if(!$item){ return redirect('inicio#alert')->with(['message_error'=>'No se encontró la página.']); }
    $products = \Solunes\Business\App\ProductBridge::whereNull('variation_id')->where('active',1)->where('id','!=',$item->id)->limit(4)->orderBy('id','DESC')->get();
    $page = \Solunes\Master\App\Page::find(3);
    return view('sales::content.product', ['page'=>$page,'item'=>$item, 'products'=>$products]);
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
      if(config('sales.custom_add_cart')){
        if(!\CustomFunc::checkCustomAddCart($product, $request)){
          return redirect($this->prev)->with('message_error', 'Hubo un error al agregar su producto.');
        }
      }
      if($request->input('quantity')>0){
        $cart = \Sales::get_cart();
        $detail = NULL;
        $count = 0;
        $custom_price = $product->real_price;
        $variation_id = NULL;
        $variation_value = NULL;
        $variation_2_id = NULL;
        $variation_2_value = NULL;
        $variation_3_id = NULL;
        $variation_3_value = NULL;
        if(config('business.product_variations')){
          $count = 0;
          foreach($product->product_bridge_variation as $product_bridge_variation){
            if($product_bridge_variation->stockable){
              if(!$request->has('variation_'.$product_bridge_variation->id)||$request->input('variation_'.$product_bridge_variation->id)=='0'||$request->input('variation_'.$product_bridge_variation->id)==0){
                return redirect($this->prev)->with('message_error', 'Debe seleccionar todas las opciones requeridas.');
              }
              if($count==0){
                $variation_id = $product_bridge_variation->id;
                $variation_value = $request->input('variation_'.$product_bridge_variation->id);
              } else if($count==1){
                $variation_2_id = $product_bridge_variation->id;
                $variation_2_value = $request->input('variation_'.$product_bridge_variation->id);
              } else if($count==2){
                $variation_3_id = $product_bridge_variation->id;
                $variation_3_value = $request->input('variation_'.$product_bridge_variation->id);
              }
              $count++;
            }
          }
          foreach($product->product_bridge_variation as $product_bridge_variation){
            if(!$product_bridge_variation->stockable){
              if($request->has('variation_'.$product_bridge_variation->id)&&$request->input('variation_'.$product_bridge_variation->id)!='0'&&$request->input('variation_'.$product_bridge_variation->id)!=0){
                $count++;
                if($count>1){
                  $detail .= ' | ';
                }
                $detail .= $product_bridge_variation->name.': ';
                $subarray = $request->input('variation_'.$product_bridge_variation->id);
                if(!is_array($subarray)){
                  $subarray = [$subarray];
                }
                //\Log::info(json_encode($subarray));
                foreach($product->product_bridge_variation_options()->whereIn('variation_option_id', $subarray)->get() as $key => $option){
                  if($key>0){
                    $detail .= ', ';
                  }
                  $detail .= $option->variation_option->name.' ';
                  $custom_price += $option->variation_option->extra_price;
                }
              } else if($product_bridge_variation->optional==0){
                return redirect($this->prev)->with('message_error', 'Debe seleccionar todas las opciones requeridas.');
              }
            }
          }
          $product = \Business::getProductBridgeVariable($product, $variation_id, $variation_value, $variation_2_id, $variation_2_value, $variation_3_id, $variation_3_value);
        }

        $stock_changed = false;
        $quantity = $request->input('quantity');
        if(config('solunes.inventory')){
          $stock = \Business::getProductBridgeStock($product, config('business.online_store_agency_id'));
          if($stock==0){
            return redirect($this->prev)->with('message_error', 'Lo sentimos, no contamos con stock para este producto.');
          } else if($stock<$quantity){
            $quantity = $stock;
            $stock_changed = true;
          }
        }

        if(config('sales.custom_add_cart_detail')){
          $custom_detail = \CustomFunc::checkCustomAddCartDetail($product, $request);
          if($custom_detail){
            $detail .= ' | Detalle: '.$custom_detail;
          }
        } else if($request->has('detail')){
          $detail .= ' | Detalle: '.$request->input('detail');
        }
        \Sales::add_cart_item($cart, $product, $quantity, $detail, $custom_price);
        $message = 'Se añadió su producto al carro de compras. ';
        if($stock_changed){
          $message .= 'Se cambió la cantidad que usted solicitó debido a que no contamos con más stock.';
        }
        return redirect($this->prev)->with('message_success', $message);
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
      if(config('payments.sfv_version')>1||config('payments.discounts')){
        $cart_item->discount_price = $product->discount_price;
      }
      if(config('sales.delivery')){
        $cart_item->weight = $product->weight;
      }
      $cart_item->save();

      return redirect('process/finalizar-compra/'.$cart->id)->with('message_success', 'Ahora puede confirmar los datos de su pedido.');
    } else {
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    }
  }

  /* Ruta GET para revisar el carro de compras */
  public function getCheckCart($type, $cart_id = NULL) {
    if($cart_id){
      $cart = \Solunes\Sales\App\Cart::where('id', $cart_id)->status('holding')->first();
    } else {
      $cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->first();
    }
    if($cart){
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
        $array['city_id'] = config('sales.default_city');
        if('solunes.customer'&&$user->customer){
          $array['address'] = $user->customer->address;
          $array['address_extra'] = $user->customer->address_extra;
          $array['nit_number'] = $user->customer->nit_number;
          $array['nit_social'] = $user->customer->nit_name;
        } else {
          $array['address'] = $user->address;
          $array['address_extra'] = $user->address_extra;
          $array['nit_number'] = $user->nit_number;
          $array['nit_social'] = $user->nit_name;
        }
      } else {
        session()->set('url.intended', url()->current());
        $array['auth'] = false;
        $array['city_id'] = config('sales.default_city');
        $array['address'] = NULL;
        $array['address_extra'] = NULL;
        $array['nit_number'] = NULL;
        $array['nit_social'] = NULL;
      }
      $array['cart'] = $cart;
      $array['cities'] = \Solunes\Business\App\City::get()->lists('name','id')->toArray();
      if(config('sales.delivery')){
        $array['shipping_options'] = \Solunes\Sales\App\Shipping::active()->order()->lists('name','id');
        $array['shipping_descriptions'] = \Solunes\Sales\App\Shipping::active()->order()->get();
      } else {
        $array['shipping_options'] = [];
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
      $discount_amount = 0;
      foreach($cart->cart_items as $item){
        $order_cost += $item->total_price;
        $order_weight += $item->total_weight;
        if(config('payments.sfv_version')>1||config('payments.discounts')){
          $discount_amount += $item->discount_price;
        }
        if(config('solunes.inventory')){
          $stock = \Business::getProductBridgeStock($item->product_bridge, config('business.online_store_agency_id'));
          if($stock<$item->quantity){
            $item->quantity = $stock;
            $item->save();
          }
        }
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
      if(config('solunes.customer')){
        $customer = \Sales::customerRegistration($request);
        $user = $customer->user;
      } else {
        $customer = NULL;
        $user = \Sales::userRegistration($request);
      }
      if(is_string($user)){
        return redirect($this->prev)->with('message_error', 'Hubo un error al finalizar su registro: '.$user);
      }
      
      // Sale
      $total_cost = $order_cost + $shipping_cost;
      $agency = \Solunes\Business\App\Agency::find(1); // Parametrizar tienda en config
      $currency = \Solunes\Business\App\Currency::find(1); // Parametrizar tienda en config
      $sale = new \Solunes\Sales\App\Sale;
      $sale->user_id = $user->id;
      if($customer){
        $sale->customer_id = $customer->id;
      }
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
      $sale_payment->payment_method_id = $request->input('payment_method_id');
      $sale_payment->currency_id = $currency->id;
      $sale_payment->exchange = $currency->main_exchange;
      $sale_payment->amount = $total_cost;
      if(config('payments.sfv_version')>1||config('payments.discounts')){
        $sale_payment->discount_amount = $discount_amount;
      }
      if(config('sales.delivery')){
        $sale_payment->pay_delivery = 1;
      }
      $sale_payment->pending_amount = $total_cost;
      $sale_payment->detail = 'Pago por compra online: #'.$sale_payment->id;
      $sale_payment->save();

      // Sale Delivery
      if(config('sales.delivery')){
        $shipping = \Solunes\Sales\App\Shipping::find($request->input('shipping_id'));
        if($shipping){
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
          if($shipping_city = $shipping->shipping_cities()->where('city_id', $sale_delivery->city_id)->first()){
            $delivery_time = $shipping_city->shipping_days;
          } else {
            $delivery_time = 1;
          }
          if($delivery_time==1){
            $sale_delivery->delivery_time = $delivery_time.' día';
          } else {
            $sale_delivery->delivery_time = $delivery_time.' días';
          }
          $sale_delivery->save();
        }
      }

      // Sale Items
      $store_agency = \Solunes\Business\App\Agency::find(config('business.online_store_agency_id'));
      foreach($cart->cart_items as $cart_item){
        $product_bridge = $cart_item->product_bridge;
        $sale_item = new \Solunes\Sales\App\SaleItem;
        $sale_item->parent_id = $sale->id;
        $sale_item->product_bridge_id = $cart_item->product_bridge_id;
        //$sale_item->name = $cart_item->product_bridge->name;
        $sale_item->currency_id = $currency->id;
        $sale_item->detail = $cart_item->detail;
        $sale_item->price = $cart_item->price;
        $sale_item->quantity = $cart_item->quantity;
        if(config('payments.sfv_version')>1){
          $sale_item->economic_sin_activity = $product_bridge->economic_sin_activity;
          $sale_item->product_sin_code = $product_bridge->product_sin_code;
          $sale_item->product_internal_code = $product_bridge->product_internal_code;
          $sale_item->product_serial_number = $product_bridge->product_serial_number;
        }
        if(config('payments.sfv_version')>1||config('payments.discounts')){
          $sale_item->discount_price = $product_bridge->discount_price;
          $sale_item->discount_amount = round($product_bridge->discount_price * $cart_item->quantity);
        }
        //$sale_item->weight = $cart_item->weight;
        $sale_item->save();
        \Inventory::reduce_inventory($store_agency, $sale_item->product_bridge, $sale_item->quantity);
      }

      $cart->status = 'sale';
      $cart->user_id = $user->id;
      $cart->save();

      // Send Email
      $vars = ['@name@'=>$user->name, '@total_cost@'=>$sale->total_cost, '@sale_link@'=>url('process/sale/'.$sale->id)];
      \FuncNode::make_email('new-sale', [$user->email], $vars);

      $redirect = 'process/sale/'.$sale->id;

      // Revisar redirección a método de pago antes.
      if(config('sales.redirect_to_payment')){
        $model = '\\'.$sale_payment->payment_method->model;
        return \Payments::generateSalePayment($sale, $model, $redirect);
      }

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

  /* Ruta POST para editar una venta pendiente */
  public function postSaleUpdateNit(Request $request) {
    $sale_id = $request->input('sale_id');
    $save_for_all = $request->input('save_for_all');
    if($request->has('invoice_name')&&$request->has('invoice_nit')&&$sale = \Solunes\Sales\App\Sale::findId($sale_id)->checkOwner()->first()){
      $sale->invoice_name = $request->input('invoice_name');
      $sale->invoice_nit = $request->input('invoice_nit');
      $sale->save();
      $user = auth()->user();
      $customer = $user->customer;
      if($customer&&$save_for_all=='1'){
        $customer->nit_name = $request->input('invoice_name');
        $customer->nit_number = $request->input('invoice_nit');
        $customer->save();
      }
      return redirect($this->prev)->with('message_success', 'Sus datos fueron actualizados correctamente.');
    } else {
      return redirect($this->prev)->with('message_error', 'Debe llenar sus datos de facturación.');
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

  public function getMySalesHistory($token) {
    $user = auth()->user();
    $array['page'] = \Solunes\Master\App\Page::find(1);
    $array['user'] = $user;
    $array['items'] = $user->successful_sales;
    return view('sales::process.sales-history', $array);
  }

  public function getMyPendingCarts($token) {
    $user = auth()->user();
    $array['page'] = \Solunes\Master\App\Page::find(1);
    $array['user'] = $user;
    $array['items'] = $user->pending_carts;
    $array['sales'] = $user->holding_sales;
    return view('sales::process.pending-carts', $array);
  }

  /* Ruta GET probar el email de una venta */
  public function getTestSuccessSale($sale_id) {
    if(config('services.enable_test')){
      $sale = \Solunes\Sales\App\Sale::find($sale_id);
      $customer['email'] = 'edumejia30@gmail.com';
      $customer['name'] = 'Eduardo Mejia';
      \Sales::sendConfirmationSaleEmail($sale, $customer);
    } else {
      return redirect('')->with('message_error', 'La prueba no pudo ser realizada.');
    }
  }

}