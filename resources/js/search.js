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
      var results = $('.'+$(this).attr('id').replace(new RegExp('-form$'), '-results'));
      var page = $(form).find('[name=page]').val();
      var results_mode = $(form).find('[name=results_mode]').val();
      var row_id = $(form).find('[name=row_id]').val();

      // Abort an existing request.
      if (typeof form.data('xhr') != 'undefined' && results_mode !== 'row') {
        form.data('xhr').abort();
        form.removeData('xhr');
      }

      // Trigger on form.
      results.trigger('hnhdigital-search::before-prep', [form]);

      // Trigger on results.
      form.trigger('hnhdigital-search::before-prep', [form]);

      var action_url = form.data('action');

      if (action_url.indexOf('?') == -1) {
        action_url += '?';
      } else {
        action_url += '&';
      }
      action_url += 'page=' + page;
      action_url += '&results_mode=' + results_mode;
      action_url += '&row_id=' + row_id;

      $.each(form.data('paramaters'), function(key, value) {
        action_url += '&'+key+'=' + value;
      });

      var xhr = $.ajax(action_url, {
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

      if (results_mode !== 'row') {
        form.data('xhr', xhr);
      }

      return false;
    });
  },

  /**
   * Update the search results.
   *
   * @return void
   */
  updateResults: function(results, response) {
    if (!results.hasClass('hnhdigital-search-results')) {
      var form_id = results.attr('id').replace(new RegExp('-form$'), '-results');
      results = $('.'+form_id);
    }

    if (typeof response.row != 'undefined') {
      results.find('[data-id=' + response.id + ']:not(:first)').remove();
      results.find('[data-id=' + response.id + ']').replaceWith(response.row);
      results.find('[data-id=' + response.id + ']').trigger('hnhdigital-search::after-reload');

      results.find('.search-info-rows').html($H.build(response.info));

      return;
    }

    results.find('.search-header').html($H.build(response.header)).toggle(response.paginator.count > 0 || response.paginator.total > 0);
    results.find('.search-notices').html($H.build(response.notices));
    results.find('.search-info-rows').html($H.build(response.info));
    results.find('.search-footer').html($H.build(response.footer));

    var tbody_rows = results.find('.search-result-rows');

    if (typeof response.columns != 'undefined') {
      results.find('colgroup').replaceWith(response.columns);
    }

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

    // Update action xhr route.
    $('.' + form_id).data('action', response.xhr_route);

    if (results.data('scroll-to')) {
      window.scrollTo(0, 0);
    }

    // Trigger on form.
    $('.' + form_id).trigger('hnhdigital-search::success', [response]);

    // Trigger on results.
    $(results).trigger('hnhdigital-search::success', [response]);

    // Trigger a scroll event.
    $(window).trigger('scroll');
  },

  serialize: function(form, results) {
    var search = {};

    results.find('.search-field').each(function() {

      if (this.name.includes('[]')) {
        if (typeof search[this.name.replace('[]', '')] == 'undefined') {
          search[this.name.replace('[]', '')] = [];
        }
        if ($(this).prop('checked')) {
          search[this.name.replace('[]', '')].push($(this).val());
        }
        return;
      }

      if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
        if ($(this).prop('checked')) {
          search[this.name] = $(this).val();
        }

        return;
      }

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

  /**
   * Run a search request.
   */
  $('.hnhdigital-search-results').on('submit', function() {
    var form_id = $(this).attr('id').replace(new RegExp('-results$'), '-form');
    var form = $('.'+form_id);
    $('.' + form_id + ' button[type=submit]').trigger('click');
  });

  /**
   * Provide triggerable action to update the search results for a given element.
   */
  $('.hnhdigital-search-results').on('updateResults', function(e, response) {
    var form_id = $(this).attr('id').replace(new RegExp('-results$'), '-form');
    var form = $('.'+form_id);

    $.searchComponentsSearch.updateResults(form, response);
  });

  /**
   * Reset inputs.
   */
  $('.hnhdigital-search-results').on('reset', function(e) {
    var results = $(this);
    var result_id = results.attr('id');
    var form_id = results.attr('id').replace(new RegExp('-form$'), '-results');

    $('.' + result_id + ' .search-field').each(function() {
      switch ($(this).prop('tagName')) {
        case 'SELECT':
          if ($(this).prop('multiple') && $(this).hasClass('init-select2')) {
            $(this).val('').trigger('change');
          } else if ($(this).prop('multiple')) {
            $(this).find('option:selected').removeProp('selected').trigger('change')
          } else {
            $(this).prop('selectedIndex', 0).trigger('change');
          }
          break;
        default:
          if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') {
            $(this).prop('checked', false);
            break;
          }

          $(this).val('');
      }
    });

    // Trigger on form.
    $('.' + form_id).trigger('hnhdigital-search::reset');

    // Trigger on results.
    $(results).trigger('hnhdigital-search::reset');

    $(results).submit();
  });

  /**
   * Click action for Loading next page of results.
   */
  $('.hnhdigital-search-results').on('click', '.action-load-next-page', function() {
    if ($(this).data('loading-next-page')) {
      return;
    }

    $(this).data('loading-next-page', true)
      .html((typeof $.icon !== 'undefined' ? $.icon('s circle-notch fa-spin') : '') + ' Loading...');

    var results = $(this).closest('.hnhdigital-search-results');
    var form = $('.'+results.attr('id').replace(new RegExp('-results$'), '-form'));
    $(form).find('[name=page]').val($(this).data('page'));
    $(form).find('[name=results_mode]').val('append');
    form.trigger('submit');

    $(form).find('[name=page]').val(1);
    $(form).find('[name=results_mode]').val('rows');
  });

  $('.hnhdigital-search-results .search-result-rows').on('reload', 'tr', function(e) {
    if (typeof $(this).data('id') === 'undefined') {
      return;
    }

    $(this).trigger('hnhdigital-search::before-reload');

    var results = $(this).closest('.hnhdigital-search-results');
    var form = $('.'+results.attr('id').replace(new RegExp('-results$'), '-form'));

    $(form).find('[name=results_mode]').val('row');
    $(form).find('[name=row_id]').val($(this).data('id'));
    form.trigger('submit');

    $(form).find('[name=page]').val(1);
    $(form).find('[name=row_id]').val('');
    $(form).find('[name=results_mode]').val('rows');
  });

  /**
   * Run a next page of results when the link comes into view.
   */
  $(window).on('DOMContentLoaded load resize scroll', function() {
    $('.action-load-next-page:visible').each(function(index) {
      if (isElementInViewport(this)) {
        if (typeof $(this).animateCss == 'function') {
          $(this).animateCss('flipOutX');
        }
        $(this).trigger('click');
      }
    });
  }).trigger('scroll');
});

/**
 * Is the element in the viewport?
 */
function isElementInViewport(el) {
    var rect = el.getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}
