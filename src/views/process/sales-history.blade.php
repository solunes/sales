@extends('master::layouts/admin-2')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="content-header-left col-md-9 col-12 mb-2">
    <div class="row breadcrumbs-top">
        <div class="col-12">
            <h2 class="content-header-title float-left mb-0">Historial de Ventas</h2>
            <div class="breadcrumb-wrapper col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url(config('customer.redirect_after_login')) }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Historial de Ventas</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Data list view starts -->
<section id="data-thumb-view" class="data-thumb-view-header">
  <!-- dataTable starts -->
  <div class="table-responsive">
      <table class="table data-thumb-view">
          <thead>
              <tr>
                  <th></th>
                  <th>N° ORDEN</th>
                  <th>FECHA DE PAGO</th> 
                  <th>VÍA DE PAGO</th>
                  <th>MONTO</th>
                  <th style="width:10%">VER FACTURA</th>
              </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              @foreach($item->sale_payments as $sale_payment)
                <?php $payment = $sale_payment->payment; ?>
                @if($payment)
                  <tr>
                    <td></td>
                    <td class="product-name">#{{ $payment->id }}</td>
                    <td class="product-name">{{ $payment->payment_date }}</td>
                    <td class="product-name">{{ $sale_payment->payment_method->name }}</td>
                    <td class="product-name">{{ $payment->currency->name }} {{ $payment->amount }}</td>
                    <td>
                      <div class="chip chip-warning">
                        @foreach($payment->payment_invoices as $payment_invoice)
                          <div class="chip-body">
                            <div class="chip-text"><a target="_blank" href="{{ $payment_invoice->invoice_url }}">Ver Factura</a></div>
                          </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                @endif
              @endforeach
            @endforeach

          </tbody>
      </table>
  </div>
  <!-- dataTable ends -->
</section>


@endsection

@section('script')
  <!--<script>
    new CBPFWTabs(document.getElementById('tabs'));
  </script>-->
@endsection