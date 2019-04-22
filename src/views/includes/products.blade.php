<h1>Productos</h1>
@if(count($items)>0)
  <div class="solunes-products">
    <div class="row">
      @foreach($items as $product)
        @include('sales::includes.product-summary', ['col_size'=>'col-md-4'])
      @endforeach
    </div>
  </div>
@endif