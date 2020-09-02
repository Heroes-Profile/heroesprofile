<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
<script type="text/javascript">
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<!--Data Tables-->
<script src="{{ asset('js/datatables.min.js') }}"></script>
