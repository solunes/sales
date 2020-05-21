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
@if(config('sales.ask_address')&&!$quotation)
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
@if(config('sales.ask_coordinates')&&!$quotation)
  <div class="col-md-12">
    @if($map_coordinates['type']=='default')
      <h3>Seleccionar Ubicación</h3>
      <p>Por favor, seleccione su ubicación exacta en el mapa.</p>
    @else
      <h3>Confirmar Ubicación</h3>
      <p>Por favor, valide que la ubicación seleccionada en el mapa sea correcta. Caso contrario, puede seleccionar una nueva ubicación para el envío.</p>
    @endif
    {!! \Field::generate_map_field('map_coordinates', 'map', [], ['id'=>'map_coordinates'], $map_coordinates['latitude'].';'.$map_coordinates['longitude'], 'edit'); !!}
    <br>
  </div>
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
@elseif($auth)
  @if(config('sales.sales_cellphone'))
    <div class="col-md-6">
      <div class="checkout-form-list">
        <label>Teléfono / Celular <span class="required">*</span></label>                   
        {!! Form::text('cellphone', auth()->user()->cellphone, ['placeholder'=>'Teléfono o celular']) !!}
      </div>
    </div>
  @endif
@endif
@if(config('sales.ask_invoice')&&!$quotation)
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
    @if(config('sales.delivery_select_day'))
      <div class="col-md-6">
        <div class="checkout-form-list">
          <label>Seleccionar Fecha de Entrega <span class="required">*</span></label>                   
          {!! Form::select('shipping_date', $shipping_dates, NULL, ['id'=>'shipping_date']) !!}
        </div>
      </div>
    @endif
    @if(config('sales.delivery_select_hour'))
      <div class="col-md-6">
        <div class="checkout-form-list">
          <label>Seleccionar Hora <span class="required">*</span></label>                   
          {!! Form::select('shipping_time_id', $shipping_times, NULL, ['id'=>'shipping_time_id']) !!}
        </div>
      </div>
    @endif
  @endif
  <div class="col-md-6"  @if($quotation) style="opacity: 0; visibility: hidden; height: 0;" @endif>
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