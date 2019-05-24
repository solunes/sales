@if(config('sales.delivery')&&config('sales.delivery_city'))
  @if(config('sales.delivery_country'))
    <div class="col-md-12">
      <div class="checkout-form-list">
        <label>País <span class="required">*</span></label>
        {!! Form::select('country_id', $countries, $country_id, ['id'=>'country_id', 'class'=>'query_shipping']) !!}                   
      </div>
    </div>
  @endif
  <div class="col-md-12">
    <div class="checkout-form-list">
      <label>Ciudad <span class="required">*</span></label>
      {!! Form::select('city_id', $cities, $city_id, ['id'=>'city_id', 'class'=>'query_shipping']) !!}                   
    </div>
  </div>
  <div class="col-md-12 city_other">
    <div class="checkout-form-list">
      <label>Especifique la Otra Ciudad</label>                   
      {!! Form::text('city_other', NULL) !!}
    </div>
  </div>
@else
  {!! Form::hidden('city_id', config('sales.default_city'), ['id'=>'city_id', 'class'=>'query_shipping']) !!}
@endif
@if(!$auth)
  <div class="col-md-6">
    <div class="checkout-form-list">
      <label>Nombre <span class="required">*</span></label>                   
      {!! Form::text('first_name', NULL) !!}
    </div>
  </div>
  <div class="col-md-6">
    <div class="checkout-form-list">
      <label>Apellido <span class="required">*</span></label>                    
      {!! Form::text('last_name', NULL) !!}
    </div>
  </div>
@endif
@if(config('sales.ask_address'))
  <div class="col-md-12">
    <div class="checkout-form-list">
      <label>Dirección <span class="required">*</span></label>
      {!! Form::text('address', $address, ['placeholder'=>'Datos de zona, barrio, calle, número']) !!}
    </div>
  </div>
  <div class="col-md-12">
    <div class="checkout-form-list">                  
      {!! Form::text('address_extra', $address_extra, ['placeholder'=>'Otros detalles como color, referencias, etc. (Opcional)']) !!}
    </div>
  </div>
  @endif
@if(config('sales.ask_coordinates'))
  <p>En construcción, selector de mapa aqui.</p>
@endif
@if(!$auth)
  @if(config('sales.sales_email'))
    <div class="col-md-6">
      <div class="checkout-form-list">
        <label>Email <span class="required">*</span></label>                    
        {!! Form::text('email', NULL, ['placeholder'=>'Introduzca un correo electrónico']) !!}
      </div>
    </div>
  @endif
  @if(config('sales.sales_cellphone'))
    <div class="col-md-6">
      <div class="checkout-form-list">
        <label>Teléfono / Celular <span class="required">*</span></label>                   
        {!! Form::text('cellphone', NULL, ['placeholder'=>'Teléfono o celular']) !!}
      </div>
    </div>
  @endif
  @if(config('sales.sales_username'))
    <div class="col-md-6">
      <div class="checkout-form-list">
        <label>Carnet de Identidad <span class="required">*</span></label>                   
        {!! Form::text('username', NULL, ['placeholder'=>'Carnet de Identidad']) !!}
      </div>
    </div>
  @endif
@endif
@if(config('sales.ask_invoice'))
  <div class="col-md-6">
    <div class="checkout-form-list">
      <label>Número de NIT <span class="required">*</span></label>                   
      {!! Form::text('nit_number', $nit_number, ['spellcheck'=>'false','placeholder'=>'Número de NIT']) !!}
    </div>
  </div>
  <div class="col-md-6">
    <div class="checkout-form-list">
      <label>Razón Social <span class="required">*</span></label>                   
      {!! Form::text('nit_social', $nit_social, ['spellcheck'=>'false','placeholder'=>'Razón Social']) !!}
    </div>
  </div>
@endif
@if($user_cart_registration)
  @if(config('sales.delivery'))
    <div class="col-md-6">
      <div class="checkout-form-list">
        <label>Método de Envío <span class="required">*</span></label>                   
        {!! Form::select('shipping_id', $shipping_options, NULL, ['id'=>'shipping_id', 'class'=>'query_shipping']) !!}
      </div>
    </div>
  @endif
  <div class="col-md-6">
    <div class="checkout-form-list">
      <label>Método de Pago <span class="required">*</span></label>                    
      {!! Form::select('payment_method_id', $payment_options, NULL, ['id'=>'payment_id']) !!}
    </div>
  </div>
@endif
@if(!$auth)
  <div class="col-md-12">
    <div class="checkout-form-list">
      <p>Para facilitar sus compras a futuro, introduzca una contraseña y así se guardarán sus datos en una cuenta de usuario.</p>
      <label>Contraseña <span class="required">*</span></label>
      <input name="password" type="password" placeholder="Contraseña">  
    </div>
  </div>  
@endif