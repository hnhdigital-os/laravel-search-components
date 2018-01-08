/**
 * HnhDigital Search Component for Search.
 *
 * @type {Object}
 */
$.searchComponentsSearch = {

  /**
   * Auto init any search components.
   *
   * @return void
   */
  autoInit: function() {
    $('.hnhdigital-search-form').on('submit', function() {
      var form = $(this);
      var results = $('#'+$(this).attr('id').replace('-form', '-results'));
      var page = $(form).find('[name=page]').val();

      $.ajax(form.data('action') + '?page=' + page, {
        data: $.searchComponentsSearch.serialize(results),
        beforeSend: function() {

        },
        success: function(response) {
          $.searchComponentsSearch.updateResults(results, response);
        }
      });
      return false;
    });
  },

  /**
   * Update the search results.
   *
   * @return void
   */
  updateResults: function(results, response) {
    results.find('.search-header').html($H.build(response.header));
    results.find('.search-result-rows').html($H.build(response.rows));
    window.scrollTo(0, 0);
  },

  serialize: function(results) {
    var search = {};

    results.find('.search-field').each(function() {
        search[this.name] = $(this).val();
    });

    return {'search': search};
  }
}

$(function() {
  $.searchComponentsSearch.autoInit();
});
