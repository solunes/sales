<div class="table-content table-responsive">
  <table>
    <thead>
      <tr>
        <th class="product-thumbnail">Imagen</th>
        <th class="product-name" style="max-width: 200px;">Producto</th>
        <th class="product-price">Precio</th>
        @if(config('payments.sfv_version')>1||config('payments.discounts')){
        <th class="product-price">Precio c/Desc</th>
        @endif
        <th class="product-quantity">Cantidad</th>
        <th class="product-subtotal">Total</th>
        @if($delete)
          <th class="product-remove">Remover</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @foreach($items as $item)
        <tr>
          <td class="product-thumbnail"><a target="_blank" href="{{ url($item->product_bridge->internal_url) }}">
            {!! Asset::get_image('product-bridge-image', 'subdetail', $item->product_bridge->image) !!}
          </a></td>
          <td class="product-name" style="max-width: 200px;"><a target="_blank" href="{{ url($item->product_bridge->internal_url) }}">{{ $item->detail }}</a></td>
          <td class="product-price"><span class="amount">{{ $item->currency->name }} {{ $item->price }}</span></td>
          @if(config('payments.sfv_version')>1||config('payments.discounts')){
          <td class="product-price"><span class="amount"> @if($item->discount_price>0) {{ $item->currency->name }} {{ $item->discount_price }} @endif </span></td>
          @endif
          <td class="product-quantity">
            @if($editable)
              <input name="quantity[{{ $item->id }}]" type="number" value="{{ $item->quantity }}">
              <input name="product_id[{{ $item->id }}]" type="hidden" value="{{ $item->id }}">
            @else
              <span class="amount">{{ $item->quantity }}</span>
            @endif
          </td>
          <td class="product-subtotal">{{ $item->currency->name }} {{ $item->total_price }}</td>
          @if($delete)
            <td class="product-remove"><a href="#" class="delete"><i class="fa fa-times"></i></a></td>
          @endif
        </tr>
      @endforeach
    </tbody>
  </table>
</div>