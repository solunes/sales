@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="solunes-store container">
  <h1>Mis Carros de Compras</h1>
  @if(count($items)>0||count($sales)>0)
    <div class="cart-waiting">

      <div class="row">
        @foreach($sales as $item)
          <div class="col-md-4">
            <div class="each_cart">
              <div class="row title_cart">
                <div class="col-md-5 ">
                  <p class="status pending"><span>Venta Pendiente</span></p>
                </div>
                <div class="col-md-7">
                  <h5>Items: {{ count($item->sale_items) }} </h5>
                </div>          
              </div>
              <hr>
              <div class="row description_cart">
                <div class="col-xs-12">
                  <p><i class="fa fa-calendar"></i>Fecha: {{ $item->created_at->format('d/m/Y') }}</p>
                </div>
              </div>
              <hr>
              <div class="row products_cart">
                @foreach($item->sale_items as $sale_item)
                <div class="col-md-4">
                  @if($sale_item->product_bridge->image)
                    {!! Asset::get_image('product-bridge-image', 'subdetail', $sale_item->product_bridge->image) !!}
                  @else
                    <img class="img-responsive" src="{{ asset('assets/admin/img/no_picture.jpg') }}" />
                  @endif
                  <p class="center">{{ $sale_item->price }} (x{{ $sale_item->quantity }})</p>
                </div>
                @endforeach
              </div>
              <div class="btn_contain">
                <a class="view-btn" href="{{ url('process/sale/'.$item->id) }}">Finalizar Compra</a>
              </div>
            </div>
          </div>
        @endforeach
        @foreach($items as $item)
          <div class="col-md-4">
            <div class="each_cart">
              <div class="row title_cart">
                <div class="col-md-5 ">
                  <p class="status pending"><span>Carro de Compras</span></p>
                </div>
                <div class="col-md-7">
                  <h5>Items: {{ count($item->cart_items) }} </h5>
                </div>          
              </div>
              <hr>
              <div class="row description_cart">
                <div class="col-xs-12">
                  <p><i class="fa fa-calendar"></i>Fecha: {{ $item->created_at->format('d/m/Y') }}</p>
                </div>
              </div>
              <hr>
              <div class="row products_cart">
                @foreach($item->cart_items as $cart_item)
                <div class="col-md-4">
                  @if($cart_item->product_bridge->image)
                    {!! Asset::get_image('product-bridge-image', 'subdetail', $cart_item->product_bridge->image) !!}
                  @else
                    <img class="img-responsive" src="{{ asset('assets/admin/img/no_picture.jpg') }}" />
                  @endif
                  <p class="center">{{ $cart_item->price }} (x{{ $cart_item->quantity }})</p>
                </div>
                @endforeach
              </div>
              <div class="btn_contain">
                <a class="view-btn" href="{{ url('process/confirmar-compra/carro-de-compras/'.$item->id) }}">Ver Carro</a>
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