<script type="text/javascript">
  function queryShipping(){
    var order_cost = {{ $total }};
    var weight = {{ $weight }};
    var shipping_id = $('#shipping_id').val();
    var city_id = $('#city_id').val();
    $.ajax("{{ url('process/calculate-shipping') }}/" + shipping_id + "/" + city_id + "/" + weight, {
      success: function(data) {
        if(data.shipping){
          var shipping_cost = parseFloat(data.shipping_cost);
          var total_cost = order_cost + shipping_cost;
          $(".shipping_cost").html(shipping_cost);
          $(".total_cost").html(total_cost);
        } else {
          var shipping_id = $('#shipping_id').val(data.new_shipping_id);
          queryShipping();
          alert('No se puede realizar un envío a esa ciudad por ese método de envío. Por lo tanto, le cambiamos a Unibol Courier.');
        }
      }
    });
  }

  function updateOtherCity(){
    var city_id = $('#city_id').val();
    console.log('City ID: ' + city_id)
    if(city_id==13){
      $('.city_other').fadeIn();
    } else {
      $('.city_other').fadeOut();
    }
  }

  $( document ).ready(function() {
    @if(config('sales.delivery'))
      queryShipping();
    @endif
    @if(config('sales.delivery')&&config('sales.delivery_city'))
      updateOtherCity();
    @endif
  });

  @if(config('sales.delivery'))
    $(document).on('change', 'select.query_shipping', function() {
      queryShipping();
    });
  @endif

  @if(config('sales.delivery')&&config('sales.delivery_city'))
    $(document).on('change', 'select#city_id', function() {
      updateOtherCity();
    });
  @endif

  @if(config('sales.delivery')&&count($shipping_descriptions)>1)
    @foreach($shipping_descriptions as $key => $shipping)
      $('#accordion-shipping #collapse-shipping-{{ $key }}').on('show.bs.collapse', function () {
        $('.shipping-active-icon').css({opacity:0});
        $('#heading{{ $key }} .shipping-active-icon').css({opacity:1});
        var shipping_id = $('#shipping_id').val({{ $shipping->id }});
      })
    @endforeach
  @endif

  @if(count($payment_descriptions)>1)
    @foreach($payment_descriptions as $key => $payment)
      $('#accordion-payment #collapse-payment-{{ $key }}').on('show.bs.collapse', function () {
        $('.payment-active-icon').css({opacity:0});
        $('#collapse-payment-{{ $key }} .payment-active-icon').css({opacity:1});
        var payment_id = $('#payment_id').val({{ $payment->id }});
      })
    @endforeach
  @endif
  
</script>