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

      var action_url = form.data('action');

      if (action_url.indexOf('?') == -1) {
        action_url += '?';
      } else {
        action_url += '&';
      }
      action_url += 'page=' + page;

      $.ajax(action_url, {
        data: $.searchComponentsSearch.serialize(results),
        beforeSend: function() {
          form.trigger('hnhdigital-search::before-send');
        },
        success: function(response) {
          $.searchComponentsSearch.updateResults(results, response);
          form.trigger('hnhdigital-search::success', [response]);
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
