<script type="text/javascript">
  function queryShipping(){
    @if(config('business.pricing_rules'))
      <?php $real_order_amount = \Business::getSaleDiscount($total, $cart->coupon_code); ?>
      <?php $total = $real_order_amount; ?>
    @endif
    var order_cost = {{ $total }};
    var weight = {{ $weight }};
    var map_coordinates = $('#map_coordinates').val();
    var shipping_id = $('#shipping_id').val();
    @if(config('sales.delivery_country'))
      var country_id = $('#country_id').val();
    @else
      var country_id = 1;
    @endif
    var city_id = $('#city_id').val();
    $.ajax("{{ url('process/calculate-shipping') }}/" + shipping_id + "/" + country_id + "/" + city_id + "/" + weight + "/" + map_coordinates + "/" + {{ $agency->id }} , {
      success: function(data) {
        if(city_id!=data.shipping_city){
          var $el = $("#city_id");
          $el.empty(); // remove old options
          $.each(data.shipping_cities, function(key,value) {
            $el.append($("<option></option>").attr("value", value).text(key));
          });
        }
        updateOtherCity(data.other_city);
        if(data.shipping){
          var shipping_cost = parseFloat(data.shipping_cost);
          var total_cost = order_cost + shipping_cost;
          $(".shipping_cost").html(shipping_cost);
          $(".total_cost").html(total_cost);
        } else if(data.new_shipping_id) {
          var shipping_id = $('#shipping_id').val(data.new_shipping_id);
          queryShipping();
          alert('No se puede realizar un envío a esa ciudad por ese método de envío. Por lo tanto, cambiamos el canal de envío por uno disponible.');
        } else {
          //queryShipping();
          alert('No tenemos cobertura hasta la ubicación introducida. Introduzca una zona válida por favor.');
        }
        var $el = $("#shipping_date");
        $el.empty(); // remove old options
        $.each(data.shipping_dates, function(key,value) {
          $el.append($("<option></option>")
             .attr("value", value).text(value));
        });
        var $el = $("#shipping_time_id");
        $el.empty(); // remove old options
        $.each(data.shipping_times, function(key,value) {
          $el.append($("<option></option>")
             .attr("value", value).text(key));
        });
      }
    });
  }

  function updateOtherCity(active){
    if(active){
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

  @if(config('sales.ask_coordinates'))
    $(document).on('change', '#map_coordinates', function() {
      queryShipping();
    });
  @endif

  @if(config('sales.delivery')&&count($shipping_descriptions)>1)
    let shipping_changed = false;
    @foreach($shipping_descriptions as $key => $shipping)
      $('#click-accordion-shipping-{{ $key }}').on('click', function () {
        $('.shipping-active-icon').css({opacity:0});
        $('#heading{{ $shipping->id }} .shipping-active-icon').css({opacity:1});
        if(shipping_changed===false){
          shipping_changed = true;
          var shipping_id = $('#shipping_id').val({{ $shipping->id }});
        }
        shipping_changed = false;
        queryShipping();
        return false;
      })
    @endforeach
    $('#shipping_id').on('change', function () {
      if(shipping_changed===false){
        shipping_changed = true;
        $('.shipping-active-icon').css({opacity:0});
        $('#heading'+$(this).val()+' .shipping-active-icon').css({opacity:1});
      }
      shipping_changed = false;
    })
  @endif

  @if(count($payment_descriptions)>1)
    let payment_changed = false;
    @foreach($payment_descriptions as $key => $payment)
      $('#click-accordion-payment-{{ $key }}').on('click', function () {
        $('.payment-active-icon').css({opacity:0});
        $('#heading{{ $payment->id }} .payment-active-icon').css({opacity:1});
        if(payment_changed===false){
          payment_changed = true;
          var payment_id = $('#payment_id').val({{ $payment->id }});
        }
        payment_changed = false;
        return false;
      })
    @endforeach
    $('#payment_id').on('change', function () {
      if(payment_changed===false){
        payment_changed = true;
        $('.payment-active-icon').css({opacity:0});
        $('#heading'+$(this).val()+' .payment-active-icon').css({opacity:1});
      }
      payment_changed = false;
    })
  @endif
  
</script>