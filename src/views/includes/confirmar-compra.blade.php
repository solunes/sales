<div class="row">
 <div class="col-md-12 col-sm-12 col-xs-12">
  <form action="{{ url('process/update-cart') }}" method="post">       
    @include('store::includes.cart-full', ['items'=>$cart->cart_items, 'editable'=>true, 'delete'=>true])
    <div class="row">
      <div class="col-md-9 col-sm-7 col-xs-12">
        <div class="buttons-cart">
          <input type="submit" value="Actualizar Carro">
          <a href="{{ url('productos') }}">Seguir comprando</a>
        </div>
      </div>
      <div class="col-md-3 col-sm-5 col-xs-12">
        <div class="cart_totals">
          <!--<h2>TOTAL</h2>-->
          <table>
            <tbody>
              <!--<tr class="cart-subtotal">
                <th>Subtotal</th>
                <td><span class="amount">Bs. 1000</span></td>
              </tr>-->
              <tr class="order-total">
                <th>Total</th>
                <td>
                  <strong><span class="amount">Bs. {{ $total }}</span></strong>
                </td>
              </tr>                     
            </tbody>
          </table>
          <div class="wc-proceed-to-checkout">
            <a href="{{ url('process/finalizar-compra/'.$cart->id) }}">Confirmar Compra</a>
          </div>
        </div>
      </div>
    </div>
  </form> 
</div>
</div>