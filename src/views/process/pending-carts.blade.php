@extends('master::layouts/admin-2')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="content-header-left col-md-9 col-12 mb-2">
    <div class="row breadcrumbs-top">
        <div class="col-12">
            <h2 class="content-header-title float-left mb-0">Mis Carros de Compras</h2>
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url(config('customer.redirect_after_login')) }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Mis Carros de Compras</li>
                </ol>
            </div>
        </div>
    </div>
</div>


<div class="content-body ecommerce-application">             
    <!-- Wishlist Starts -->
    <section id="wishlist" class="grid-view wishlist-items">
        @foreach($sales as $item)
        <div class="card ecommerce-card">
            <div class="card-content">
                <?php $sale_image = $item->sale_item; ?>
                @if($sale_image&&$sale_image->product_bridge->image)
                <div class="item-img text-center">
                  <img src="{{ asset(\Asset::get_image_path('product-bridge-image','thumb',$sale_image->product_bridge->image)) }}" class="img-fluid" alt="img-placeholder">
                </div>
                @endif
                <div class="card-body">
                    <div class="item-wrapper">
                        <div>
                            <h4 class="item-price">
                                Monto: Bs. {{ $item->amount }}
                            </h4>
                        </div>
                    </div>
                    <div class="item-name">
                        <span>{{ $item->name }}</span>
                    </div>
                    <div
                        <p class="item-description">
                            {{ $item->sale_item->detail }}
                        </p>
                    </div>
                </div>
                <div class="item-options text-center">
                    <div class="wishlist remove-wishlist">
                      @if(config('payments.customer_cancel_payments')&&$payment->customer_cancel_payments)
                      <a href="{{ url('payments/cancel-payment/'.$payment->id) }}">
                        <i class="feather icon-x align-middle"></i> Cancelar
                      </a>
                      @endif
                    </div>
                    <div class="cart move-cart">
                      <a href="{{ url('process/sale/'.$item->id) }}">
                        <i class="feather icon-home"></i> <span class="move-to-cart">Finalizar Compra</span>
                      </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </section>
    <!-- Wishlist Ends -->            
</div>
@endsection

@section('script')

@endsection