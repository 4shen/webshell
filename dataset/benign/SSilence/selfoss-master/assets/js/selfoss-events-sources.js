import selfoss from './selfoss-base';

/**
 * initialize source editing events for loggedin users
 */
selfoss.events.sources = function() {
    // cancel source editing
    $('.source-cancel').unbind('click').click(function() {
        var parent = $(this).parents('.source');
        if (parent.hasClass('source-new')) {
            parent.fadeOut('fast', function() {
                $(this).remove();
            });
        } else {
            $(this).parents('.source-edit-form').hide();
        }
    });

    // add new source
    $('.source-add').unbind('click').click(function() {
        $.ajax({
            url: 'source',
            type: 'GET',
            success: function(response) {
                $('.source-opml').after(response);
                selfoss.events.sources();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                parent.find('.source-edit-delete').removeClass('loading');
                selfoss.ui.showError(selfoss.ui._('error_add_source') + ' ' +
                                     textStatus + ' ' + errorThrown);
            }
        });
    });

    // save source
    $('.source-save').unbind('click').click(function() {
        var parent = $(this).parents('.source');

        // remove old errors
        parent.find('span.error').remove();
        parent.find('.error').removeClass('error');

        // show loading
        parent.find('.source-action').addClass('loading');

        // get id
        let id = parent.attr('data-source-id');

        // set url
        const url = `source/${id}`;

        // get values and params
        var values = selfoss.getValues(parent);
        values['tags'] = values['tags'].split(',');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: values,
            success: function(response) {
                var id = response['id'];
                parent.attr('data-source-id', id);

                // show saved text
                parent.find('.source-showparams').addClass('saved').html(selfoss.ui._('source_saved'));
                window.setTimeout(function() {
                    parent.find('.source-showparams').removeClass('saved').html(selfoss.ui._('source_edit'));
                }, 10000);

                // hide input form
                parent.find('.source-edit-form').hide();

                // update title
                var title = $('<p>').html(response.title).text();
                parent.find('.source-title').text(title);
                parent.find("input[name='title']").val(title);

                // show all links for new items
                parent.removeClass('source-new');

                // update tags
                selfoss.refreshTags(response.tags, true);

                // update sources
                selfoss.refreshSources(response.sources, true);

                selfoss.events.navigation();
            },
            error: function(jqXHR) {
                selfoss.showErrors(parent, JSON.parse(jqXHR.responseText));
            },
            complete: function() {
                parent.find('.source-action').removeClass('loading');
            }
        });
    });

    // delete source
    $('.source-delete').unbind('click').click(function() {
        var answer = confirm(selfoss.ui._('source_warn'));
        if (answer == false) {
            return;
        }

        // get id
        var parent = $(this).parents('.source');
        var id = parent.attr('data-source-id');

        // show loading
        parent.find('.source-edit-delete').addClass('loading');

        // delete on server
        $.ajax({
            url: 'source/delete/' + id,
            data: {},
            type: 'POST',
            success: function() {
                parent.fadeOut('fast', function() {
                    $(this).remove();
                });

                // reload tags and remove source from navigation
                selfoss.reloadTags();
                $(`#nav-sources [data-source-id=${id}]`).parents('li').get(0).remove();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                parent.find('.source-edit-delete').removeClass('loading');
                selfoss.ui.showError(selfoss.ui._('error_delete_source') + ' ' + errorThrown);
            }
        });
    });

    // show params
    $('.source-showparams').unbind('click').click(function() {
        $(this).parent().parent().find('.source-edit-form').show();
    });

    // select new source spout type
    $('.source-spout').unbind('change').change(function() {
        var val = $(this).val();
        var params = $(this).parents('ul').find('.source-params');

        // save param values
        var savedParamValues = {};
        params.find('input').each(function(index, param) {
            if (param.value) {
                savedParamValues[param.name] = param.value;
            }
        });

        params.show();
        if ($.trim(val).length == 0) {
            params.html('');
            return;
        }
        params.addClass('loading');
        $.ajax({
            url: 'source/params',
            data: { spout: val },
            type: 'GET',
            success: function(data) {
                params.removeClass('loading').html(data);

                // restore param values
                params.find('input').each(function(index, param) {
                    if (savedParamValues[param.name]) {
                        param.value = savedParamValues[param.name];
                    }
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                params.removeClass('loading').append('<li class="error">' + errorThrown + '</li>');
            }
        });
    });
};
