@if($i)
  <script type="text/javascript"> 
    var typingTimer;                //timer identifier
    var doneTypingInterval = 2000;  //time in ms, 5 second for example
    var $input = $('#barcode-reader');
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
      var barcode = $('#barcode-reader').val();
      checkBarcode(barcode);
    }

    $(document).ready(function() {
      $(document).on('change', 'select#search-product', function() { 
        console.log('Producto seleccionado manualmente');
        product_id = $(this).val();
        if(product_id!=''){
          createPurchaseProduct(product_id);
          $('select#search-product').val('').change();
        }
      });
    });

    function createPurchaseProduct (product_id) {
      $.lightbox("{{ url('admin/purchase-add-product/'.$i->id) }}/" + product_id + "?purchase_id={{ $i->id }}&lightbox[width]=600&lightbox[height]=400");
    }

    function checkBarcode (barcode) {
      $.ajax("{{ url('admin/check-barcode/'.$product_node_id) }}/" + barcode, {
        success: function(data) {
          //console.log('Exitoso: ' + data);
          if(data.check){
            createPurchaseProduct(data.id);
            console.log('Producto Creado: ' + data.id)
          } else {
            $.lightbox("{{ url('admin/child-model/product/create') }}/?printed=1&barcode=" + barcode + "&purchase_id={{ $i->id }}&lightbox[width]=1000&lightbox[height]=600");
            console.log('Producto NO Encontrado: ' + data.id)
          }
          $('#barcode-reader').val('');
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
    $("#barcode-reader").keypress(function(e){
        if ( e.which === 13 ) {
            console.log("Prevent form submit.");
            e.preventDefault();
        }
    });
  </script>
@endif