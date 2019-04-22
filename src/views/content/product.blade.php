@extends('layouts/master')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
@endsection

@section('content')
<div class="solunes-store container">
  @if($item)
    @include('sales::includes.product')
  @endif
</div>
<div class="solunes-store container">
  <h1>Otros Productos</h1>
  @if(count($products)>0)
    <div class="row">
      @foreach($products as $product)
        @include('sales::includes.product-summary', ['col_size'=>'col-md-3'])
      @endforeach
    </div>
  @endif
</div>

@endsection

@section('script')
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <script type="text/javascript">
    jQuery(document).on('change', '#variation_2', function() {
      value = jQuery('#variation_2').val();
      if(value==7){
        jQuery('#detail-shirt').slideUp(300);
        jQuery('#detail-shirt-2').slideUp(300);
      } else {
        jQuery('#detail-shirt').slideDown(300);
        jQuery('#detail-shirt-2').slideDown(300);
      }
    });
  </script>
  <script type="text/javascript">
    $('.slider-for').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: true,
      asNavFor: '.slider-nav'
    });
    $('.slider-nav').slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      asNavFor: '.slider-for',
      dots: false,
      centerMode: true,
      focusOnSelect: true
    });
  </script>
@endsection