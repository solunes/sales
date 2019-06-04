@extends('master::layouts/admin')

@section('content')
  <h1>Realizar Devolución</h1>
  @if(!$sale_id)
    <h3>Primero, Seleccione una Venta</h3>
    {!! Form::open(['url'=>'admin/create-refund', 'method'=>'get']) !!}
    <div class="row">
      {!! Field::form_input($i, $dt, ['name'=>'initial_date', 'required'=>false, 'type'=>'date'], ['label'=>'Fecha Inicial', 'cols'=>6, 'class'=>'datepicker']) !!}
      {!! Field::form_input($i, $dt, ['name'=>'end_date', 'required'=>false, 'type'=>'date'], ['label'=>'Fecha Final', 'cols'=>6, 'class'=>'datepicker']) !!}
    </div>
    <div class="row">
        {!! Field::form_input($i, $dt, ['name'=>'barcode', 'required'=>true, 'type'=>'string'], ['label'=>'Código de Barras de Producto', 'cols'=>4, 'placeholder'=>'Utilice el lector de código de barras']) !!}
        {!! Field::form_input($i, $dt, ['name'=>'product_id', 'required'=>true, 'type'=>'select', 'options'=>$products], ['label'=>'Seleccionar un Producto', 'cols'=>4]) !!}
        {!! Field::form_input($i, $dt, ['name'=>'place_id', 'required'=>true, 'type'=>'select', 'options'=>$places], ['label'=>'Seleccionar Sucursal', 'cols'=>4]) !!}
    </div>
    {!! Form::submit('Filtrar', array('class'=>'btn btn-site')) !!}

    {!! Form::close() !!}
    <table class="table" id="sales">
      <thead>
        <tr class="title">
          <td># Venta</td>
          <td>Vendedor</td>
          <td>Sucursal</td>
          <td>Nº de Productos</td>
          <td>Monto de Compra</td>
          <td>Datos de Factura</td>
          <td>Fecha y Hora</td>
          <td>Crear Devolución</td>
        </tr>
      </thead>
      <tbody>
        @foreach($sales as $sale)
          <tr>
            <td>#{{ $sale->id }}</td>
            <td>{{ $sale->user->name }}</td>
            <td>{{ $sale->place->name }}</td>
            <td>{{ count($sale->sale_items).' productos' }}</td>
            <td>{{ $sale->amount.' '.$sale->currency->name }}</td>
            <td>{{ $sale->invoice_nit.' - '.$sale->invoice_name }}</td>
            <td>{{ $sale->created_at }}</td>
            <td><a href="{{ url('admin/create-refund/'.$sale->id) }}">Crear Devolución</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    {!! Form::open(['url'=>'admin/create-refund', 'method'=>'post', 'id'=>'create-refund']) !!}
      <h3>Lista de Productos</h3>
      <h4>Código de Venta: #{{ $sale->id }}</h4>
      <table class="table" id="products">
        <thead>
          <tr class="title">
            <td>Nombre Producto</td>
            <td>Precio</td>
            <td>Cantidad Inicial</td>
            <td>Monto Inicial</td>
            <td>Cantidad Devuelta</td>
            <td>Monto Devuelto</td>
          </tr>
        </thead>
        <tbody>
          @foreach($sale->sale_items as $item)
            <tr>
              <td>
                <input type="hidden" name="sale_item_id[{{ $item->id }}]" class="sale_item_id" value="{{ $item->id }}" />
                <input type="hidden" name="product_id[{{ $item->id }}]" class="product_id" value="{{ $item->product_id }}" />
                <input type="text" name="product_name[{{ $item->id }}]" class="product_name form-control input-lg" value="{{ $item->product->name }}" readonly />
                <input type="hidden" name="currency[{{ $item->id }}]" class="currency form-control input-lg" value="{{ $item->currency_id }}" />
              </td>
              <td><input type="text" name="price[{{ $item->id }}]" class="price form-control input-lg" value="{{ $item->price }}" rel="{{ $item->id }}"  readonly /></td>
              <td><input type="text" name="initial_quantity[{{ $item->id }}]" class="initial_quantity form-control input-lg"  value="{{ $item->quantity }}" readonly /></td>
              <td><input type="text" name="initial_amount[{{ $item->id }}]" class="initial_amount form-control input-lg"  value="{{ $item->total }}" readonly /></td>
              <td><input type="text" name="refund_quantity[{{ $item->id }}]" class="refund_quantity form-control input-lg" rel="{{ $item->id }}" data-max_quantity="{{ $item->quantity }}" value="0" /></td>
              <td><input type="text" name="refund_amount[{{ $item->id }}]" class="refund_amount form-control input-lg" rel="{{ $item->id }}" value="0" /></td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="right">TOTAL</td>
            <td colspan="1">{!! Form::text('amount', 0, ['class'=>'form-control input-lg', 'id'=>'amount', 'readonly'=>true]) !!}</td>
          </tr>
        </tfoot>
      </table>
      <div class="row">
        {!! Field::form_input($i, $dt, ['name'=>'reference', 'required'=>true, 'type'=>'string'], ['label'=>'Referencia o Motivo de Devolución', 'cols'=>12]) !!}
      </div>
      {!! Form::hidden('sale_id', $sale->id) !!}
      {!! Form::hidden('action_form', $action) !!}
      {!! Form::hidden('model_node', $model) !!}
      {!! Form::hidden('lang_code', \App::getLocale()) !!}
      {!! Form::submit('Finalizar Devolución', array('class'=>'btn btn-site')) !!}

    {!! Form::close() !!}
  @endif

@endsection
@section('script')
  @include('master::scripts.select-js')
  @include('store::scripts.barcode-refund-js')
@endsection