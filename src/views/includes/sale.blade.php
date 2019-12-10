<div class="row">
  <div class="col-lg-6 col-md-6">

    <div class="order-block">
      <h3>SU ORDEN</h3>
      @include('sales::includes.cart-summary', ['items'=>$sale->sale_items, 'order_amount'=>$sale->order_amount, 'deliveries'=>$sale->deliveries, 'total_amount'=>$sale->amount])

    </div>

  </div>  

  <div class="col-lg-6 col-md-6">
    @if(config('sales.ask_invoice')&&config('sales.sale_edit_invoice')&&$sale->lead_status=='sale')
      <h3>DATOS PARA FACTURA</h3>
      <div class="store-form">           
        <form action="{{ url('process/sale-update-nit') }}" method="post">
          <div class="row">
            <div class="col-md-6">
              <div class="checkout-form-list">
                <label>Razón Social</label>                   
                {!! Form::text('invoice_name', $sale->invoice_name) !!}
              </div>
            </div>
            <div class="col-md-6">
              <div class="checkout-form-list">
                <label>Número de NIT</label>                    
                {!! Form::text('invoice_nit', $sale->invoice_nit) !!}
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 right" style="margin-top: 7px;">
              <label>¿Guardar para futuras compras?</label>                    
            </div>
            <div class="col-md-6">
              {!! Form::select('save_for_all', ['1'=>'Si','0'=>'No'], '1') !!}
            </div>
          </div>
          {!! Form::hidden('sale_id', $sale->id) !!}
          <input class="btn btn-site" type="submit" value="ACTUALIZAR DATOS">
        </form>
      </div>
    @endif
    @if(config('sales.delivery')&&count($sale->sale_deliveries)>0)
      <h3>MÉTODO DE ENVÍO</h3>
      @foreach($sale->sale_deliveries as $delivery)
        <div class="store-form">           
          <h4>{{ mb_strtoupper($delivery->shipping->name, 'UTF-8') }}</h4>
          {!! $delivery->shipping->content !!}
        </div>
      @endforeach
    @endif
    @if($sale->lead_status=='sale')
      <h3>MÉTODO DE PAGO</h3>
      @foreach($sale_payments as $sale_payment)
        <div class="store-form">           
          <h4>{{ mb_strtoupper($sale_payment->payment_method->name, 'UTF-8') }}</h4>
          {!! $sale_payment->payment_method->content !!}
        </div>
        @include('payments::includes.sp-'.$sale_payment->payment_method->code)
      @endforeach
    @else
      <h3>COTIZACIÓN</h3>
      <div class="store-form">           
        <h4>COTIZACIÓN: #{{ $sale->id }}</h4>
        <p>A continuación, podrá descargar la cotización generada en PDF.</p>
        <a target="_blank" href="{{ \Asset::get_file('sale-quotation_file', $sale->quotation_file) }}" class="btn btn-site">Descargar Cotización</a>
      </div>
    @endif
  </div>  
</div>