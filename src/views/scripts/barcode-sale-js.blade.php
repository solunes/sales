<script type="text/javascript"> 
  var typingTimer;                //timer identifier
  var doneTypingInterval = 2000;  //time in ms, 5 second for example
  var $input = $('#barcode');
  var array = [];
  $input.on('keydown', function () {
    clearTimeout(typingTimer);
  });
  $input.on('keyup', function () {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(doneTyping, doneTypingInterval);
  });

  function doneTyping () {
    console.log('Terminado de escribir');
    var barcode = $('#barcode').val();
    checkBarcode(barcode);
  }

  $(document).ready(function() {
    $(document).on('change', 'select#search-product', function() { 
      console.log('Producto seleccionado manualmente');
      product_id = $(this).val();
      if(product_id!=''){
        createProduct(product_id, 1);
        $('select#search-product').val('').change();
      }
    });
    $(document).on("change paste keyup", 'input.quantity', function() {
      console.log('Recalculando');
      if(fixNumber($(this).val())>fixNumber($(this).data('max_quantity'))){
        $('#notification-bar').text('La cantidad no puede ser mayor a la de stock...');
        $(this).val($(this).data('max_quantity'));
      }
      product_id = $(this).attr('rel');
      recalculateProductTotal(product_id);
    });
    $(document).on("change paste keyup", 'input.price', function() {
      console.log('Recalculando');
      if(fixNumber($(this).val())>fixNumber($(this).data('price'))){
        $('#notification-bar').text('El precio no puede ser mayor al original...');
        $(this).val($(this).data('price'));
      }
      product_id = $(this).attr('rel');
      recalculateProductTotal(product_id);
    });
    $("input#shipping_cost").on("change paste keyup", function() {
      recalculateTotal();
    });
    $("input#cash_bob").on("change paste keyup", function() {
      recalculateTotalPaid();
    });
    $("input#cash_usd").on("change paste keyup", function() {
      recalculateTotalPaid();
    });
    $("input#pos_bob").on("change paste keyup", function() {
      recalculateTotalPaid();
    });
    $("input#exchange").on("change paste keyup", function() {
      recalculateTotalPaid();
    });
    $('#field_credit_amount').hide();
    $('#field_credit_due').hide();
    $('#field_credit_details').hide();
    $('#shipping_cost_row').hide();
    $('select#type').change(function() { 
      if($(this).val()=='normal'){
        $('#shipping_cost_row').hide();
        $('#shipping_cost').val(0);
      } else {
        $('#shipping_cost_row').show();
      }
    });
    $('select#credit').change(function() { 
      if($(this).val()=='1'){
        $('#field_credit_amount').show();
        $('#field_credit_due').show();
        $('#field_credit_details').show();
      } else {
        $('#field_credit_amount').hide();
        $('#field_credit_due').hide();
        $('#field_credit_details').hide();
      }
      recalculateCredit();
    });
    //$('#field_invoice_nit').hide();
    //$('#field_invoice_name').hide();
    $('select#invoice').change(function() { 
      if($(this).val()=='1'){
        $('#field_invoice_nit').show();
        $('#field_invoice_name').show();
      } else {
        $('#field_invoice_nit').hide();
        $('#field_invoice_name').hide();
      }
      /*$('input.price').each(function(){
        var price = $(this).data(use_price);
        $(this).val(price);
        recalculateProductTotal($(this).attr('rel'));
      });*/
    });
  });

  function recalculateProductTotal (product_id) {
    price = $('#products').find('input.price[rel='+ product_id +']').val();
    quantity = $('#products').find('input.quantity[rel='+ product_id +']').val();
    currency = $('#products').find('input.currency[rel='+ product_id +']').val();
    final_price = parseInt(quantity)*fixNumber(price);
    $('#products').find('input.final_price[rel='+ product_id +']').val(final_price + ' ' + currency );
    recalculateTotal();
  }

  function recalculateTotal () {
    var total = 0;
    total = fixNumber(total);
    $('input.final_price').each(function(){
      total += fixNumber($(this).val());
    });
    var shipping_cost = $('#shipping_cost').val();
    total += fixNumber(shipping_cost);
    $('input#amount').val(total + ' Bs.');
    recalculateTotalPaid();
  }

  function recalculateTotalPaid () {
    console.log('Recalculando Total');
    var amount = fixNumber($('input#amount').val());
    var cash_bob = fixNumber($('input#cash_bob').val());
    if(isNaN(cash_bob)){
      cash_bob = 0;
    }
    var cash_usd = fixNumber($('input#cash_usd').val());
    cash_usd = cash_usd * fixNumber($('input#exchange').val());
    var pos_bob = fixNumber($('input#pos_bob').val());
    total_paid = cash_bob + cash_usd + pos_bob;
    var change = total_paid - amount;
    change = change.toFixed(2);
    $('input#paid_amount').val(total_paid.toFixed(2));
    if(change<0){
      $('#field_change label').html('Monto Restante por Pagar en Bs.');
      $('input#change').val(change + ' Bs.');
    } else {
      $('#field_change label').html('Cambio a Devolver en Bs');
      $('input#change').val(change + ' Bs.');
    }
    recalculateCredit();
  }

  function recalculateCredit () {
    var amount = $('input#amount').val();
    var paid_amount = $('input#paid_amount').val();
    var total = fixNumber(amount) - fixNumber(paid_amount);
    total = fixNumber(total).toFixed(2);
    if(total>0){
      $('input#credit_amount').val(total);
    } else {
      $('input#credit_amount').val(0);
    }
  }

  function createProduct (product_id, quantity) {
    product_id = parseInt(product_id);
    if($.inArray(product_id, array) !== -1){
      console.log('Producto existe en la tabla');
      var $quantity_item = $('#products').find('input.quantity[rel='+ product_id +']');
      quantity = parseInt($quantity_item.val()) + 1;
      if(quantity>parseInt($quantity_item.data('max_quantity'))){
        $('#notification-bar').text('No puede introducir una cantidad mayor al stock de productos...');
        quantity = parseInt($quantity_item.val());
      } 
      $('#products').find('input.quantity[rel='+ product_id +']').val(quantity);
      recalculateProductTotal(product_id);
    } else {
      console.log('Producto no existe en la tabla');
      var rowcount = $('#products>tbody>tr').length;
      var rowval = $('#products>tbody>tr:last input.product_id').val();
      $.ajax("{{ url('admin/check-product') }}/" + product_id+"/{{ $currency->id }}", {
        success: function(data) {
          array.push(product_id);
          console.log('array:' + array);
          var name = data.name;
          var price = data.price;
          var no_invoice_price = data.no_invoice_price;
          console.log('price:' + price);
          console.log('no_invoice_price:' + no_invoice_price);
          var currency = data.currency;
          var max_quantity = parseInt(data.quantity);
          var final_price = parseInt(quantity)*fixNumber(price);
          console.log('final_price:' + final_price);
          if(rowval){
            $('#products>tbody>tr:last').clone().insertAfter('#products>tbody>tr:last');
            $('#products>tbody>tr:last .count').html(parseInt(rowcount) + 1);
          }
          $('#products>tbody>tr:last input.product_id').val(product_id);
          $('#products>tbody>tr:last input.product_name').val(name);
          $('#products>tbody>tr:last input.price').val(price);
          $('#products>tbody>tr:last input.price').attr('placeholder', price + ' ' + currency);
          $('#products>tbody>tr:last input.price').attr('title', 'El precio sin factura sugerido es de ' + no_invoice_price + ' ' + currency);
          $('#products>tbody>tr:last input.price').data('price', price);
          $('#products>tbody>tr:last input.price').attr('rel', product_id);
          $('#products>tbody>tr:last input.currency').val(currency);
          $('#products>tbody>tr:last input.currency').attr('rel', product_id);
          $('#products>tbody>tr:last input.quantity').val(quantity);
          $('#products>tbody>tr:last input.quantity').attr('rel', product_id);
          $('#products>tbody>tr:last input.quantity').data('max_quantity', max_quantity);
          $('#products>tbody>tr:last input.final_price').attr('rel', product_id);
          $('#products>tbody>tr:last a.delete_row').attr('rel', product_id);
          recalculateProductTotal(product_id);
        },
        error: function() {
          $('#notification-bar').text('Ocurrió un error...');
        }
      });
    }
  }

  function fixNumber(number) {
    var number = parseFloat(number);
    if(isNaN(number)){
      number = 0;
    }
    return number;
  }

  function checkBarcode (barcode) {
    $.ajax("{{ url('admin/check-barcode/'.$node->id) }}/" + barcode, {
      success: function(data) {
        //console.log('Exitoso: ' + data);
        if(data.check){
          $('#notification-bar').text('Se agregó el producto "' + barcode +'" correctamente, introduzca la cantidad correcta.');
          createProduct(data.id, 1);
          $('#barcode').val('');
          console.log('Producto Creado: ' + data.id)
        } else {
          $('#notification-bar').text('No se encontró el producto "' + barcode + '" buscado.');
          console.log('Producto NO Encontrado: ' + data.id)
        }
      },
      error: function() {
        $('#notification-bar').text('Ocurrió un error...');
      }
    });
  }

  $(document).ready(function() {
      var pressed = false; 
      var chars = []; 
      $(window).keypress(function(e) {
          if (e.which >= 48 && e.which <= 57) {
            chars.push(String.fromCharCode(e.which));
          }
          if (pressed == false) {
            setTimeout(function(){
              if (chars.length >= 10) {
                var barcode = chars.join("");
                console.log("Barcode Scanned: " + barcode);
                checkBarcode(barcode);
              }
              chars = [];
              pressed = false;
            },500);
          }
          pressed = true;
      });
  });
  $("#barcode").keypress(function(e){
      if ( e.which === 13 ) {
          console.log("Prevent form submit.");
          e.preventDefault();
      }
  });
  $('#products').on('click', 'a.delete_row', function(e){
    e.preventDefault();
    var count = $('#products>tbody>tr').size();
    // Remover uno al counter_val si el contador si existe
    /*var counter_val = $('#'+rel+'>tfoot .calculate-count').val();
    $('#'+rel+'>tfoot .calculate-count').val(parseInt(counter_val)-1);*/
    // Remover campo completo
    if(count>1){
      $(this).parent().parent().remove();
    }
    product_id = $(this).attr('rel');
    var index = array.indexOf(parseInt(product_id));
    if (index != -1) {
      array.splice(index, 1);
      console.log('array: ' + array);
    }
    recalculateTotal();
    return false;
  });
  $('#create-sale').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) { 
      e.preventDefault();
      return false;
    }
  });
</script>