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

      $.each(form.data('paramaters'), function(key, value) {
        action_url += '&'+key+'=' + value;
      });

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
    if (!results.hasClass('hnhdigital-search-results')) {
      var form_id = results.attr('id').replace(new RegExp('-form$'), '-results');
      results = $('.'+form_id);
    }

    results.find('.search-header').html($H.build(response.header));
    results.find('.search-notices').html($H.build(response.notices));
    results.find('.search-footer').html($H.build(response.footer));

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
    $('.' + form_id).trigger('hnhdigital-search::success', [response]);

    // Trigger on results.
    $(results).trigger('hnhdigital-search::success', [response]);

    // Trigger a scroll event.
    $(window).trigger('scroll');
  },

  serialize: function(form, results) {
    var search = {};

    results.find('.search-field').each(function() {
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
   * Click action for Loading next page of results.
   */
  $('.hnhdigital-search-results').on('click', '.action-load-next-page', function() {
      if ($(this).data('loading-next-page')) {
        return;
      }

      $(this).data('loading-next-page', true)
      var results = $(this).closest('.hnhdigital-search-results');
      var form = $('.'+results.attr('id').replace(new RegExp('-results$'), '-form'));
      $(form).find('[name=page]').val($(this).data('page'));
      $(form).find('[name=results_mode]').val('append');
      form.trigger('submit');

      $(form).find('[name=page]').val(1);
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
  });
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
