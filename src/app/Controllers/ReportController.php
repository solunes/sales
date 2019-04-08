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

  public function getSalesSummary() {
    $array['currency'] = \Solunes\Business\App\Currency::find(1);
    $sales = \Solunes\Sales\App\Sale::whereIn('status', ['paid','accounted','pending-delivery','delivered']);
    // Gráficos
    $type_items = [['type'=>'paid','total'=>round(10)], ['type'=>'web','total'=>round(20)], ['type'=>'online','total'=>round(30)], ['type'=>'pending','total'=>round(40)]];
    $type_items = ['total'=>round(10),'asdd'=>round(20)];
    //$type_items = json_decode(json_encode($type_items));
    $type_field_names = ['total'=>'Ventas en Tienda '.$array['currency']->name, 'asdd'=>'Ventas Web '.$array['currency']->name, 'online'=>'Ventas Online '.$array['currency']->name, 'pending'=>'Ventas no Cobradas '.$array['currency']->name];
    $array['graphs']['type'] = ['type'=>'pie', 'graph_name'=>'type', 'name'=>'type', 'label'=>'Tipo de Ventas', 'items'=>$type_items, 'subitems'=>[], 'field_names'=>$type_field_names];
    return \Reports::check_report_view('sales::list.sales-report', $array);
  }
  
  public function getSalesDetail() {
    $sales = \Solunes\Sales\App\Sale::whereIn('status', ['paid','accounted','pending-delivery','delivered']);
    // Gráficos
    $type_items = [['type'=>'paid','total'=>round($store)], ['type'=>'web','total'=>round($web)], ['type'=>'online','total'=>round($online)], ['type'=>'pending','total'=>round($pending_total)]];
    $type_items = json_decode(json_encode($type_items));
    $type_field_names = ['paid'=>'Ventas en Tienda '.$array['currency']->name, 'web'=>'Ventas Web '.$array['currency']->name, 'online'=>'Ventas Online '.$array['currency']->name, 'pending'=>'Ventas no Cobradas '.$array['currency']->name];
    $array['graphs']['type'] = ['type'=>'pie', 'graph_name'=>'type', 'name'=>'type', 'label'=>'Tipo de Ventas', 'items'=>$type_items, 'subitems'=>[], 'field_names'=>$type_field_names];
    return \Reports::check_report_view('sales::list.sales-report', $array);
  }

}