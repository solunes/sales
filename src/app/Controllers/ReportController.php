<?php

namespace Solunes\Sales\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Asset;

class ReportController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
    $this->middleware('auth');
    $this->middleware('permission:dashboard');
	  $this->prev = $url->previous();
	}

  public function getSalesReport() {
    $model = \Solunes\Sales\App\Sale::where('status','!=','holding');
    $array = \Sales::check_report_header($model);
    $array['show_place'] = true;

    $codes_array = ['income_sale', 'income_sale_credit', 'expense_refund'];
    $account_array = \Solunes\Sales\App\Account::whereIn('code', $codes_array)->lists('id')->toArray();
    $accounts = \Solunes\Sales\App\PlaceAccountability::whereIn('account_id', $account_array)->where('created_at', '>=', $array['i_date'])->where('created_at', '<=', $array['e_date']);
    if($array['place']!='all'){
      $accounts = $accounts->where('parent_id', $array['place']);
    }
    $accounts = $accounts->get();
    $sales = 0;
    $cash = 0;
    $pos = 0;
    $web = 0;
    $online = 0;
    $pending_total = 0;
    $sales_total = 0;
    $refund_total = 0;
    $currency = \Solunes\Sales\App\Currency::find(1);
    foreach($accounts as $item){
      $new_total = \Sales::calculate_currency($item->amount, $array['currency'], $item->currency);
      if($item->account->code=='expense_refund'){
        $refund_total -= $new_total;
      } else if($item->account->code=='income_sale_credit') {
        $pending_total += $new_total;
      } else {
        $sales += $new_total;
        $sales_total += $new_total;
        foreach($item->other_accounts as $other){
          $other_amount = \Sales::calculate_currency($other->real_amount, $array['currency'], $currency);
          if($other->account->concept->code=='asset_cash'){
            $cash += $other_amount;
          } else if($other->account->concept->code=='asset_bank'){
            $pos += $other_amount;
          }
        }
        /*if($item->type=='normal'){
          $sales += $paid;
          if($item->pos_bob>0){
            $new_total -= $item->pos_bob;
            $paid -= $item->pos_bob;
            $pos += $item->pos_bob;
          } 
          $cash += $paid;
        } else if($item->type=='web'){
          $web += $paid;
        } else if($item->type=='online'){
          $online += $paid;
        }*/
      }
    }
    $array = $array + ['total'=>$sales_total, 'sales'=>$sales, 'cash'=>$cash,'pos'=>$pos, 'web'=>$web, 'online'=>$online, 'pending'=>$pending_total, 'refund_total'=>$refund_total];
    // Gráficos
    $type_items = [['type'=>'paid','total'=>round($sales)], ['type'=>'web','total'=>round($web)], ['type'=>'online','total'=>round($online)], ['type'=>'pending','total'=>round($pending_total)]];
    $type_items = json_decode(json_encode($type_items));
    $type_field_names = ['paid'=>'Ventas en Tienda '.$array['currency']->name, 'web'=>'Ventas Web '.$array['currency']->name, 'online'=>'Ventas Online '.$array['currency']->name, 'pending'=>'Ventas no Cobradas '.$array['currency']->name];
    $array['graphs']['type'] = ['type'=>'pie', 'graph_name'=>'type', 'name'=>'type', 'label'=>'Tipo de Ventas', 'items'=>$type_items, 'subitems'=>[], 'field_names'=>$type_field_names];
    return \Sales::check_report_view('sales::list.sales-report', $array);
  }
  
  public function getSalesDetailReport() {
    $model = \Solunes\Store\App\Sale::where('status','!=','holding');
    $array = \Store::check_report_header($model, ['web'=>'Web', 'online'=>'Online', 'pos'=>'POS']);
    $array['show_place'] = true;

    $sales = \Solunes\Store\App\Sale::where('status','!=','holding')->where('created_at', '>=', $array['i_date'])->where('created_at', '<=', $array['e_date']);
    if($array['place']!='all'){
      if($array['place']=='web'){
        $sales = $sales->where('type', 'web');
      } else if($array['place']=='online'){
        $sales = $sales->where('type', 'online');
      } else if($array['place']=='pos'){
        $sales = $sales->where('type', 'pos');
      } else {
        $sales = $sales->where('place_id', $array['place']);
      }
    }
    $sales = $sales->with('sale_items')->get();
    $array_items = [];
    $paid = 0;
    $pending = 0;
    $shipping = 0;
    $count = 1;
    foreach($sales as $sale){
      $new_total = \Store::calculate_currency($sale->amount, $array['currency'], $sale->currency);
      if($sale->shipping_cost>0){
        $shipping_cost = \Store::calculate_currency($sale->shipping_cost, $array['currency'], $sale->currency);
        $shipping += $shipping_cost;
        $new_total -= $shipping_cost;
      }
      if($pending_payment = $sale->pending_payment){
        $new_pending = \Store::calculate_currency($pending_payment->amount, $array['currency'], $pending_payment->currency);
        $pending_amount = \Store::calculate_currency($pending_payment->amount, $sale->currency, $pending_payment->currency);
        $paid += ($new_total - $new_pending);
        $pending += $new_pending;
      } else {
        $pending_amount = 0;
        $paid += $new_total;
      }
      foreach($sale->sale_items as $item){
        $subtotal = round($item->price * $item->quantity);
        $array_items[$item->id] = ['count'=>$count++, 'sale'=>$sale, 'item'=>$item, 'total'=>$subtotal];
        $subpending = 0;
        if($pending_amount>0){
          if($subtotal>$pending_amount){
            $subpending = $pending_amount;
            $pending_amount = 0;
          } else {
            $subpending = $subtotal;
            $pending_amount -= $subtotal;
          }
        }
        $array_items[$item->id]['pending'] = number_format($subpending, 2, '.', '').' '.$item->currency->name;
      }
    }
    $array['pending'] = $pending;
    $array['paid'] = $paid;
    $array['shipping'] = $shipping;
    $array['total'] = $pending + $paid;
    $array['items'] = $array_items;
    return \Store::check_report_view('store::list.sales-detail-report', $array);
  }

}