@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="container solunes-store">
  @include('sales::includes.buy-now')
</div>
@endsection

@section('script')
@endsection