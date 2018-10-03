<div class="row">
  <div class="col-lg-6 col-md-6">

    <div class="order-block">
      <h3>SU ORDEN</h3>
      <div class="order-summary">
        <table class="table table-bordered-top table-responsive table-store">
          <thead>
            <tr>
              <th class="product-name">Producto</th>
              <th class="product-total">Total</th>
            </tr>             
          </thead>
          <tbody>
            @if(count($cart->cart_items)>0)
              @foreach($cart->cart_items as $item)
                <tr class="cart_item">
                  <td class="product-name">
                    {{ $item->product_bridge->name }} - {{ $item->detail }} <strong class="product-quantity">(x{{ $item->quantity }})</strong>
                  </td>
                  <td class="strong">Bs. {{ $item->total_price }}</td>
                </tr>
              @endforeach
            @endif
          </tbody>
          <tfoot>
            <tr class="cart-subtotal">
              <th>SUBTOTAL</th>
              <th>Bs. <span class="amount">{{ $total }}</span></th>
            </tr>
            <tr>
              <td>Costo de Envío ({{ round($weight, 1) }} kg.)</td>
              <td class="strong">Bs. <span class="shipping_cost">0</span></td>
            </tr>
            <tr class="order-total">
              <th>Precio Total</th>
              <th>Bs. <span class="amount total_cost">{{ $total }}</span></th>
            </tr>               
          </tfoot>
        </table>
      </div>

      @if(config('sales.delivery')&&count($shipping_descriptions)>0)
        <h3>MÉTODOS DE ENVÍO</h3>
        <div class="payment-method">
          <div class="payment-accordion">
            <div class="panel-group" id="accordion-shipping" role="tablist" aria-multiselectable="true">
              @foreach($shipping_descriptions as $key => $shipping)
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="heading{{ $key }}"><h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-shipping" href="#collapse-shipping-{{ $key }}" aria-expanded="true" aria-controls="collapse{{ $key }}">{{ $shipping->name }}</a>
                  </h4></div>
                  <div id="collapse-shipping-{{ $key }}" class="panel-collapse collapse @if($key==0) in @endif " role="tabpanel" aria-labelledby="heading{{ $key }}">
                    <div class="panel-body">{!! $shipping->content !!}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif

      @if(count($payment_descriptions)>0)
        <h3>MÉTODOS DE PAGO</h3>
        <div class="payment-method">
          <div class="payment-accordion">
            <div class="panel-group" id="accordion-payment" role="tablist" aria-multiselectable="true">
              @foreach($payment_descriptions as $key => $payment)
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="heading{{ $key }}"><h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion-payment" href="#collapse-payment-{{ $key }}" aria-expanded="true" aria-controls="collapse{{ $key }}">{{ $payment->name }}</a>
                  </h4></div>
                  <div id="collapse-payment-{{ $key }}" class="panel-collapse collapse @if($key==0) in @endif " role="tabpanel" aria-labelledby="heading{{ $key }}">
                    <div class="panel-body">{!! $payment->content !!}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif

    </div>

  </div>  
  <div class="col-lg-6 col-md-6">
    @if(!$auth)
      <h3>INICIAR SESIÓN</h3>
      <div class="store-form">
        <p>Si ya tiene una cuenta de usuario, inicie sesión con su usuario y contraseña. Si no recuerda su contraseña, puede <a href="{{ url('') }}">recuperarla aquí</a>.</p>
        <?php request()->session()->put('url.intended', request()->url()); ?>
        <form action="{{ url('auth/login') }}" method="post">
          <div class="checkout-form-list">
            <label>Email o Celular <span class="required">*</span></label>
            {!! Form::text('user', NULL) !!}
          </div>
          <div class="checkout-form-list">
            <label>Contraseña  <span class="required">*</span></label>
            {!! Form::password('password', NULL) !!}
          </div>
          <p class="form-row">          
            <input class="btn btn-site" type="submit" value="INICIAR SESIÓN">
          </p>
        </form>
      </div>
    @endif
    <form action="{{ url('process/finish-sale') }}" method="post">
      @if(!$auth)
        <h3>REGISTRO DE CLIENTE</h3>
      @else
        <h3>DATOS DE ENVÍO</h3>
      @endif
      <div class="store-form">           
        <div class="row">
          @include('sales::includes.user-registration', ['user_cart_registration'=>true,'cities'=>$cities,'city_id'=>$city_id,'auth'=>$auth,'address'=>$address,'address_extra'=>$address_extra,'shipping_options'=>$shipping_options,'payment_options'=>$payment_options])
          <div class="col-md-12">
            <input name="cart_id" type="hidden" value="{{ $cart->id }}">
            <input class="btn btn-site" type="submit" value="FINALIZAR COMPRA">
          </div>
        </div>   
      </div>
    </form>                   
  </div>  
</div>