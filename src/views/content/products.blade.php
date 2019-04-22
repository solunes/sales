@extends('layouts/master')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="solunes-store container">
  @include('sales::includes.products')
</div>
@endsection

@section('script')
@endsection