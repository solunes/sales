<div class="solunes-product">
  <div class="row">
    <div class="col-sm-6 col-product-image product-slider text-center">

      <div class="slider slider-for">
        <div class="each-slide-contain">
          <img src="{{ Asset::get_image_path('product-bridge-image','normal', $item->image )}}">
        </div>
        @if($item->model_type=='product'&&$item->product)
          @foreach($item->product->product_images as $subimage)
          <div class="each-slide-contain">
            <img src="{{ Asset::get_image_path('product-image-image','normal', $subimage->image )}}">
          </div>
          @endforeach
        @endif
      </div>
      <div class="slider slider-nav">
        <div class="each-slide-contain">
          <img src="{{ Asset::get_image_path('product-bridge-image','normal', $item->image )}}">
        </div>
        @if($item->model_type=='product'&&$item->product)
          @foreach($item->product->product_images as $subimage)
          <div class="each-slide-contain">
            <img src="{{ Asset::get_image_path('product-image-image','normal', $subimage->image )}}">
          </div>
          @endforeach
        @endif
      </div>

    </div>
    <div class="col-sm-6 col-product-detail">
      <div class="summary entry-summary">
        <h1 class="product_title entry-title">{{ $item->name }}</h1>
        @if($item->price>0)
          @if($item->promo_price>0)
            <p class="price">
              <!--<del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>900.00</span></del>-->
              <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{ $item->currency->name }}</span> {{ $item->promo_price }}</span></ins>
            </p>
          @else
            <p class="price">
                <!--<del><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>900.00</span></del>-->
                <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{ $item->currency->name }}</span> {{ $item->price }}</span></ins>
            </p>
          @endif
        @else
          <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"></span></span></ins>
        @endif
        <div class="woocommerce-product-details__short-description">
          <p>{{ $item->content }}</p>
        </div>
        <form class="cart" method="post" action="{{ url('process/add-cart-item') }}">
          <div class="row">
            @foreach($item->product_bridge_variation as $variation)
              <div class="col-sm-6">
                <div class="va-label">
                  <div class="va-separator clear"></div>
                  <label class="va-attribute-label">{{ $variation->label }}</label>
                  <div class="va-pickers">
                    <select id="variation_{{ $variation->id }}" name="variation_{{ $variation->id }}" class="c_select update-price">
                      @if($variation->id!=2)
                        <option value="0">Seleccione una opción</option>
                      @endif
                      @foreach($variation->variation_options as $variation_option)
                        <option value="{{ $variation_option->id }}">{{ $variation_option->name }} @if($variation_option->extra_price>0) + Bs. {{ $variation_option->extra_price }} @endif</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              @if($variation->id==2)
                <div id="detail-shirt" class="col-sm-6" style="display: none;">
                  <div class="va-label">
                    <div class="va-separator clear"></div>
                    <label class="va-attribute-label">Escriba su nombre</label>
                    <div class="va-pickers">
                      <input class="form-control" type="text" name="detail" placeholder="Ej: SIMON" />
                    </div>
                  </div>
                </div>
                <div id="detail-shirt-2" class="col-sm-6" style="display: none;">
                  <div class="va-label">
                    <div class="va-separator clear"></div>
                    <label class="va-attribute-label">Escriba su número</label>
                    <div class="va-pickers">
                      <input class="form-control" type="number" name="detail_1" placeholder="Ej: 10" min="1" max="99" />
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
          </div>
          <br>
          <div class="va-separator clear"></div>
          <div class="quantity-add-to-cart-wrapper">
            <div class="quantity buttons_added">
              <input type="button" value="-" class="minus">
              <input type="number" class="input-text qty text" step="1" min="1" max="10" name="quantity" value="1" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric">
              <input type="button" value="+" class="plus">
            </div>
          </div>
          <input type="hidden" name="product_id" value="{{ $item->id }}" />
          @if($item->promo_price>0)
            <input type="hidden" id="product_price" name="product_price" value="{{ $item->promo_price }}" />
          @else
            <input type="hidden" id="product_price" name="product_price" value="{{ $item->price }}" />
          @endif
          <br>
          <input type="submit" name="submit" value="Añadir al carrito" class="single_add_to_cart_button button alt" />
        </form>
        <!--<div class="social-icons">
          <ul class="list-unstyled list-social-icons">
            <li class="facebook"><a class="fa fa-facebook" href="http://www.facebook.com/sharer.php?u=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/" title=""></a></li>
            <li class="twitter"> <a class="fa fa-twitter" href="https://twitter.com/share?url=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/&amp;text=Black%20Gym%20Bag" title=""></a></li>
            <li class="google_plus"> <a class="fa fa-google-plus" href="https://plus.google.com/share?url=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/" title=""></a></li>
            <li class="pinterest"> <a class="fa fa-pinterest" href="https://pinterest.com/pin/create/bookmarklet/?media=https://demo2.chethemes.com/sportexx/wp-content/uploads/2015/04/04.png&amp;url=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/&amp;description=Black%20Gym%20Bag" title=""></a></li>
            <li class="digg"> <a class="fa fa-digg" href="http://digg.com/submit?url=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/&amp;title=Black%20Gym%20Bag" title=""></a></li>
            <li class="email"> <a class="fa fa-envelope" href="mailto:yourfriend@email.com?subject=Black%20Gym%20Bag&amp;body=https://demo2.chethemes.com/sportexx/product/black-gym-bag-2/" title=""></a></li>
          </ul>
        </div>-->
        <div class="product_meta">
          <span class="posted_in">Categorias: <a href="#" rel="tag">Bolivar</a>, <a href="#" rel="tag">Bolivarmania</a></span>
        </div>
      </div>
    </div>

  </div>
</div>