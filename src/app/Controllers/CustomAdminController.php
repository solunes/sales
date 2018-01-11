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

	public function getIndex() {
		$user = auth()->user();
		//$array['tasks'] = $user->active_sales_tasks;
		$array['tasks'] = \Solunes\Sales\App\SalesTask::limit(2)->get();
		$array['active_issues_saless'] = \Solunes\Sales\App\Sales::has('active_sales_issues')->with('active_sales_issues')->get();
      	return view('sales::list.dashboard', $array);
	}

	/* Módulo de Proyectos */

	public function allSaless() {
		$array['items'] = \Solunes\Sales\App\Sales::get();
      	return view('sales::list.saless', $array);
	}

	public function findSales($id, $tab = 'description') {
		if($item = \Solunes\Sales\App\Sales::find($id)){
			$array = ['item'=>$item, 'tab'=>$tab];
      		return view('sales::item.sales', $array);
		} else {
			return redirect($this->prev)->with('message_error', 'Item no encontrado');
		}
	}

	public function findSalesTask($id) {
		if($item = \Solunes\Sales\App\SalesTask::find($id)){
			$array = ['item'=>$item];
      		return view('sales::item.sales-task', $array);
		} else {
			return redirect($this->prev)->with('message_error', 'Item no encontrado');
		}
	}

	public function findProjecIssue($id) {
		if($item = \Solunes\Sales\App\SalesIssue::find($id)){
			$array = ['item'=>$item];
      		return view('sales::item.sales-issue', $array);
		} else {
			return redirect($this->prev)->with('message_error', 'Item no encontrado');
		}
	}

	public function allWikis($sales_type_id = NULL, $wiki_type_id = NULL) {
		$array['sales_type_id'] = $sales_type_id;
		$array['wiki_type_id'] = $wiki_type_id;
		if($sales_type_id&&$wiki_type_id){
			$array['items'] = \Solunes\Sales\App\Wiki::where('sales_type_id',$sales_type_id)->where('wiki_type_id',$wiki_type_id)->get();
		} else if($sales_type_id){
			$array['items'] = \Solunes\Sales\App\WikiType::get();
		} else {
			$array['items'] = \Solunes\Sales\App\SalesType::get();
		}
      	return view('sales::list.wikis', $array);
	}

	public function findWiki($id) {
		if($item = \Solunes\Sales\App\Wiki::find($id)){
			$array = ['item'=>$item];
      		return view('sales::item.wiki', $array);
		} else {
			return redirect($this->prev)->with('message_error', 'Item no encontrado');
		}
	}

}