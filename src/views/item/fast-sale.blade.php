@extends('master::layouts/admin')

@section('content')
  <h1>Agregar Productos</h1>
  {!! Form::open(['url'=>'admin/create-fast-sale', 'method'=>'post', 'id'=>'create-sale']) !!}
    <p id="notification-bar"></p>
    <div class="row">
      @if(config('business.product_barcode'))
        <div class="col-sm-1 hidden-xs icon"><i class="fa fa-barcode"></i></div>
        {!! Field::form_input($i, $dt, ['name'=>'barcode', 'required'=>true, 'type'=>'string'], ['label'=>'Introduzca el código de barras o utilce el lector', 'cols'=>5]) !!}
      @endif
      <div class="col-sm-1 hidden-xs icon"><i class="fa fa-cart-plus"></i></div>
      {!! Field::form_input($i, $dt, ['name'=>'search-product', 'required'=>true, 'type'=>'select', 'options'=>$products], ['label'=>'Seleccione un producto ', 'cols'=>5]) !!}
    </div>
    <table class="table" id="products">
      <thead>
        <tr class="title">
          <td>Nombre Producto</td>
          <td>Precio</td>
          <td>Cantidad</td>
          <td>Subtotal</td>
          <td>X</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <input type="hidden" name="product_id[]" class="product_id form-control input-lg" />
            <input type="text" name="product_name[]" class="product_name form-control input-lg" readonly />
            <input type="hidden" name="currency[]" class="currency form-control input-lg" />
          </td>
          <td>
            @if(config('sales.editable_price'))
              <input type="text" name="price[]" class="price form-control input-lg" />
            @else
              <input type="text" name="price[]" class="price form-control input-lg" readonly />
            @endif
          </td>
          <td><input type="text" name="quantity[]" class="quantity form-control input-lg" rel="" /></td>
          <td><input type="text" name="final_price[]" class="final_price form-control input-lg" rel="" readonly /></td>
          <td><a class="delete_row" href="#">X</a></td>
        </tr>
      </tbody>
      <tfoot>
        <tr id="shipping_cost_row">
          <td colspan="3" class="right">Costo de Envío en Bs.</td>
          <td colspan="1">{!! Form::text('shipping_cost', 0, ['class'=>'form-control input-lg', 'id'=>'shipping_cost']) !!}</td>
        </tr>
        <tr>
          <td colspan="3" class="right total">TOTAL</td>
          <td colspan="1">{!! Form::text('amount', 0, ['class'=>'form-control input-lg', 'id'=>'amount', 'readonly'=>true]) !!}</td>
        </tr>
      </tfoot>
    </table>
    <h1>Detalles de Venta</h1>
    <div class="row">
      <div class="col-sm-8">
        <div class="row">
          <div class="col-xs-1 hidden-xs icon"><i class="fa fa-building"></i></div>
          {!! Field::form_input($i, $dt, ['name'=>'agency_id', 'required'=>true, 'preselected'=>true, 'type'=>'select', 'options'=>$agencies], ['label'=>'Seleccione la Agencia', 'cols'=>5]) !!}
          <div class="col-xs-1 hidden-xs icon"><i class="fa fa-tags"></i></div>
          {!! Field::form_input($i, $dt, ['name'=>'payment_method_id', 'required'=>true, 'preselected'=>true, 'type'=>'select', 'options'=>$payment_methods], ['label'=>'Método de Pago', 'cols'=>5]) !!}
        </div><br>
        <div class="row">
          <div class="col-xs-1 hidden-xs icon"><i class="fa fa-user"></i></div>
          {!! Field::form_input($i, $dt, ['name'=>'invoice_number', 'required'=>true, 'type'=>'string'], ['label'=>'NIT de Cliente', 'cols'=>5]) !!}
          <div class="col-xs-1 hidden-xs icon"><i class="fa fa-user"></i></div>
          {!! Field::form_input($i, $dt, ['name'=>'invoice_name', 'required'=>true, 'type'=>'string'], ['label'=>'Nombre de Cliente', 'cols'=>5]) !!}
        </div>
      </div>
      <div class="col-sm-4">
        @if(config('sales.sales_detail'))
          <div class="row">
            <div class="col-xs-2 hidden-xs icon"><br><br><i class="fa fa-pencil"></i></div>
            {!! Field::form_input($i, $dt, ['name'=>'detail', 'required'=>true, 'type'=>'text'], ['label'=>'Detalle', 'cols'=>10, 'rows'=>8, 'placeholder'=>'Anote cualquier detalle referente a la venta']) !!}
          </div>
        @endif
      </div>
    </div>
    {!! Form::hidden('action_form', $action) !!}
    {!! Form::hidden('model_node', $model) !!}
    {!! Form::hidden('lang_code', \App::getLocale()) !!}
    {!! Form::submit('Generar Venta', array('class'=>'btn btn-site')) !!}

  {!! Form::close() !!}
@endsection
@section('script')
  @include('master::scripts.select-js')
  @include('sales::scripts.barcode-sale-js', ['quotation'=>false])
@endsection