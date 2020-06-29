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
      $this->middleware('permission:dashboard');
	  $this->prev = $url->previous();
	  $this->module = 'admin';
	}

	public function getPendingQuotations() {
		$array['items'] = \Solunes\Sales\App\Sale::where('status','holding')->where('lead_status','quotation-done')->get();
      	return view('sales::list.pending-quotations', $array);
	}

	public function getSalePendingDeliveries() {
		$array['items'] = \Solunes\Sales\App\Sale::where('status','pending-delivery')->get();
      	return view('sales::list.pending-deliveries', $array);
	}

	public function getSaleDelivered($sale_id) {
		$item = \Solunes\Sales\App\Sale::where('id', $sale_id)->where('status','pending-delivery')->first();
		if($item){
			$item->status = 'delivered';
			$item->save();
			return redirect($this->prev)->with('message_success', 'El pedido fue marcado como enviado correctamente.');
	  	} else {
			return redirect($this->prev)->with('message_error', 'Hubo un error al marcar su pedido como enviado.');
	  	}
	}

	public function getCreateFastSale() {
		$user = auth()->user();
		if($user->hasRole('admin')){
			$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id')->toArray();
		} else {
			if($user->agency_id){
				$array['agencies'] = \Solunes\Business\App\Agency::where('id', $user->agency_id)->lists('name', 'id')->toArray();
			} else {
				$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id')->toArray();
			}
		}
		$array['payment_methods'] = \Solunes\Payments\App\PaymentMethod::get()->lists('name', 'id')->toArray();
		$array['currencies'] = \Solunes\Business\App\Currency::get()->lists('name', 'id')->toArray();
		$array['customers'] = [0=>'Seleccionar Contacto']+\Solunes\Customer\App\Customer::get()->sortBy('name')->lists('name', 'id')->toArray();
		$array['invoices'] = [0=>'Sin Factura', 1=>'Con Factura'];
		$array['i'] = NULL;
		$array['dt'] = 'create';
		$array['action'] = 'create';
		$array['model'] = 'sale';
		$array['currency'] = \Solunes\Business\App\Currency::where('type', 'main')->first();
		$array['node'] = \Solunes\Master\App\Node::where('name', 'product-bridge')->first();
	    $product_options = [''=>'-'];
        if(config('business.categories')){
	        $categories = \Solunes\Business\App\Category::has('product_bridges')->with('product_bridges')->get()->sortBy('name');
	        foreach($categories as $category){
	            foreach($category->product_bridges as $product){
	            	//if($product->total_stock>0){
	            		$name = $product->name;
	            		if(config('business.product_barcode')){
	            			$name .= ' ('.$product->barcode.')';
	            		}
	                	$product_options[$category->name][$product->id] = $name;
	            	//}
	            }
	        }
	    }
        $product_bridges = \Solunes\Business\App\ProductBridge::whereNull('category_id')->get()->sortBy('name');
        foreach($product_bridges as $product){
        	//if($product->total_stock>0){
        		$name = $product->name;
        		if(config('business.product_barcode')){
        			$name .= ' ('.$product->barcode.')';
        		}
            	$product_options['Sin categoría'][$product->id] = $name;
        	//}
        }
		$array['products'] = $product_options;
      	return view('sales::item.fast-sale', $array);
	}

    public function postCreateFastSale(Request $request) {
      $validator = \Validator::make($request->all(), \Solunes\Sales\App\Sale::$rules_create_sale);
      /*if($request->input('paid_amount')<$request->input('amount')&&!$request->input('credit')){
		return redirect($this->prev)->with('message_error', 'Debe introducir un monto pagado mayor al total, o incluir la opción de crédito.')->withErrors($validator);
      }*/
	  if($validator->passes()&&$request->input('product_id')[0]) {
	  	$user = auth()->user();
	  	$customer = NULL;
	  	if($request->input('invoice_name')){
	  		$customer = \Solunes\Customer\App\Customer::where('ci_number', $request->input('invoice_number'))->first();
	  	}
	  	if(!$customer){
	  		$customer = new \Solunes\Customer\App\Customer;
	  		$customer->first_name = $request->input('invoice_name');
	  		$customer->name = $request->input('invoice_name');
	  		$customer->ci_number = $request->input('invoice_number');
	  		$customer->password = 12345678;
	  		$customer->save();
	  	}
	  	$currency = \Solunes\Business\App\Currency::find(1);
	  	$payment_method = \Solunes\Payments\App\PaymentMethod::find($request->input('payment_method_id'));
	  	$invoice = 1;
	  	$invoice_name = $request->input('invoice_name');
	  	$invoice_number = $request->input('invoice_number');
	  	$agency_id = $request->input('agency_id');
	  	$sale_details = [];
	  	foreach($request->input('product_id') as $product_key => $product_id){
	  		$product_bridge = \Solunes\Business\App\ProductBridge::find($product_id);
	  		$sale_details[] = ['product_bridge_id'=>$product_bridge->id,'amount'=>$request->input('price')[$product_key],'quantity'=>$request->input('quantity')[$product_key],'detail'=>$request->input('product_name')[$product_key]];
	  	}
	  	$sale = \Sales::generateSale($user->id, $customer->id, $currency->id, $payment_method->id, $invoice, $invoice_name, $invoice_number, $sale_details, $agency_id, $request->input('detail'));
  		$sale_payment = $sale->sale_payment;
  		$payment = $sale_payment->payment;
	  	if($payment_method->code=='manual-payment'){
	  		$transaction = \Payments::generatePaymentTransaction($customer->id, [$payment->id], $payment_method->code);
            $transaction->external_payment_code = $transaction->payment_code;
            $transaction->status = 'paid';
            $transaction->save();
            $transaction = \Solunes\Payments\App\Transaction::find($transaction->id);
            if(config('payments.pagostt_params.enable_bridge')){
                $payment_registered = \PagosttBridge::transactionSuccesful($transaction);
            } else {
                $payment_registered = \Customer::transactionSuccesful($transaction);
            }
      		$payment = \Solunes\Payments\App\Payment::find($payment->id);
      		$sale_payment = $payment->sale_payment;
      		$sale = $sale_payment->sale;
      		$sale->status = 'delivered';
      		$sale->save();

	      	if($payment->invoice_url){
      			return redirect($payment->invoice_url);
      		}
      		return redirect($this->prev)->with('message_success', 'Su venta fue procesada correctamente.');
	  	} else if($payment_method->code=='pagostt') {
      		app('\Solunes\Payments\App\Controllers\PagosttController')->getMakeManualCashierPayment($customer->id, $payment->id);
      		$payment = \Solunes\Payments\App\Payment::find($payment->id);
      		if($payment->invoice_url){
      			return redirect($payment->invoice_url);
      		}
	  	}

		return redirect('admin/model/sale/view/'.$sale->id)->with('message_success', 'La venta se realizó correctamente');
	  } else {
		return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos y al menos un producto para enviarlo.')->withErrors($validator);
	  }
    }

	public function getCreateManualSale() {
		$user = auth()->user();
		if($user->hasRole('admin')){
			$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id')->toArray();
		} else {
			if($user->agency_id){
				$array['agencies'] = \Solunes\Business\App\Agency::where('id', $user->agency_id)->lists('name', 'id')->toArray();
			} else {
				$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id')->toArray();
			}
		}
		$array['payment_methods'] = \Solunes\Payments\App\PaymentMethod::get()->lists('name', 'id')->toArray();
		$array['currencies'] = \Solunes\Business\App\Currency::get()->lists('name', 'id')->toArray();
		$array['customers'] = [0=>'Seleccionar Contacto']+\Solunes\Customer\App\Customer::get()->sortBy('name')->lists('name', 'id')->toArray();
		$array['invoices'] = [0=>'Sin Factura', 1=>'Con Factura'];
		$array['i'] = NULL;
		$array['dt'] = 'create';
		$array['action'] = 'create';
		$array['model'] = 'sale';
		$array['currency'] = \Solunes\Business\App\Currency::where('type', 'main')->first();
		$array['node'] = \Solunes\Master\App\Node::where('name', 'product-bridge')->first();
	    $product_options = [''=>'-'];
        if(config('business.categories')){
	        $categories = \Solunes\Business\App\Category::has('product_bridges')->with('product_bridges')->get()->sortBy('name');
	        foreach($categories as $category){
	            foreach($category->product_bridges as $product){
	            	//if($product->total_stock>0){
	            		$name = $product->name;
	            		if(config('business.product_barcode')){
	            			$name .= ' ('.$product->barcode.')';
	            		}
	                	$product_options[$category->name][$product->id] = $name;
	            	//}
	            }
	        }
	    }
        $product_bridges = \Solunes\Business\App\ProductBridge::whereNull('category_id')->get()->sortBy('name');
        foreach($product_bridges as $product){
        	//if($product->total_stock>0){
        		$name = $product->name;
        		if(config('business.product_barcode')){
        			$name .= ' ('.$product->barcode.')';
        		}
            	$product_options['Sin categoría'][$product->id] = $name;
        	//}
        }
		$array['products'] = $product_options;
      	return view('sales::item.create-sale', $array);
	}

    public function postCreateManualSale(Request $request) {
      $validator = \Validator::make($request->all(), \Solunes\Sales\App\Sale::$rules_create_sale);
      /*if($request->input('paid_amount')<$request->input('amount')&&!$request->input('credit')){
		return redirect($this->prev)->with('message_error', 'Debe introducir un monto pagado mayor al total, o incluir la opción de crédito.')->withErrors($validator);
      }*/
	  if($validator->passes()&&$request->input('customer_id')&&$request->input('product_id')[0]) {
	  	$user = auth()->user();
	  	$customer = \Solunes\Customer\App\Customer::find($request->input('customer_id'));
	  	$currency = \Solunes\Business\App\Currency::find(1);
	  	$payment_method = \Solunes\Payments\App\PaymentMethod::find($request->input('payment_method_id'));
	  	$invoice = 1;
	  	$invoice_name = $request->input('invoice_name');
	  	$invoice_number = $request->input('invoice_number');
	  	$agency_id = $request->input('agency_id');
	  	$sale_details = [];
	  	foreach($request->input('product_id') as $product_key => $product_id){
	  		$product_bridge = \Solunes\Business\App\ProductBridge::find($product_id);
	  		$sale_details[] = ['product_bridge_id'=>$product_bridge->id,'amount'=>$request->input('price')[$product_key],'quantity'=>$request->input('quantity')[$product_key],'detail'=>$request->input('product_name')[$product_key]];
	  	}
	  	$sale = \Sales::generateSale($user->id, $customer->id, $currency->id, $payment_method->id, $invoice, $invoice_name, $invoice_number, $sale_details, $agency_id, $request->input('detail'));
	  	if($request->input('status')=='paid'){
	  		$sale_payment = $sale->sale_payment;
	  		$payment = $sale_payment->payment;
      		app('\Solunes\Payments\App\Controllers\PagosttController')->getMakeManualCashierPayment($customer->id, $payment->id);
      		$payment = \Solunes\Payments\App\Payment::find($payment->id);
      		if($payment->invoice_url){
      			return redirect($payment->invoice_url);
      		}
	  	}

		return redirect('admin/model/sale/view/'.$sale->id)->with('message_success', 'La venta se realizó correctamente');
	  } else {
		return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos y al menos un producto para enviarlo.')->withErrors($validator);
	  }
    }

	public function getCreateManualQuotation() {
		$user = auth()->user();
		if($user->hasRole('admin')){
			$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id');
		} else {
			if($user->agency_id){
				$array['agencies'] = \Solunes\Business\App\Agency::where('id', $user->agency_id)->lists('name', 'id');
			} else {
				$array['agencies'] = \Solunes\Business\App\Agency::lists('name', 'id');
			}
		}
		$array['payment_methods'] = \Solunes\Payments\App\PaymentMethod::get()->lists('name', 'id');
		$array['currencies'] = \Solunes\Business\App\Currency::get()->lists('name', 'id');
		$array['customers'] = [0=>'Seleccionar Contacto']+\Solunes\Customer\App\Customer::get()->sortBy('name')->lists('name', 'id')->toArray();
		$array['invoices'] = [0=>'Sin Factura', 1=>'Con Factura'];
		$array['i'] = NULL;
		$array['dt'] = 'create';
		$array['action'] = 'create';
		$array['model'] = 'sale';
		$array['currency'] = \Solunes\Business\App\Currency::where('type', 'main')->first();
		$array['node'] = \Solunes\Master\App\Node::where('name', 'product-bridge')->first();
	    $product_options = [''=>'-'];
        if(config('business.categories')){
	        $categories = \Solunes\Business\App\Category::has('product_bridges')->with('product_bridges')->get()->sortBy('name');
	        foreach($categories as $category){
	            foreach($category->product_bridges as $product){
	            	//if($product->total_stock>0){
	            		$name = $product->name;
	            		if(config('business.product_barcode')){
	            			$name .= ' ('.$product->barcode.')';
	            		}
	                	$product_options[$category->name][$product->id] = $name;
	            	//}
	            }
	        }
	    }
        $product_bridges = \Solunes\Business\App\ProductBridge::whereNull('category_id')->get()->sortBy('name');
        foreach($product_bridges as $product){
        	//if($product->total_stock>0){
        		$name = $product->name;
        		if(config('business.product_barcode')){
        			$name .= ' ('.$product->barcode.')';
        		}
            	$product_options['Sin categoría'][$product->id] = $name;
        	//}
        }
		$array['products'] = $product_options;
      	return view('sales::item.create-quotation', $array);
	}

    public function postCreateManualQuotation(Request $request) {
      $validator = \Validator::make($request->all(), \Solunes\Sales\App\Sale::$rules_create_sale);
      /*if($request->input('paid_amount')<$request->input('amount')&&!$request->input('credit')){
		return redirect($this->prev)->with('message_error', 'Debe introducir un monto pagado mayor al total, o incluir la opción de crédito.')->withErrors($validator);
      }*/
	  if($validator->passes()&&$request->input('customer_id')&&$request->input('product_id')[0]) {
	  	$user = auth()->user();
	  	$customer = \Solunes\Customer\App\Customer::find($request->input('customer_id'));
	  	$currency = \Solunes\Business\App\Currency::find(1);
	  	$payment_method = \Solunes\Payments\App\PaymentMethod::find($request->input('payment_method_id'));
	  	$invoice = 1;
	  	$agency_id = $request->input('agency_id');
	  	$invoice_name = $request->input('invoice_name');
	  	$invoice_number = $request->input('invoice_number');
	  	$sale_details = [];
	  	foreach($request->input('product_id') as $product_key => $product_id){
	  		$product_bridge = \Solunes\Business\App\ProductBridge::find($product_id);
	  		$sale_details[] = ['product_bridge_id'=>$product_bridge->id,'amount'=>$request->input('price')[$product_key],'quantity'=>$request->input('quantity')[$product_key],'detail'=>$request->input('product_name')[$product_key]];
	  	}
	  	$sale = \Sales::generateQuotation($user->id, $customer->id, $currency->id, $invoice, $sale_details, $agency_id, $request->input('detail'));
	  	if($request->input('status')=='paid'){
	  		$sale_payment = $sale->sale_payment;
	  		$payment = $sale_payment->payment;
      		app('\Solunes\Payments\App\Controllers\PagosttController')->getMakeManualCashierPayment($customer->id, $payment->id);
      		$payment = \Solunes\Payments\App\Payment::find($payment->id);
      		if($payment->invoice_url){
      			return redirect($payment->invoice_url);
      		}
	  	}

		return redirect('admin/model/sale/view/'.$sale->id)->with('message_success', 'La venta se realizó correctamente');
	  } else {
		return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos y al menos un producto para enviarlo.')->withErrors($validator);
	  }
    }

	public function getCreateSaleRefund($sale_id = NULL) {
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

    public function postCreateSaleRefund(Request $request) {
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