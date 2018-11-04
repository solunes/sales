@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="solunes-store">
  <div class="cart-waiting">
    <div class="container">

      <div class="row">
        <div class="col-md-4">
          <div class="each_cart">
            <div class="row title_cart">
              <div class="col-md-5 ">
                <p class="status pending">Estado: <br><span>Pendiente</span></p>
              </div>
              <div class="col-md-7">
                <h5>3 Items</h5>
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
        <div class="col-md-4">
          <div class="each_cart">
            <div class="row title_cart">
              <div class="col-md-5 ">
                <p class="status delivered">Estado: <br><span>Completado</span></p>
              </div>
              <div class="col-md-7">
                <h5>3 Items</h5>
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
        <div class="col-md-4">
          <div class="each_cart">
            <div class="row title_cart">
              <div class="col-md-5 ">
                <p class="status cancel">Estado: <br><span>Cancelado</span></p>
              </div>
              <div class="col-md-7">
                <h5>3 Items</h5>
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
      </div>

    </div>
  </div><!-- End container -->
</div>
@endsection

@section('script')

@endsection