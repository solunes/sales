@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<!-- checkout-area start -->
<div class="container solunes-store">

  @include('sales::includes.finalizar-compra')

</div>
<!-- checkout-area end -->  
@endsection

@section('script')
  @include('sales::scripts.finalizar-compra-js')
  @if(isset($map_coordinates))
  	@include('master::scripts.map-js')
  	@include('master::scripts.map-register-js')
  @endif
@endsection