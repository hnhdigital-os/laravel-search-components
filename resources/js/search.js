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
      var results = $('#'+$(this).attr('id').replace(new RegExp('-form$'), '-results'));
      var page = $(form).find('[name=page]').val();

      var action_url = form.data('action');

      if (action_url.indexOf('?') == -1) {
        action_url += '?';
      } else {
        action_url += '&';
      }
      action_url += 'page=' + page;

      $.ajax(action_url, {
        data: $.searchComponentsSearch.serialize(form, results),
        beforeSend: function() {
          // Trigger on form.
          results.trigger('hnhdigital-search::before-send');

          // Trigger on results.
          form.trigger('hnhdigital-search::before-send');
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
    results.find('.search-notices').html($H.build(response.notices));

    var tbody_rows = results.find('.search-result-rows');

    if (typeof response.rows != 'undefined') {
      tbody_rows.html($H.build(response.rows));
    }

    if (typeof response.append != 'undefined') {
      tbody_rows.append($H.build(response.append));
    }

    if (typeof response.prepend != 'undefined') {
      tbody_rows.prepend($H.build(response.prepend));
    }

    var form_id = results.attr('id').replace(new RegExp('-results$'), '-form');

    if (results.data('scroll-to')) {
      window.scrollTo(0, 0);
    }

    // Trigger on form.
    $('#' + form_id).trigger('hnhdigital-search::success', [response]);

    // Trigger on results.
    $(results).trigger('hnhdigital-search::success', [response]);
  },

  serialize: function(form, results) {
    var search = {};

    results.find('.search-field').each(function() {
        search[this.name] = $(this).val();
    });

    // Trigger on form.
    serialize_extra = form.triggerHandler('hnhdigital-search::serialize', [search]);

    if (typeof serialize_extra == 'object') {
      search = serialize_extra;
    }

    // Trigger on results.
    serialize_extra = results.triggerHandler('hnhdigital-search::serialize', [search]);

    if (typeof serialize_extra == 'object') {
      search = serialize_extra;
    }

    return {
      'search': search,
      'current-keys': results.find('.search-result-rows tr')
        .map(function() { return $(this).data('search-key') })
        .get()
        .join(',')
    };
  }
}

$(function() {
  $.searchComponentsSearch.autoInit();

  $('.hnhdigital-search-results').on('submit', function() {
    var form_id = $(this).attr('id').replace(new RegExp('-results$'), '-form');
    $('#' + form_id + ' button[type=submit]').trigger('click');
  });
});
