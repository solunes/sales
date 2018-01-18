<?php

namespace Solunes\Sales\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Asset;

class CustomAdminController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->middleware('auth');
	  $this->middleware('permission:sales');
	  $this->prev = $url->previous();
	  $this->module = 'admin';
	}

	public function getCalculateTotal($amount, $currency_id) {
		$main_currency = \Solunes\Business\App\Currency::find($currency_id);
		$item_currency = \Solunes\Business\App\Currency::find(1);
		return \Business::calculate_currency($amount, $main_currency, $item_currency);
	}

	public function getCreateSale() {
		$array['places'] = \Solunes\Business\App\Agency::where('type', 'store')->lists('name', 'id');
		$array['currencies'] = \Solunes\Business\App\Currency::where('in_accounts',1)->get()->lists('name', 'id');
		$array['invoices'] = [0=>'Sin Factura', 1=>'Con Factura'];
		$array['types'] = ['normal'=>'En Tienda', 'web'=>'Web', 'online'=>'Online'];
		$array['i'] = NULL;
		$array['dt'] = 'create';
		$array['action'] = 'create';
		$array['model'] = 'sale';
		$array['currency'] = \Solunes\Business\App\Currency::where('type', 'main')->first();
		$array['node'] = \Solunes\Master\App\Node::where('name', 'product')->first();
        $categories = \Solunes\Business\App\Category::has('products')->with('products')->orderBy('name', 'ASC')->get();
        $product_options = [''=>'-'];
        foreach($categories as $category){
            foreach($category->products as $product){
            	if($product->total_stock>0){
                	$product_options[$category->name][$product->id] = $product->name.' ('.$product->barcode.')';
            	}
            }
        }
		$array['products'] = $product_options;
		$array['currency_dollar'] = \Solunes\Business\App\Currency::find(2);
      	return view('store::item.create-sale', $array);
	}

    public function postCreateSale(Request $request) {
      $validator = \Validator::make($request->all(), \Solunes\Sales\App\Sale::$rules_create_sale);
      if($request->input('paid_amount')<$request->input('amount')&&!$request->input('credit')){
		return redirect($this->prev)->with('message_error', 'Debe introducir un monto pagado mayor al total, o incluir la opción de crédito.')->withErrors($validator);
      }
	  if($validator->passes()&&$request->input('product_id')[0]) {

		$item = new \Solunes\Sales\App\Sale;
		$item->user_id = auth()->user()->id;
		$item->place_id = $request->input('place_id');
		$item->currency_id = 1;
		$item->order_amount = $request->input('amount');
		$item->amount = $request->input('amount');
		$item->change = $request->input('change');
		$item->paid_amount = $request->input('paid_amount');
		$item->invoice = $request->input('invoice');
		$item->invoice_name = $request->input('invoice_name');
		$item->invoice_nit = $request->input('invoice_nit');
		$item->type = $request->input('type');
		$item->status = 'paid';
		$item->save();

		// Crear pagos de venta
		if($request->input('cash_bob')){
			$detail = 'Cobro en efectivo (BOB) realizado en tienda';
			\Sales::register_sale_payment($item, 1, 1, 'paid', $request->input('cash_bob'), $detail);
		}
		if($request->input('cash_usd')){
			$detail = 'Cobro en efectivo (USD) realizado en tienda';
			\Sales::register_sale_payment($item, 1, 2, 'paid', $request->input('cash_usd'), $detail, $request->input('exchange'));
		}
		if($request->input('pos_bob')){
			$detail = 'Cobro en POS (BOB) realizado en tienda';
			\Sales::register_sale_payment($item, 2, 1, 'paid', $request->input('pos_bob'), $detail);
		}

		// Crear Envío en Pedido
		/*if($request->input('shipping_cost')>0){
			$sale_delivery = new \Solunes\Sales\App\SaleDelivery;
			$sale_delivery->parent_id = $item->id;
			$sale_delivery->shipping_id = $request->input('shipping_id');
			$sale_delivery->detail = $request->input('credit_details');
			$sale_delivery->currency_id = 1;
			$sale_delivery->amount = $request->input('credit_amount');
			$sale_delivery->save();
		}*/

		// Crear Venta a Crédito
		if($request->input('credit')){
			$credit = new \Solunes\Sales\App\SaleCredit;
			$credit->parent_id = $item->id;
			$credit->due_date = $request->input('credit_due');
			$credit->detail = $request->input('credit_details');
			$credit->currency_id = 1;
			$credit->amount = $request->input('credit_amount');
			$credit->save();
			$credit_percentage = $request->input('amount') / $request->input('credit_amount');
		}

		$total_count = count($request->input('product_id'));
		$count = 0;
		$pending_sum = 0;
		foreach($request->input('product_id') as $product_key => $product_id){
			if($product = \Solunes\Business\App\ProductBridge::find($product_id)){
				$subitem = new \Solunes\Sales\App\SaleItem;
				$subitem->parent_id = $item->id;
				$subitem->product_id = $product->id;
				$subitem->currency_id = $product->currency_id;
				$subitem->price = $request->input('price')[$product_key];
				$subitem->quantity = $request->input('quantity')[$product_key];
				$subitem->total = $subitem->price * $subitem->quantity;
				if($item->credit&&$item->credit_amount>0){
					$pending = round($credit_percentage * $total, 2);
					$pending_sum += $pending;
					$subitem->pending = $pending;
					$count ++;
					if($total_count==$count){
						$diff = $item->credit_amount - $pending_sum;
						if($diff!=0){
							$subitem->pending = $subitem->pending + $diff;
						}
					}
				}
				$subitem->save();
			}
		}
		return redirect('admin/model/sale/view/'.$item->id)->with('message_success', 'La venta se realizó correctamente');
	  } else {
		return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos y al menos un producto para enviarlo.')->withErrors($validator);
	  }
    }

	public function getCreateRefund($sale_id = NULL) {
		$array['i'] = NULL;
		$array['dt'] = 'create';
		$array['action'] = 'create';
		$array['model'] = 'refund';
		$array['node'] = \Solunes\Master\App\Node::where('name', 'refund')->first();
		$array['sale_id'] = $sale_id;
		$array['places'] = \Solunes\Business\App\Agency::lists('name','id');
		$array['products'] = \Solunes\Business\App\Agency::lists('name','id');
		$sales = \Solunes\Sales\App\Sale::orderBy('created_at', 'DESC');
        if(request()->has('initial_date')){
            $initial_date = request()->input('initial_date').' 00:00:00';
            $sales = $sales->where('created_at', '>', $initial_date);
        }
        if(request()->has('end_date')){
            $end_date = request()->input('end_date').' 23:59:59';
            $sales = $sales->where('created_at', '<', $end_date);
        }
        if(request()->has('product_id')){
            $product_id = request()->input('product_id');
        	$sales = $sales->whereHas('sale_items', function ($query) use($product_id) {
			    $query->where('product_id', $product_id);
			});
        }
        if(request()->has('place_id')){
            $place_id = request()->input('place_id');
            $sales = $sales->where('place_id', $place_id);
        }
		$array['sales'] = $sales->get();
		if(!$sale_id||count($array['sales'])==1){
		} else {
			$array['sale'] = \Solunes\Sales\App\Sale::find($sale_id);
		}
      	return view('store::item.create-refund', $array);
	}

    public function postCreateRefund(Request $request) {
      $validator = \Validator::make($request->all(), \Solunes\Sales\App\Refund::$rules_create_refund);
	  if($validator->passes()) {

	  	$sale = \Solunes\Sales\App\Sale::find($request->input('sale_id'));

		$item = new \Solunes\Sales\App\Refund;
		$item->user_id = auth()->user()->id;
		$item->place_id = $sale->place_id;
		$item->currency_id = $sale->currency_id;
		$item->sale_id = $request->input('sale_id');
		$item->reference = $request->input('reference');
		$item->amount = $request->input('amount');
		$item->save();

		foreach($request->input('product_id') as $product_key => $product_id){
			if($request->input('refund_quantity')[$product_key]>0){
				$subitem = new \Solunes\Sales\App\RefundItem;
				$subitem->parent_id = $item->id;
				$subitem->product_id = $request->input('product_id')[$product_key];
				$subitem->currency_id = $item->currency_id;
				$subitem->initial_quantity = $request->input('initial_quantity')[$product_key];
				$subitem->initial_amount = $request->input('initial_amount')[$product_key];
				$subitem->refund_quantity = $request->input('refund_quantity')[$product_key];
				$subitem->refund_amount = $request->input('refund_amount')[$product_key];
				$subitem->sale_item_id = $request->input('sale_item_id')[$product_key];
				$subitem->save();
			}
		}
		return redirect('admin/model/refund/view/'.$item->id)->with('message_success', 'La devolución se realizó correctamente');
	  } else {
		return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos y al menos un producto para enviarlo.')->withErrors($validator);
	  }
    }

}