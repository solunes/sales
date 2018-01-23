@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="container solunes-store">
  @include('sales::includes.confirmar-compra')
</div>
@endsection

@section('script')
  @include('sales::scripts.delete-row-js')
@endsection