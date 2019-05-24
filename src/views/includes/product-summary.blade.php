<div class="{{ $col_size }} col-sm-3">
  <div class="solunes-product-item">
    <div class="solunes-product-item wow fadeInUp">
      <div class="solunes-product-image-actions" style="height: 300px;">
        <a href="{{url('producto/'.$product->slug)}}" class="solunes-product-image">
          @if($product->image)
            <img src="{{ Asset::get_image_path('product-bridge-image', 'thumb', $product->image) }}" class="img-responsive" alt="{{ $product->name }}" />
          @else
            <img src="{{ asset('assets/admin/img/no-image.jpg') }}" class="img-responsive" alt="{{ $product->name }}" />
          @endif
        </a>
      </div>
      <a href="{{url('producto/'.$product->slug)}}">
        <h2>{{ $product->name }}</h2>
        <span class="solunes-price"><span class="solunes-price-amount amount"><span class="solunes-price-currency">{{ $sale->currency->name }}</span>&nbsp;{{ $product->price }}</span></span>
      </a>
      <div class="solunes-product-actions">
        <div class="solunes-group-product-actions">
          @if(count($product->product_bridge_variation)==0)
            <a href="{{ url('process/add-cart-item/'.$product->id) }}" class="btn btn-site">Añadir al carrito</a>
          @else
            <a href="{{ url('producto/'.$product->slug) }}" class="btn btn-site">Añadir al carrito</a>
          @endif
          <a href="{{ url('producto/'.$product->slug) }}" class="btn btn-site">Ver producto</a>
        </div>
      </div>
    </div>
  </div>
</div>