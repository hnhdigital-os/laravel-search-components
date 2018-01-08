
<div id="{{ $form_id }}-results" class="panel-body tab-pane active">
   <div class="table-responsive">
      <table class="table {{ $table_class }}">
      </table>
   </div>
</div>

@section('footer')
@parent
<form id="{{ $form_id }}-form" class="hnhdigital-search-form" novalidate="novalidate" action="" method="post" onsubmit="return false;">
  <button type="submit" class="hidden-search-button"></button>
</form>
<script>
  $('#{{ $form_id }}-form').data('validator', {'settings': {}})
</script>
@stop
