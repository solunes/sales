<table class="table table-bordered-top table-responsive table-store">
	<thead>
		<tr>
			<th class="product-name">Producto</th>
			<th class="product-total">Total</th>
		</tr>             
	</thead>
	<tbody>
	  @if(count($items)>0)
		@foreach($items as $item)
		  <tr class="cart_item">
			<td>
				{{ $item->detail }} <strong>(x{{ $item->quantity }})</strong>
			</td>
			<td class="strong">
				<span class="amount">{{ $item->currency->name }} {{ $item->total_price }}</span>
			</td>
		  </tr>
		@endforeach
	  @endif
	</tbody>
	<tfoot>
		<tr class="cart-subtotal">
			<th>Subtotal</th>
			<th>{{ $item->currency->name }} {{ $order_amount }}</th>
		</tr>
		@foreach($deliveries as $delivery)
		<tr>
			<td>Costo de Envío ({{ $delivery->total_weight }} kg.)</td>
			<td class="strong">{{ $item->currency->name }} {{ $delivery->shipping_cost }}</td>
		</tr>
		@endforeach
		<tr class="order-total">
			<th>Precio Total</th>
			<th>{{ $item->currency->name }} {{ $total_amount }}</th>
		</tr>               
	</tfoot>
</table>