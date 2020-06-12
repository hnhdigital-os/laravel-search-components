<div class="hnhdigital-search">
  <div id="hnhdigital-{{ $search->name }}-results" class="hnhdigital-{{ $search->name }}-results hnhdigital-search-results {!! $search->result_class ?? '' !!}" {!! $search->result_attributes ?? '' !!}>

     {{ $slot ?? '' }}

     <div class="table-responsive">
        <table class="table {{ $search->table_class ?? '' }}" {!! $search->table_attributes ?? '' !!}>
          {!! $search->columns !!}
          <thead class="search-header">
            {!! $search->search_header !!}
          </thead>
          {!! $search->search_input !!}
          <tbody class="search-notices">
            {!! $search->notices !!}
          </tbody>
          <tbody class="search-info-rows">
            {!! $search->search_info !!}
          </tbody>
          <tbody class="search-result-rows">
            {!! $search->result !!}
          </tbody>
          <tbody class="search-footer">
            {!! $search->search_footer !!}
          </tbody>
        </table>
     </div>

     {!! $footer ?? '' !!}
  </div>
</div>

@section('footer')
@parent
<form id="{{ $search->form_id }}" class="hnhdigital-{{ $search->name }}-form hnhdigital-search-form" novalidate="novalidate" action="{{ $search->fallback_route }}"  data-action="{{ $search->route }}" method="post" onsubmit="return false;">
  <input type="hidden" name="page" value="{{ $search->getPaginator('page') }}">
  <input type="hidden" name="results_mode" value="rows">
  <input type="hidden" name="row_id" value="">
  <button type="submit" class="hidden-search-button"></button>
</form>

<script>
  $('#{{ $search->form_id }}').data('validator', {'settings': {}})
</script>
@stop
