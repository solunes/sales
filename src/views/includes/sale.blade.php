<div class="row">
  <div class="col-lg-6 col-md-6">

    <div class="order-block">
      <h3>SU ORDEN</h3>
      @include('sales::includes.cart-summary', ['items'=>$sale->sale_items, 'order_amount'=>$sale->order_amount, 'deliveries'=>$sale->deliveries, 'total_amount'=>$sale->amount])

    </div>

  </div>  

  <div class="col-lg-6 col-md-6">
    @if(config('sales.ask_invoice'))
      <h3>DATOS PARA FACTURA</h3>
      <form action="{{ url('process/sale-update-nit') }}" method="post">
        <div class="row">
          <div class="col-md-6">
            <div class="checkout-form-list">
              <label>Razón Social</label>                   
              {!! Form::text('nit_social', $sale->invoice_name) !!}
            </div>
          </div>
          <div class="col-md-6">
            <div class="checkout-form-list">
              <label>Número de NIT</label>                    
              {!! Form::text('nit_number', $sale->invoice_nit) !!}
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="checkout-form-list">
              <label>¿Desea guardar estos datos para futuras compras?</label>                    
              {!! Form::checkbox('save_for_all', 'true', true) !!}
            </div>
          </div>
        </div>
        {!! Form::hidden('sale_id', $sale->id) !!}
        <input class="btn btn-site" type="submit" value="ACTUALIZAR DATOS">
      </form>
    @endif
    @if(config('sales.delivery'))
      <h3>MÉTODO DE ENVÍO</h3>
      @foreach($sale->sale_deliveries as $delivery)
        <div class="store-form">           
          <h4>{{ mb_strtoupper($delivery->shipping->name, 'UTF-8') }}</h4>
          {!! $delivery->shipping->content !!}
        </div>
      @endforeach
    @endif
    <h3>MÉTODO DE PAGO</h3>
    @foreach($sale_payments as $sale_payment)
      <div class="store-form">           
        <h4>{{ mb_strtoupper($sale_payment->payment_method->name, 'UTF-8') }}</h4>
        {!! $sale_payment->payment_method->content !!}
      </div>
      @include('payments::includes.sp-'.$sale_payment->payment_method->code)
    @endforeach
  </div>  
</div>