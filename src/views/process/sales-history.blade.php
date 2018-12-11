@extends('layouts/master')
@include('helpers.meta')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/sales/store.css') }}">
@endsection

@section('content')
<div class="container solunes-store">
  <h1>Historial de Ventas</h1>
  @if(count($items)>0)
    <div class="payment-history">

      <div class="row">
        <div class="payment-table" style="width:100%">
          <table style="width:100%">
            <tr>
              <th>N° ORDEN</th>
              <th>FECHA DE PAGO</th> 
              <th>VÍA DE PAGO</th>
              <th>MONTO</th>
              <th style="width:10%">VER FACTURA</th>
            </tr>
            @foreach($items as $item)
              @foreach($item->sale_payments as $sale_payment)
                <?php $payment = $sale_payment->payment; ?>
                @if($payment)
                  <tr class="each">
                    <td class="border-site">#{{ $item->id }}</td>
                    <td>{{ $payment->payment_date }}</td> 
                    <td>Pagos TT</td>
                    <td>Bs. {{ $payment->amount }}</td>
                    <td class="icon-cell">
                      @foreach($payment->payment_invoices as $payment_invoice)
                        <a href="{{ $payment_invoice->invoice_url }}">
                          VER <i class="fa fa-plus-circle"></i>
                        </a>
                      @endforeach
                    </td>
                  </tr>
                @endif
              @endforeach
            @endforeach
          </table>
        </div>
      </div>

    </div>
  @else
    <p>Actualmente no se encontraron carros de compra pendientes en su cuenta.</p>
  @endif
</div><!-- End container  -->
@endsection

@section('script')
  <!--<script>
    new CBPFWTabs(document.getElementById('tabs'));
  </script>-->
@endsection