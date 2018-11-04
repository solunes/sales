@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="container solunes-store">
  <div class="payment-history">

    <div class="row">
      <div class="payment-table">
        <table style="width:100%">
          <tr>
            <th>N° ORDEN</th>
            <th>FECHA DE PAGO</th> 
            <th>VÍA DE PAGO</th>
            <th>MONTO</th>
            <th style="width:10%">VER</th>
          </tr>
          <tr class="each">
            <td class="border-site">#152</td>
            <td>29 octubre, 2018</td> 
            <td>Pagos TT</td>
            <td>Bs. 158</td>
            <td class="icon-cell">VER <i class="fa fa-plus-circle"></i></td>
          </tr>
          <tr class="each">
            <td class="border-site">#152</td>
            <td>29 octubre, 2018</td> 
            <td>Pagos TT</td>
            <td>Bs. 158</td>
            <td class="icon-cell">VER <i class="fa fa-plus-circle"></i></td>
          </tr>
          <tr class="each">
            <td class="border-site">#152</td>
            <td>29 octubre, 2018</td> 
            <td>Pagos TT</td>
            <td>Bs. 158</td>
            <td class="icon-cell">VER <i class="fa fa-plus-circle"></i></td>
          </tr>
          <tr class="each">
            <td class="border-site">#152</td>
            <td>29 octubre, 2018</td> 
            <td>Pagos TT</td>
            <td>Bs. 158</td>
            <td class="icon-cell">VER <i class="fa fa-plus-circle"></i></td>
          </tr>
        </table>
      </div>
    </div>

  </div>
</div><!-- End container  -->
@endsection

@section('script')
  <!--<script>
    new CBPFWTabs(document.getElementById('tabs'));
  </script>-->
@endsection