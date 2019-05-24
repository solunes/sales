<script type="text/javascript">
  function queryShipping(){
    var order_cost = {{ $total }};
    var weight = {{ $weight }};
    var shipping_id = $('#shipping_id').val();
    if(config('sales.delivery_country')){
      var country_id = $('#country_id').val();
    } else {
      var country_id = 1;
    }
    var city_id = $('#city_id').val();
    $.ajax("{{ url('process/calculate-shipping') }}/" + shipping_id + "/" + country_id + "/" + city_id + "/" + weight, {
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
        } else {
          var shipping_id = $('#shipping_id').val(data.new_shipping_id);
          $('#accordion-shipping .panel-collapse.in').removeClass('in');
          $('#accordion-shipping #collapse-shipping-'+$(this).val()).collapse('show');
          queryShipping();
          alert('No se puede realizar un envío a esa ciudad por ese método de envío. Por lo tanto, le cambiamos a Unibol Courier.');
        }
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

  @if(config('sales.delivery')&&count($shipping_descriptions)>1)
    let shipping_changed = false;
    @foreach($shipping_descriptions as $key => $shipping)
      $('#accordion-shipping #collapse-shipping-{{ $shipping->id }}').on('show.bs.collapse', function () {
        $('.shipping-active-icon').css({opacity:0});
        $('#heading{{ $shipping->id }} .shipping-active-icon').css({opacity:1});
        if(shipping_changed===false){
          shipping_changed = true;
          var shipping_id = $('#shipping_id').val({{ $shipping->id }});
        }
        shipping_changed = false;
      })
    @endforeach
    $('#shipping_id').on('change', function () {
      if(shipping_changed===false){
        shipping_changed = true;
        $('#accordion-shipping .panel-collapse.in').removeClass('in');
        $('#accordion-shipping #collapse-shipping-'+$(this).val()).collapse('show');
      }
      shipping_changed = false;
    })
  @endif

  @if(count($payment_descriptions)>1)
    let payment_changed = false;
    @foreach($payment_descriptions as $key => $payment)
      $('#accordion-payment #collapse-payment-{{ $payment->id }}').on('show.bs.collapse', function () {
        $('.payment-active-icon').css({opacity:0});
        $('#heading{{ $payment->id }} .payment-active-icon').css({opacity:1});
        if(payment_changed===false){
          payment_changed = true;
          var payment_id = $('#payment_id').val({{ $payment->id }});
        }
        payment_changed = false;
      })
    @endforeach
    $('#payment_id').on('change', function () {
      if(payment_changed===false){
        payment_changed = true;
        $('#accordion-payment .panel-collapse.in').removeClass('in');
        $('#accordion-payment #collapse-payment-'+$(this).val()).collapse('show');
      }
      payment_changed = false;
    })
  @endif
  
</script>