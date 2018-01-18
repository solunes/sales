<script type="text/javascript"> 
  $(document).ready(function() {
    $(document).on('change', 'input#amount', function() { 
    	console.log('Recalculando');
    	var amount = $(this).data('amount');
      var new_amount = $(this).val();
      if(parseFloat(new_amount)>parseFloat(amount)){
        $(this).val(parseFloat(amount));
      }
    });
  });

</script>