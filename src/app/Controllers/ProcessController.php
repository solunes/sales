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
    if(config('product.product_variations')){
      $items = \Solunes\Business\App\ProductBridge::whereNull('variation_id')->where('active',1)->get();
    } else {
      $items = \Solunes\Business\App\ProductBridge::where('active',1)->get();
    }
    $page = \Solunes\Master\App\Page::find(3);
    return view('sales::content.products', ['page'=>$page,'items'=>$items]);
  }

  /* Ruta para producto */
  public function findProduct($slug) {
    $item = \Solunes\Business\App\ProductBridge::whereTranslation('slug', $slug)->where('active', 1)->first();
    if(!$item){ return redirect('inicio#alert')->with(['message_error'=>'No se encontró la página.']); }
    if($item->product_type=='product'&&config('solunes.product')){
      $real_item = \Solunes\Product\App\Product::find($item->product_id);
      if(!$real_item){ return redirect('inicio#alert')->with(['message_error'=>'No se encontró el producto específico.']); }
    }
    if(config('product.product_variations')){
      $products = \Solunes\Business\App\ProductBridge::whereNull('variation_id')->where('active',1)->where('id','!=',$item->id)->limit(4)->orderBy('id','DESC')->get();
    } else {
      $products = \Solunes\Business\App\ProductBridge::where('active',1)->where('id','!=',$item->id)->limit(4)->orderBy('id','DESC')->get();
    }
    $page = \Solunes\Master\App\Page::find(3);
    return view('sales::content.product', ['page'=>$page,'item'=>$item, 'products'=>$products]);
  }

  /* Ruta para calcular precio de envio en AJAX segun envio, ciudad y peso */
  public function getCalculateShipping($shipping_id, $country_id, $city_id, $weight, $map_coordinates, $agency_id = NULL) {
    $shipping_array = \Sales::calculate_shipping_cost($shipping_id, $country_id, $city_id, $weight, $map_coordinates, $agency_id);
    return $shipping_array;
  }

  /* Ruta GET para añadir un item de carro de compras. Cantidad: 1 */
  public function getAddCartItem($product_id, $agency_id = NULL) {
    \Artisan::call('fix-sales-status');
    if($product = \Solunes\Business\App\ProductBridge::find($product_id)){
      $cart = \Sales::get_cart($agency_id);
      \Sales::add_cart_item($cart, $product, 1);
      return redirect($this->prev)->with('message_success', 'Se añadió su producto al carro de compras.');
    } else {
      return redirect($this->prev)->with('message_error', 'Debe seleccionar un producto existente.');
    }
  }

  /* Ruta POST para añadir un carro de compras. Cantidad: Definida en input */
  public function postAddCartItem(Request $request) {
    \Artisan::call('fix-sales-status');
    if($product = \Solunes\Business\App\ProductBridge::find($request->input('product_id'))){
      if(config('sales.custom_add_cart')){
        if(!\CustomFunc::checkCustomAddCart($product, $request)){
          return redirect($this->prev)->with('message_error', 'Hubo un error al agregar su producto.');
        }
      }
      $agency_id = NULL;
      if($request->input('quantity')>0){
        if($request->has('agency_id')){
          $agency_id = $request->input('agency_id');
        } else {
          $agency_id = config('business.online_store_agency_id');
        }
        $cart = \Sales::get_cart($agency_id);
        $detail = $product->name;
        $count = 0;
        $agency = \Solunes\Business\App\Agency::find($agency_id);
        $custom_price = \Business::getProductPrice($product, $request->input('quantity'));
        $product_bridge_variation_array = [];
        if(config('business.product_variations')){
          $count = 0;
          foreach($product->product_bridge_variation as $product_bridge_variation){
            if($product_bridge_variation->stockable){
              if(!$request->has('variation_'.$product_bridge_variation->id)||$request->input('variation_'.$product_bridge_variation->id)=='0'||$request->input('variation_'.$product_bridge_variation->id)==0){
                return redirect($this->prev)->with('message_error', 'Debe seleccionar todas las opciones requeridas.');
              }
              $variation_value = $request->input('variation_'.$product_bridge_variation->id);
              $detail .= ' - '.$product_bridge_variation->name.': '.$product->product_bridge_variation_option()->where('variation_option_id', $variation_value)->first()->name;
              $product_bridge_variation_array[$product_bridge_variation->id] = $request->input('variation_'.$product_bridge_variation->id);
              $count++;
            }
          }
          foreach($product->product_bridge_variation as $product_bridge_variation){
            if(!$product_bridge_variation->stockable){
              if($request->has('variation_'.$product_bridge_variation->id)&&$request->input('variation_'.$product_bridge_variation->id)!='0'&&$request->input('variation_'.$product_bridge_variation->id)!=0){
                $count++;
                $detail .= ' | ';
                $detail .= $product_bridge_variation->name.': ';
                $subarray = $request->input('variation_'.$product_bridge_variation->id);
                if(!is_array($subarray)){
                  $subarray = [$subarray];
                }
                //\Log::info(json_encode($subarray));
                foreach($product->product_bridge_variation_option()->whereIn('variation_option_id', $subarray)->get() as $key => $option){
                  if($key>0){
                    $detail .= ', ';
                  }
                  $detail .= $option->name.' ';
                  if(config('sales.custom_add_cart_extra_price')){
                    $custom_price += \CustomFunc::checkCustomAddCartExtraPrice($product, $option, $option->extra_price, $request);
                  } else {
                    $custom_price += $option->extra_price;
                  }
                }
              } else if($product_bridge_variation->optional==0){
                return redirect($this->prev)->with('message_error', 'Debe seleccionar todas las opciones requeridas.');
              }
            }
          }
          $product = \Business::getProductBridgeVariable($product, $product_bridge_variation_array);
        }

        $stock_changed = false;
        $quantity = $request->input('quantity');
        if(config('solunes.inventory')){
          if(config('sales.check_cart_stock')&&$agency->stockable){
            $stock = \Business::getProductBridgeStock($product, $agency_id);
            if($stock==0){
              return redirect($this->prev)->with('message_error', 'Lo sentimos, no contamos con stock para este producto.');
            } else if($stock<$quantity){
              $quantity = $stock;
              $stock_changed = true;
            }
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
        if(config('sales.custom_add_cart_fix')){
          $cart = \CustomFunc::checkCustomAddCartFix($cart, $product, $request);
        }
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
      if(config('sales.sales_agency')&&$request->has('agency_id')){
        $cart->agency_id = $request->input('agency_id');
      }
      $cart->session_id = \Session::getId();
      $cart->type = 'buy-now';
      $cart->save();

      $cart_item = new \Solunes\Sales\App\CartItem;
      $cart_item->parent_id = $cart->id;
      $cart_item->product_bridge_id = $product->id;
      $cart_item->currency_id = $product->currency_id;
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
      $cart = \Solunes\Sales\App\Cart::where('id', $cart_id)->status('holding')->orderBy('updated_at','DESC')->first();
    } else {
      $cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->orderBy('updated_at','DESC')->first();
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
    if($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->orderBy('updated_at','DESC')->first()){
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
  public function getFinishSale($cart_id = NULL, $quotation = false) {
    if(($cart_id&&$cart = \Solunes\Sales\App\Cart::findId($cart_id)->checkOwner()->status('holding')->orderBy('updated_at','DESC')->first())||($cart = \Solunes\Sales\App\Cart::checkOwner()->checkCart()->status('holding')->orderBy('updated_at','DESC')->first())){
      $array['country_id'] = config('sales.default_country');
      $array['city_id'] = config('sales.default_city');
      $array['city_other'] = NULL;
      if(config('business.agency_shippings')&&$cart->agency_id){
        $array['agency'] = $cart->agency;
      } else {
        $array['agency'] = \Solunes\Business\App\Agency::first();
      }
      if(\Auth::check()){
        $user = \Auth::user();
        $array['auth'] = true;
        if('solunes.customer'&&$user->customer){
          if(config('solunes.addresses')){
            if($user->customer->main_customer_address->country_id){
              $array['country_id'] = $user->customer->main_customer_address->country_id;
            }
            if($user->customer->main_customer_address->city_id){
              $array['city_id'] = $user->customer->main_customer_address->city_id;
            }
            $array['city_other'] = $user->customer->main_customer_address->city_other;
            $array['address'] = $user->customer->main_customer_address->address;
            $array['address_extra'] = $user->customer->main_customer_address->address_extra;
          } else {
            if($user->customer->country_id){
              $array['country_id'] = $user->customer->country_id;
            }
            if($user->customer->city_id){
              $array['city_id'] = $user->customer->city_id;
            }
            $array['city_other'] = $user->customer->city_other;
            $array['address'] = $user->customer->address;
            $array['address_extra'] = $user->customer->address_extra;
          }
          $array['nit_number'] = $user->customer->nit_number;
          $array['nit_social'] = $user->customer->nit_name;
          if(config('sales.ask_coordinates')&&!$quotation&&$user->customer->latitude&&$user->customer->longitude){
            $array['map_coordinates'] = ['type'=>'customer', 'latitude'=>$user->customer->latitude, 'longitude'=>$user->customer->longitude];
          } else {
            $array['map_coordinates'] = ['type'=>'none', 'latitude'=>NULL, 'longitude'=>NULL];
          }
        } else {
          if($user->country_id){
            $array['country_id'] = $user->country_id;
          }
          if($user->city_id){
            $array['city_id'] = $user->city_id;
          }
          $array['city_other'] = $user->city_other;
          $array['address'] = $user->address;
          $array['address_extra'] = $user->address_extra;
          $array['nit_number'] = $user->nit_number;
          $array['nit_social'] = $user->nit_name;
          if(config('sales.ask_coordinates')&&!$quotation&&$user->latitude&&$user->longitude){
            $array['map_coordinates'] = ['type'=>'user', 'latitude'=>$user->latitude, 'longitude'=>$user->longitude];
          } else {
            $array['map_coordinates'] = ['type'=>'none', 'latitude'=>NULL, 'longitude'=>NULL];
          }
        }
      } else {
        session()->set('url.intended', url()->current());
        $array['auth'] = false;
        $array['address'] = NULL;
        $array['address_extra'] = NULL;
        $array['nit_number'] = NULL;
        $array['nit_social'] = NULL;
        if(config('sales.ask_coordinates')&&!$quotation){
          $array['map_coordinates'] = ['type'=>'none', 'latitude'=>NULL, 'longitude'=>NULL];
        }
      }
      if(config('sales.delivery_country')){
        $array['countries'] = \Solunes\Business\App\Country::get()->lists('name','id')->toArray();
        $region_ids = \Solunes\Business\App\Region::where('country_id', $array['country_id'])->lists('id')->toArray();
        $array['cities'] = \Solunes\Business\App\City::whereIn('region_id', $region_ids)->get()->lists('name','id')->toArray();
      } else {
        $array['cities'] = \Solunes\Business\App\City::get()->lists('name','id')->toArray();
      }
      $array['cart'] = $cart;
      $array['shipping_dates'] = [];
      $array['shipping_times'] = [];
      if(config('sales.delivery')){
        if(config('business.agency_shippings')&&$cart->agency_id){
          $array['shipping_options'] = \Solunes\Sales\App\Shipping::whereHas('agency_shipping', function($q) use($cart) {
            $q->where('agency_id', $cart->agency_id);
          })->active()->order()->lists('name','id');
          $array['shipping_descriptions'] = \Solunes\Sales\App\Shipping::whereHas('agency_shipping', function($q) use($cart) {
            $q->where('agency_id', $cart->agency_id);
          })->active()->order()->get();
          $first_shipping = \Solunes\Sales\App\Shipping::whereHas('agency_shipping', function($q) use($cart) {
            $q->where('agency_id', $cart->agency_id);
          })->active()->order()->first();
        } else {
          $array['shipping_options'] = \Solunes\Sales\App\Shipping::active()->order()->lists('name','id');
          $array['shipping_descriptions'] = \Solunes\Sales\App\Shipping::active()->order()->get();
          $first_shipping = \Solunes\Sales\App\Shipping::active()->order()->first();
        }
        if(config('sales.delivery_select_day')&&$first_shipping){
          $first_shipping_city = $first_shipping->shipping_city()->where('city_id', $array['city_id'])->first();
          if(!$first_shipping_city){
            $first_shipping_city = $first_shipping->shipping_city;
          }
          $array['shipping_dates'] = \Sales::getShippingDates($first_shipping, $first_shipping_city->shipping_days);
        }
        if(config('sales.delivery_select_hour')&&$first_shipping){
          $array['shipping_times'] = $first_shipping->shipping_times()->lists('name','id')->toArray();
        }
      } else {
        $array['shipping_options'] = [];
        $array['shipping_descriptions'] = [];
      }
      if(config('business.agency_payment_methods')&&$cart->agency_id){
        $array['payment_options'] = \Solunes\Payments\App\PaymentMethod::whereHas('agency_payment_method', function($q) use($cart) {
          $q->where('agency_id', $cart->agency_id);
        })->active()->order()->lists('name','id');
        $array['payment_descriptions'] = \Solunes\Payments\App\PaymentMethod::whereHas('agency_payment_method', function($q) use($cart) {
          $q->where('agency_id', $cart->agency_id);
        })->active()->order()->get();
      } else {
        $array['payment_options'] = \Solunes\Payments\App\PaymentMethod::active()->order()->lists('name','id');
        $array['payment_descriptions'] = \Solunes\Payments\App\PaymentMethod::active()->order()->get();
      }
      $array['page'] = \Solunes\Master\App\Page::find(2);
      $total = 0;
      $weight = 0;
      foreach($cart->cart_items as $cart_item){
        $total += $cart_item->total_price;
        $weight += $cart_item->total_weight;
      }
      $array['total'] = $total;
      $array['weight'] = $weight;
      if($quotation){
        $array['quotation'] = $quotation;
      } else {
        $array['quotation'] = false;
      }
      if(config('sales.ask_coordinates')&&!$quotation&&$array['map_coordinates']['type']=='none'){
        $coordinates = config('solunes.default_location');
        $coordinates = explode(';',$coordinates);
        $array['map_coordinates'] = ['type'=>'default', 'latitude'=>$coordinates[0], 'longitude'=>$coordinates[1]];
      }
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
    \Artisan::call('fix-sales-status');
    $cart_id = $request->input('cart_id');
    if(auth()->check()){
      $rules = \Solunes\Sales\App\Sale::$rules_auth_send;
    } else {
      $rules = \Solunes\Sales\App\Sale::$rules_send;
    }
    if(!config('sales.delivery')){
      unset($rules['shipping_id']);
    }
    if(!config('sales.delivery_city')){
      unset($rules['city_id']);
    }
    if(!config('sales.ask_address')||$request->has('quotation')){
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
    if(!config('sales.ask_invoice')||$request->has('quotation')){
      unset($rules['nit_number']);
      unset($rules['nit_social']);
    }
    $validator = \Validator::make($request->all(), $rules);
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else if($cart_id&&$cart = \Solunes\Sales\App\Cart::findId($cart_id)->checkOwner()->status('holding')->first()){
      $new_user = false;
      if($request->has('quotation')&&$request->input('quotation')!='false'&&$request->input('quotation')!=false){
        $quotation = $request->input('quotation');
      } else {
        $quotation = false;
      }

      if(config('sales.sales_agency')){
        if($cart->agency_id){
          $agency = $cart->agency;
        } else {
          $agency = \Solunes\Business\App\Agency::find(config('business.online_store_agency_id')); // Parametrizar tienda en config
        }
      }
      $order_cost = 0;
      $order_weight = 0;
      $discount_amount = 0;
      foreach($cart->cart_items as $item){
        $order_cost += $item->total_price;
        $order_weight += $item->total_weight;
        if(config('payments.sfv_version')>1||config('payments.discounts')){
          $discount_amount += $item->discount_price;
        }
        if(config('solunes.inventory')&&$agency->stockable){
          $stock = \Business::getProductBridgeStockItem($item->product_bridge, $agency->id);
          if($stock){
            if(is_integer($stock)){
              if($stock<$item->quantity){
                //$stock->quantity = $stock->quantity - $item->quantity;
                return redirect($this->prev)->with('message_error', 'El item "'.$item->product_bridge->name.'" no cuenta con stock suficiente. Actualmente tiene "'.$stock.'" unidades disponibles.')->withInput();
              } else if(!$stock) {
                //$stock->quantity = 0;
                return redirect($this->prev)->with('message_error', 'El item "'.$item->product_bridge->name.'" no cuenta con stock.')->withInput();
              }
            } else {
              if($stock->quantity<$item->quantity){
                //$stock->quantity = $stock->quantity - $item->quantity;
                return redirect($this->prev)->with('message_error', 'El item "'.$item->product_bridge->name.'" no cuenta con stock suficiente. Actualmente tiene "'.$stock->quantity.'" unidades disponibles.')->withInput();
              } else if(!$stock) {
                //$stock->quantity = 0;
                return redirect($this->prev)->with('message_error', 'El item "'.$item->product_bridge->name.'" no cuenta con stock.')->withInput();
              }
            }
            //$stock->save();
          }
        }
      }
      if(config('sales.delivery')){
        $shipping_array = \Sales::calculate_shipping_cost($request->input('shipping_id'), $request->input('country_id'), $request->input('city_id'), $order_weight, null);
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
      $currency = $item->currency;
      $sale = new \Solunes\Sales\App\Sale;
      $sale->user_id = $user->id;
      if($customer){
        $sale->customer_id = $customer->id;
      }
      if($quotation){
        $sale->lead_status = 'quotation-request';
      } else {
        $sale->lead_status = 'sale';
      }
      if(config('sales.sales_agency')){
        $sale->agency_id = $agency->id;
      }
      $sale->currency_id = $currency->id;
      $sale->order_amount = $order_cost;
      $sale->amount = $total_cost;
      if(config('sales.ask_invoice')&&!$quotation){
        if(config('sales.generate_invoice_pagostt')){
          $sale->invoice = true;
        } else {
          $sale->invoice = false;
        }
        $sale->invoice_nit = $request->input('nit_number');
        $sale->invoice_name = $request->input('nit_social');
      } else {
        $sale->invoice = false;
      }
      //$sale->type = 'online';
      $sale->save();
      if($quotation){
        $sale->name = 'Cotización Online: #'.$sale->id;
      } else {
        $sale_name = 'Venta Online: #'.$sale->id;
        $sale->load('sale_items');
        $count_sale_items = count($cart->cart_items);
        if($count_sale_items>1){
          $sale_name .= ' (x'.$count_sale_items.' items)';
        } else if($count_sale_items==1){
          $sale_name .= ' (x1 item)';
        }
        $sale->name = $sale_name;
      }
      $sale->save();

      // Sale Payment
      if(!$quotation){
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
      }

      // Sale Delivery
      if(config('sales.delivery')){
        $shipping = \Solunes\Sales\App\Shipping::find($request->input('shipping_id'));
        if($shipping){
          $sale_delivery = new \Solunes\Sales\App\SaleDelivery;
          $sale_delivery->parent_id = $sale->id;
          $sale_delivery->shipping_id = $request->input('shipping_id');
          $sale_delivery->currency_id = $sale->currency_id;
          if(config('sales.delivery_city')){
            if(config('sales.delivery_country')){
              $sale_delivery->country_code = $user->city->region->country->name;
            } else {
              $sale_delivery->country_code = 'BO';
            }
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
          if(config('sales.delivery_select_day')&&$request->has('shipping_date')){
            $sale_delivery->shipping_date = $request->input('shipping_date');
          }
          if(config('sales.delivery_select_hour')&&$request->has('shipping_time_id')){
            $sale_delivery->shipping_time_id = $request->input('shipping_time_id');
          }
          $sale_delivery->name = 'Pedido de venta en linea';
          $sale_delivery->address = $request->input('address');
          $sale_delivery->address_extra = $request->input('address_extra');
          $sale_delivery->postal_code = 'LP01';
          $sale_delivery->phone = $request->input('cellphone');
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
          if(config('sales.ask_coordinates')&&!$quotation){
            $coordinates = $request->input('map_coordinates');
            if($coordinates){
              $coordinates = explode(';', $coordinates);
              if(isset($coordinates[0])&&isset($coordinates[1])){
                $sale_delivery->latitude = $coordinates[0];
                $sale_delivery->longitude = $coordinates[1];
                if(!$customer->latitude&&!$customer->longitude){
                  $customer->latitude = $coordinates[0];
                  $customer->longitude = $coordinates[1];
                  $customer->save();
                }
              }
            }
          }
          $sale_delivery->save();
        }
      }

      // Sale Items
      $store_agency = $agency;
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
        if(config('solunes.inventory')&&!config('inventory.reduce_inventory_after_purchase')&&$sale_item->product_bridge->stockable&&!$quotation){
          \Inventory::reduce_inventory($store_agency, $sale_item->product_bridge, $sale_item->quantity);
        }
      }

      $cart->status = 'sale';
      $cart->user_id = $user->id;
      $cart->save();

      $sale->updated_at = NULL;
      if(config('sales.sale_duration_hours')){
        $now = new \DateTime('+'.config('sales.sale_duration_hours').' hours');
        $sale->expiration_date = $now->format('Y-m-d');
        $sale->expiration_time = $now->format('H:i:s');
      }
      $sale->save();

      // Send Email
      if($quotation){
        //$vars = ['@name@'=>$user->name, '@total_cost@'=>$sale->total_cost, '@sale_link@'=>url('process/sale/'.$sale->id)];
        //\FuncNode::make_email('new-sale', [$user->email], $vars);
      } else {
        \Payments::generatePayment($sale); // Generar pagos de la venta
        $vars = ['@name@'=>$user->name, '@total_cost@'=>$sale->total_cost, '@sale_link@'=>url('process/sale/'.$sale->id)];
        \FuncNode::make_email('new-sale', [$user->email], $vars);
      }

      $redirect = 'process/sale/'.$sale->id;
      if($quotation){
        return redirect($redirect)->with('message_success', 'Su cotización fue generada correctamente.');
      }
      // Revisar redirección a método de pago antes a PAGOSTT, TODO: Configurar para Paypal y Otros
      if(config('sales.redirect_to_payment')&&$sale_payment->payment_method->code=='pagostt'){
        $model = '\\'.$sale_payment->payment_method->model;
        return \Payments::generateSalePayment($sale, $model, $redirect, NULL);
      }
      return redirect($redirect)->with('message_success', 'Su compra fue confirmada correctamente, ahora debe proceder al pago para finalizarla.');
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al actualizar su carro de compras.');
    }
  }

  /* Ruta GET para revisar venta pendiente */
  public function getSale($sale_id) {
    if($sale = \Solunes\Sales\App\Sale::findId($sale_id)->checkOwner()->with('cart','cart.cart_items')->first()){
      if($sale->status!='holding'&&$sale->status!='pending-delivery'){
        return redirect($this->prev)->with('message_error', 'Esta orden ya no se encuentra disponible para pagar.');
      }
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

  /* Ruta GET probar el envio de emails de una venta */
  public function getTestSuccessSale($sale_id) {
    if(config('services.enable_test')){
      $sale = \Solunes\Sales\App\Sale::find($sale_id);
      $customer['email'] = 'edumejia30@gmail.com';
      $customer['name'] = 'Eduardo Mejia';
      \Sales::customerSuccessfulPayment($sale, $customer);
    } else {
      return redirect('')->with('message_error', 'La prueba no pudo ser realizada.');
    }
  }

}