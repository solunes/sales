@extends('master::layouts/admin')

@section('content')
  <h2>Envíos Pendientes</h2>
  @if(count($items)>0)
    <p>A continuación verá un listado de los envíos pendientes por hacer.</p>
    <table class="table">
      <thead>
        <tr class="title">
          <td>Vendedor</td>
          <td>Agencia</td>
          <td>Cliente</td>
          <td>Nombre de Venta</td>
          <td>Monto</td>
          <td>Ver Detalle</td>
          <td>Marcar como Enviado</td>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
          <tr>
            <td>{{ $item->user->name }}</td>
            <td>{{ $item->agency->name }}</td>
            <td>{{ $item->customer->name }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->currency->name }} {{ $item->amount }}</td>
            <td class="restore">
              <a href="{{ url('admin/model/sale/view/'.$item->id) }}">Ver</a>
            </td>
            <td class="edit">
              <a href="{{ url('admin/sale-delivered/'.$item->id) }}" onclick="return confirm("¿Está seguro que desea marcar este pedido como enviado?.");">Marcar como Enviado</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <p>No tiene cotizaciones en su historial.</p>
  @endif

@endsection