$(function () {
    // Ajax Setup
    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
        var token;
        if (! options.crossDomain) {
            token = window.Global.csrfToken;
            if (token) {
                jqXHR.setRequestHeader('X-CSRF-Token', token);
            }
        }

        return jqXHR;
    });

    $.ajaxSetup({
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Accept', 'application/json');
            // xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
        },
        statusCode: {
            401: function () {
                window.location.href = '/auth/login';
            },
            403: function () {
                window.location.href = '/';
            }
        }
    });

    // Prevent double form submission
    $('form').submit(function () {
        var $form = $(this);
        $form.find(':submit').prop('disabled', true);
    });

    // Autosizing of textareas.
    // autosize($('textarea.autosize'));

    // Mock the DELETE form requests.
    $('[data-method]').not(".disabled").append(function () {
        var methodForm = "\n";
        methodForm += "<form action='" + $(this).attr('href') + "' method='POST' style='display:none'>\n";
        methodForm += "<input type='hidden' name='_method' value='" + $(this).attr('data-method') + "'>\n";
        methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=token]').attr('content') + "'>\n";
        methodForm += "</form>\n";
        return methodForm;
    })
        .removeAttr('href')
        .on('click', function () {
            var button = $(this);

            if (button.hasClass('confirm-action')) {
                askConfirmation(function () {
                    button.find("form").submit();
                });
            } else {
                button.find("form").submit();
            }
        });

    // Messenger config
    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top',
        theme: 'air'
    };

    // App setup
    window.Cachet = {};

    moment.locale(Global.locale);

    $('abbr.timeago').each(function () {
        var $el = $(this);
        $el
            .livestamp($el.data('timeago'))
            .tooltip();
    });

    window.Cachet.Notifier = function () {
        this.notify = function (message, type, options) {
            if (_.isPlainObject(message)) {
                message = message.detail;
            }
            type = (typeof type === 'undefined' || type === 'error') ? 'error' : type;

            var defaultOptions = {
                message: message,
                type: type,
                showCloseButton: true
            };

            options = _.extend(defaultOptions, options);

            Messenger().post(options);
        };
    };

    $(".sidebar-toggler").on('click', function (e) {
        e.preventDefault();
        $(".wrapper").toggleClass("toggled");
    });

    $('.color-code').each(function () {
        var $this = $(this);

        $this.minicolors({
            control: 'hue',
            defaultValue: $this.val() || '',
            inline: false,
            letterCase: 'lowercase',
            opacity: false,
            position: 'bottom left',
            theme: 'bootstrap'
        });
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('button.close').on('click', function () {
        $(this).parents('div.alert').addClass('hide');
    });

    $('form[name=IncidentForm] select[name=component_id]').on('change', function () {
        var $option = $(this).find('option:selected');
        var $componentStatus = $('#component-status');

        if (parseInt($option.val(), 10) !== 0) {
            if ($componentStatus.hasClass('hidden')) {
                $componentStatus.removeClass('hidden');
            }
        } else {
            $componentStatus.addClass('hidden');
        }
    });

    // Sortable models.
    var orderableLists = document.querySelectorAll('[data-orderable-list]');

    $.each(orderableLists, function (k, list) {
        var url = $(list).data('orderableList');
        var notifier = new Cachet.Notifier();

        new Sortable(list, {
            group: 'omega',
            handle: '.drag-handle',
            onUpdate: function () {
                var orderedIds = $.map(list.querySelectorAll('[data-orderable-id]'), function(elem) {
                    return $(elem).data('orderable-id');
                });

                $.ajax({
                    async: true,
                    url: url,
                    type: 'POST',
                    data: {
                        ids: orderedIds
                    },
                    success: function () {
                        notifier.notify('Ordering updated.', 'success');
                    },
                    error: function () {
                        notifier.notify('Ordering not updated.', 'error');
                    }
                });
            }
        });
    });

    // Toggle inline component statuses.
    $('form.component-inline').on('click', 'input[type=radio]', function () {
        var $form = $(this).parents('form');
        var formData = $form.serializeObject();

        $.ajax({
            async: true,
            url: '/dashboard/api/components/' + formData.component_id,
            type: 'POST',
            data: formData,
            success: function(component) {
                (new Cachet.Notifier()).notify($form.data('messenger'), 'success');
            },
            error: function(a, b, c) {
                (new Cachet.Notifier()).notify('Something went wrong updating the component.');
            }
        });
    });

    // Incident management
    $('select[name=template]').on('change', function () {
        var $this = $(this).find('option:selected'),
            slug   = $this.val();

        // Only fetch the template if we've picked one.
        if (slug) {
            $.ajax({
                async: true,
                data: {
                    slug: slug
                },
                url: '/dashboard/api/incidents/templates',
                success: function(tpl) {
                    var $form = $('form[role=form]');
                    $form.find('input[name=name]').val(tpl.name);
                    $form.find('textarea[name=message]').val(tpl.template);
                },
                error: function () {
                    (new Cachet.Notifier()).notify('There was an error finding that template.');
                }
            });
        }
    });

    // Banner removal JS
    $('#remove-banner').on('click', function (){
        $('#banner-view').remove();
        $('input[name=remove_banner]').val('1');
    });

    $('.group-name').on('click', function (event) {
        event.stopPropagation();

        var $this = $(this);

        $this.find('.group-toggle').toggleClass('ion-ios-minus-outline').toggleClass('ion-ios-plus-outline');

        $this.next('.group-items').toggleClass('hide');
    });

    $('.select-group').on('click', function (event) {
        var $parentGroup = $(this).closest('ul.list-group');
        $parentGroup.find('input[type=checkbox]').prop('checked', true);
        event.stopPropagation();
        return false;
    });

    $('.deselect-group').on('click', function (event) {
        var $parentGroup = $(this).closest('ul.list-group');
        $parentGroup.find('input[type=checkbox]').prop('checked', false);
        event.stopPropagation();
        return false;
    });

    // Setup wizard
    $('.wizard-next').on('click', function () {
        var $form   = $('#setup-form'),
            $btn    = $(this),
            current = $btn.data('currentBlock'),
            next    = $btn.data('nextBlock');

        $btn.button('loading');

        // Only validate going forward. If current group is invalid, do not go further
        if (next > current) {
            var currentUrl = window.location.href.replace(/step\d/, '');
            var url = currentUrl + '/step' + current;
            $.post(url, $form.serializeObject())
                .done(function(response) {
                    goToStep(current, next);
                })
                .fail(function(response) {
                    var errors = _.toArray(response.responseJSON.errors);
                    _.each(errors, function(error) {
                        (new Cachet.Notifier()).notify(error);
                    });
                })
                .always(function () {
                    $btn.button('reset');
                });

            return false;
        } else {
            goToStep(current, next);
            $btn.button('reset');
        }
    });

    // Sparkline
    if ($.fn.sparkline) {
        var sparkLine = function () {
            $('.sparkline').each(function () {
                var data = $(this).data();
                data.valueSpots = {
                    '0:': data.spotColor
                };

                $(this).sparkline(data.data, data);
                var composite = data.compositedata;

                if (composite) {
                    var stlColor = $(this).attr("data-stack-line-color"),
                        stfColor = $(this).attr("data-stack-fill-color"),
                        sptColor = $(this).attr("data-stack-spot-color"),
                        sptRadius = $(this).attr("data-stack-spot-radius");

                    $(this).sparkline(composite, {
                        composite: true,
                        lineColor: stlColor,
                        fillColor: stfColor,
                        spotColor: sptColor,
                        highlightSpotColor: sptColor,
                        spotRadius: sptRadius,
                        valueSpots: {
                            '0:': sptColor
                        }
                    });
                };
            });
        };

        sparkLine();
    }

    function goToStep(current, next) {
        // validation was ok. We can go on next step.
        $('.block-' + current)
          .removeClass('show')
          .addClass('hidden');

        $('.block-' + next)
          .removeClass('hidden')
          .addClass('show');

        $('.steps .step')
            .removeClass("active")
            .filter(":lt(" + (next) + ")")
            .addClass("active");
    }

    // Check for updates.
    if ($('#update-alert').length > 0) {
        $.ajax({
            async: true,
            dataType: 'json',
            url: '/api/v1/version',
        }).done(function (result) {
            if (result.meta.on_latest === false) {
                $('#update-alert').removeClass('hidden');
            }
        });
    }

    function askConfirmation(callback, cancelCallback) {
        swal({
            type: "warning",
            title: "Confirm your action",
            text: "Are you sure you want to do this?",
            buttonsStyling: false,
            reverseButtons: true,
            confirmButtonText: "Yes",
            confirmButtonClass: "btn btn-lg btn-danger",
            cancelButtonClass: "btn btn-lg btn-default",
            showCancelButton: true,
            focusCancel: true
        }).then(function () {
            if (_.isFunction(callback)) callback();
        }, function () {
            if (_.isFunction(cancelCallback)) cancelCallback();
        });
    }
});
