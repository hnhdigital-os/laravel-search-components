
<div class="hnhdigital-search">
  <div id="hnhdigital-{{ $search->name }}-results" class="panel-body tab-pane active">
     <div class="table-responsive">
        <table class="table{{ $table_class or '' }}">
          {!! $search->colgroup !!}
          <thead class="search-header">
            {!! $search->search_header !!}
          </thead>
          {!! $search->search_input !!}
          <tbody class="search-result-rows">
            {!! $search->result !!}
          </tbody>
          <thead class="search-footer">
            {!! $search->search_footer !!}
          </thead>
        </table>
     </div>
  </div>
</div>

@section('footer')
@parent
<form id="{{ $search->form_id }}" class="hnhdigital-search-form" novalidate="novalidate" action="{{ $search->fallback_route }}"  data-action="{{ $search->route }}" method="post" onsubmit="return false;">
  <input type="hidden" name="page" value="{{ $search->getPaginator('page') }}">
  <button type="submit" class="hidden-search-button"></button>
</form>

<script>
  $('#{{ $search->form_id }}').data('validator', {'settings': {}})
</script>
@stop
