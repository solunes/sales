<table class="table table-bordered table-responsive table-store">
  <thead>
    <tr>
      <th class="product-thumbnail">Imagen</th>
      <th class="product-name">Producto</th>
      <th class="product-price">Precio Unitario</th>
      <th class="product-quantity">Cantidad</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="product-thumbnail"><a target="_blank" href="{{ url($product->internal_url) }}">
        {!! Asset::get_image('product-bridge-image', 'subdetail', $product->image) !!}
      </a></td>
      <td class="product-name"><a target="_blank" href="{{ url($product->internal_url) }}">{{ $product->name }}</a></td>
      <td class="product-price"><span class="amount">{{ $product->currency->name }} {{ $product->real_price }}</span></td>
      <td class="product-quantity">
        <input name="quantity" type="number" value="1">
        <input name="product_id" type="hidden" value="{{ $product->id }}">
      </td>
    </tr>
  </tbody>
</table>
