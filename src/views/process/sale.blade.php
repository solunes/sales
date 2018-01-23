@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<!-- checkout-area start -->
<div class="container solunes-store">
  
  @include('sales::includes.sale')
  
</div>
<!-- checkout-area end -->  
@endsection

@section('script')
@endsection