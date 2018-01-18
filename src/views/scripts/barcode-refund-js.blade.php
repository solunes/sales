<script type="text/javascript"> 
  $(document).ready(function() {
    $(document).on("change paste keyup", 'input.refund_quantity', function() {
      console.log('Recalculando');
      if(parseFloat($(this).val())>parseFloat($(this).data('max_quantity'))){
        $('#notification-bar').text('La cantidad no puede ser mayor a la de stock...');
        $(this).val($(this).data('max_quantity'));
      }
      product_id = $(this).attr('rel');
      recalculateProductTotal(product_id);
    });
    $(document).on("change paste keyup", 'input.refund_amount', function() {
      console.log('Recalculando');
      recalculateTotal();
    });
  });

  function recalculateProductTotal (product_id) {
    price = $('#products').find('input.price[rel='+ product_id +']').val();
    quantity = $('#products').find('input.refund_quantity[rel='+ product_id +']').val();
    final_price = parseInt(quantity)*parseFloat(price);
    $('#products').find('input.refund_amount[rel='+ product_id +']').val(final_price);
    recalculateTotal();
  }

  function recalculateTotal () {
    var total = 0;
    total = parseFloat(total);
    $('input.refund_amount').each(function(){
      total += parseFloat($(this).val());
    });
    $('input#amount').val(total + ' Bs.');
    recalculateTotalPaid();
  }

  $('#create-sale').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) { 
      e.preventDefault();
      return false;
    }
  });
</script>