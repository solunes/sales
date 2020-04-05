@extends('master::layouts/email')

@section('icon','Cart')

@section('content')
	<h2 style="font-family: Arial, Helvetica, sans-serif;margin-top: 16px;margin-bottom: 16px;word-break: break-word;font-size: 28px;line-height: 38px;font-weight: bold;">
		Compra de Tickets
	</h2>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 16px;margin-bottom: 8px;word-break: break-word;font-size: 19px;line-height: 31px;">
		{{ trans('sales::mail.successful_ticket_content') }}
	</p>
	<p style="font-family: Arial, Helvetica, sans-serif;margin-top: 16px;margin-bottom: 8px;word-break: break-word;font-size: 19px;line-height: 31px;">
		Detalle de compra:
		<?php $total = 0; ?>
		@foreach($sale->sale_items as $sale_item)
		<br>- {{ $sale_item->detail }} ({{ $sale_item->quantity }}) - {{ $sale->currency->name }} {{ $sale_item->price }}
		<?php $total += $sale_item->price * $sale_item->quantity; ?>
		@endforeach
		<br><strong>TOTAL: {{ $sale->currency->name }} {{ round($total, 2) }}</strong>
	</p>
	@include('master::emails.helpers.button', ['button_link'=>asset(\Asset::get_file('reservation-tickets_file',$reservation->tickets_file)), 'button_title'=>'Ver Entrada'])
@endsection

@section('unsuscribe-email')
	{{ url('auth/unsuscribe/'.urlencode($email)) }}
@endsection