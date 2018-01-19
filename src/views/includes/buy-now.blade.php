<div class="row">
   <div class="col-md-12 col-sm-12 col-xs-12">
    <form action="{{ url('process/buy-now') }}" method="post">       
      @include('sales::includes.buy-now-table')
      <div class="row">
        <div class="col-md-9 col-sm-7 col-xs-12">
          <div class="buttons-cart">
            <input class="btn btn-site" type="submit" value="Confirmar Compra">
          </div>
        </div>
      </div>
    </form> 
  </div>
</div>