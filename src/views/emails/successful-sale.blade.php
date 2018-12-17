@extends('master::layouts/email')

@section('icon')
Cart
@endsection

@section('content')
	<h2 style="font-family: Arial, Helvetica, sans-serif;margin-top: 16px;margin-bottom: 8px;word-break: break-word;font-size: 28px;line-height: 38px;font-weight: bold;">
		Compra Realizada
	</h2>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 0px;margin-bottom: 32px;word-break: break-word;font-size: 19px;line-height: 31px;">
		{{ trans('sales::mail.successful_sale_content') }}
	</p>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 0px;margin-bottom: 32px;word-break: break-word;font-size: 19px;line-height: 31px;">
		Detalle de compra:
		<?php $total = 0; ?>
		@foreach($sale->sale_items as $sale_item)
		<br>- {{ $sale_item->detail }} ({{ $sale_item->quantity }}) - Bs. {{ $sale_item->price }}
		<?php $total += $sale_item->price * $sale_item->quantity; ?>
		@endforeach
		<br><strong>TOTAL: Bs. {{ round($total, 2) }}</strong>
	</p>
	@if(count($sale->sale_deliveries)>0)
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 0px;margin-bottom: 32px;word-break: break-word;font-size: 19px;line-height: 31px;">
		@foreach($sale->sale_deliveries as $sale_delivery)
			Envío a: 
			@if($sale_delivery->city)
				{{ $sale_delivery->country_code }} - {{ $sale_delivery->city->name }}
			@endif
			@if($sale_delivery->city_other)
				- {{ $sale_delivery->city_other }}
			@endif
			@if($sale_delivery->address)
				- {{ $sale_delivery->address }}
				@if($sale_delivery->address_extra)
					{{ $sale_delivery->address_extra }}
				@endif
			@endif
		@endforeach
	</p>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 0px;margin-bottom: 32px;word-break: break-word;font-size: 19px;line-height: 31px;">
		{{ trans('sales::mail.successful_sale_delivery').' '.$sale_delivery->delivery_time }}
	</p>
	@endif
@endsection

@section('unsuscribe-email')
	{{ url('auth/unsuscribe/'.urlencode($email)) }}
@endsection