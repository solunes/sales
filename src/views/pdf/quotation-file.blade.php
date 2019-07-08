<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://fonts.googleapis.com/css?family=Oswald&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
    <style>
      body { font-family: 'Montserrat', sans-serif; }
      h1 { font-family: 'Oswald', sans-serif; }
      table { width: 100%; text-align: center; border-top: 1px solid #ddd; border-left: 1px solid #ddd; }
      table thead, table tfoot { font-weight: bold; }
      table td { border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; padding: 10px; }
      .col-sm-6 { width: 49%; display: inline-block; }
      .block { display: inline-block; margin-left: 1%; margin-right: 1%; width: auto; margin-bottom: 30px; text-align: center; width: 30%; }
      .block img { margin-bottom: 10px; }
    </style>
</head>
<body style="margin:0; padding:0;">
  <h1>DETALLES DE LA COTIZACIÓN</h1>
  <p><strong>Titular: </strong> {{ $item->customer->name }}</p>
  <p><strong>Número de Cotización: </strong> #{{ $item->id }}</p>
  <p><strong>Monto Total: </strong> {{ $item->currency->name }} {{ $item->amount }}</p>
  <p><strong>Detalle:</strong> </p>
  <table>
    <thead>
      <tr>
        <td>#</td>
        <td>Item</td>
        <td>Precio</td>
        <td>Cantidad</td>
        <td>Total</td>
      </tr>
    </thead>
    <tbody>
      @foreach($item->sale_items as $key => $sale_item)
      <tr>
        <td>{{ $key+1 }}</td>
        <td>{{ $sale_item->detail }}</td>
        <td>{{ $sale_item->price }}</td>
        <td>{{ $sale_item->quantity }}</td>
        <td>{{ $sale_item->currency->name }} {{ $sale_item->total }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>TOTAL</td>
        <td>{{ $item->currency->name }} {{ $item->amount }}</td>
      </tr>
    </tfoot>
  </table>
  <br>
</body>
</html>