<script type="text/javascript">
  $('a.delete').click(function (event) {
    $(this).parents('tr').first().remove();
    event.stopPropagation();
    return false;
  });
</script>