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
    if(city_id==11){
      $('.city_other').fadeIn();
    } else {
      $('.city_other').fadeOut();
    }
  }

  $( document ).ready(function() {
    queryShipping();
    updateOtherCity();
  });

  $(document).on('change', 'select.query_shipping', function() {
    queryShipping();
  });

  $(document).on('change', 'select#city_id', function() {
    updateOtherCity();
  });

</script>