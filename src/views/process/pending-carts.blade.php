@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="solunes-store container">
  <h1>Mis Carros de Compras</h1>
  @if(count($items)>0)
    <div class="cart-waiting">

      <div class="row">
        @foreach($items as $item)
          <div class="col-md-4">
            <div class="each_cart">
              <div class="row title_cart">
                <div class="col-md-5 ">
                  <p class="status pending">Estado: <br><span>Pendiente</span></p>
                </div>
                <div class="col-md-7">
                  <h5>{{ count($item->cart_items) }} Items</h5>
                </div>          
              </div>
              <hr>
              <div class="row description_cart">
                <div class="col-xs-12">
                  <p><i class="fa fa-map-marker"></i>Av. Test #165 Calle Prueba</p>
                  <p><i class="fa fa-phone"></i>2 2236565 / 2 2248585</p>
                  <p><i class="fa fa-envelope"></i>test@info.com</p>
                  <p><i class="fa fa-calendar"></i>25 - 10 - 2018</p>
                </div>
              </div>
              <hr>
              <div class="row products_cart">
                <div class="col-md-3">
                  <img src="{{ asset('assets/images/img.png') }}" />
                </div>
                <div class="col-md-3">
                  <img src="{{ asset('assets/images/img1.png') }}" />
                </div>
                <div class="col-md-3">
                  <img src="{{ asset('assets/images/img2.png') }}" />
                </div>
                <div class="col-md-3">
                  <img src="{{ asset('assets/images/img3.png') }}" />
                </div>
              </div>
              <div class="btn_contain">
                <a class="view-btn" href="">Ver más</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

    </div><!-- End container -->
  @else
    <p>Actualmente no se encontraron carros de compra pendientes en su cuenta.</p>
  @endif
</div>
@endsection

@section('script')

@endsection