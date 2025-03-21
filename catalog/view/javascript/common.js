function getURLVar(key) {
    var value = [];

    var query = String(document.location).split('?');

    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');

            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }

        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
}

$(document).ready(function () {
    /* Search */
    $('#search input[name=\'search\']').parent().find('button').on('click', function() {
        var url = $('base').attr('href') + 'index.php?route=product/search';

        var value = $('header #search input[name=\'search\']').val();

        if (value) {
            url += '&search=' + encodeURIComponent(value);
        }

        // webibazaar Search Start 
        var category_id = $('#webibazaar-search-category').val();
        if (category_id > 0) {
            url += '&category_id=' + encodeURIComponent(category_id);
        }
        // webibazaar Search END

        location = url;
    });

    $('#search input[name=\'search\']').on('keydown', function(e) {
        if (e.keyCode == 13) {
            $('header #search input[name=\'search\']').parent().find('button').trigger('click');
        }
    });

// webibazaar Search Start 
    $(".webibazaar-search").keyup(function(){
        if(($(this).val()).length >= 3) {
            var category_id = $('#webibazaar-search-category').val();
            console.log(category_id);
            $.ajax({
                url: 'index.php?route=common/webibazaar_search|search',
                type: 'post',
                data : 'search_string=' + $(this).val() + '&category_id=' + category_id,
                beforeSend  : function () {
                    $('.webibazaar-search-loader').show();
                },
                success: function(result){
                    console.log(result);
                    $('.webibazaar-search-result').html(result);
                    $('.webibazaar-search-loader').hide();
                }
            });
            //console.log(this);
        }
        else {
            $('.webibazaar-search-result').html('');
        }
    });
    // webibazaar Search END
    // Tooltip
    var oc_tooltip = function () {
        // Apply to all on current page
        tooltip = bootstrap.Tooltip.getOrCreateInstance(this);
        tooltip.show();
    }

    $(document).on('mouseenter', '[data-bs-toggle=\'tooltip\']', oc_tooltip);

    $(document).on('click', 'button', function () {
        $('.tooltip').remove();
    });

    // Date
    var oc_datetimepicker = function () {
        $(this).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function (start, end) {
            $(this.element).val(start.format('YYYY-MM-DD'));
        });
    }

    $(document).on('focus', '.date', oc_datetimepicker);

    // Time
    var oc_datetimepicker = function () {
        $(this).daterangepicker({
            singleDatePicker: true,
            datePicker: false,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'HH:mm'
            }
        }, function (start, end) {
            $(this.element).val(start.format('HH:mm'));
        }).on('show.daterangepicker', function (ev, picker) {
            picker.container.find('.calendar-table').hide();
        });
    }

    $(document).on('focus', '.time', oc_datetimepicker);

    // Date Time
    var oc_datetimepicker = function () {
        $('.datetime').daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            autoUpdateInput: false,
            timePicker: true,
            timePicker24Hour: true,
            locale: {
                format: 'YYYY-MM-DD HH:mm'
            }
        }, function (start, end) {
            $(this.element).val(start.format('YYYY-MM-DD HH:mm'));
        });
    }

    $(document).on('focus', '.datetime', oc_datetimepicker);

    // Alert Fade
    var oc_alert = function () {
        window.setTimeout(function () {
            //$('.alert-dismissible').fadeTo(1000, 0, function () {
            //    $(this).remove();
            //});
        }, 7000);
    }

    $(document).on('click', 'button', oc_alert);
});

$(document).ready(function () {
    // Currency
    $('#form-currency .dropdown-item').on('click', function (e) {
        e.preventDefault();

        $('#form-currency input[name=\'code\']').val($(this).attr('href'));

        $('#form-currency').submit();
    });

    // Search
    $('#search input[name=\'search\']').parent().find('button').on('click', function () {
        var url = $('base').attr('href') + 'index.php?route=product/search&language=' + $(this).attr('data-lang');

        var value = $('header #search input[name=\'search\']').val();

        if (value) {
            url += '&search=' + encodeURIComponent(value);
        }

        location = url;
    });

    $('#search input[name=\'search\']').on('keydown', function (e) {
        if (e.keyCode == 13) {
            $('header #search input[name=\'search\']').parent().find('button').trigger('click');
        }
    });

    // Menu
    $('#menu .dropdown-menu').each(function () {
        var menu = $('#menu').offset();
        var dropdown = $(this).parent().offset();

        var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

        if (i > 0) {
            $(this).css('margin-left', '-' + (i + 10) + 'px');
        }
    });

    // Product List
    $('#button-list').on('click', function () {
        var element = this;

        $('#product-list').attr('class', 'row row-cols-1 product-list');

        $('#button-grid').removeClass('active');
        $('#button-list').addClass('active');

        localStorage.setItem('display', 'list');
    });

    // Product Grid
    $('#button-grid').on('click', function () {
        var element = this;

        // What a shame bootstrap does not take into account dynamically loaded columns
        $('#product-list').attr('class', 'row product-grid row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3');

        $('#button-list').removeClass('active');
        $('#button-grid').addClass('active');

        localStorage.setItem('display', 'grid');
    });

    // Local Storage
    if (localStorage.getItem('display') == 'list') {
        $('#product-list').attr('class', 'row row-cols-1 product-list');
        $('#button-list').addClass('active');
    } else {
        $('#product-list').attr('class', 'row product-grid  row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3');
        $('#button-grid').addClass('active');
    }

    /* Agree to Terms */
    $('body').on('click', '.modal-link', function (e) {
        e.preventDefault();

        var element = this;

        $('#modal-information').remove();

        $.ajax({
            url: $(element).attr('href'),
            dataType: 'html',
            success: function (html) {
                $('body').append(html);

                $('#modal-information').modal('show');
            }
        });
    });

    // Cookie Policy
    $('#cookie button').on('click', function () {
        var element = this;

        $.ajax({
            url: $(this).val(),
            type: 'get',
            dataType: 'json',
            beforeSend: function () {
                $(element).button('loading');
            },
            complete: function () {
                $(element).prop('disabled', false).removeClass('loading');
            },
            success: function (json) {
                if (json['success']) {
                    $('#cookie').fadeOut(400, function () {
                        $('#cookie').remove();
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });
});

// Forms
$(document).on('submit', 'form[data-oc-toggle=\'ajax\']', function (e) {
    e.preventDefault();

    var element = this;

    var form = e.target;

    var action = $(form).attr('action');

    if (e.originalEvent !== undefined && e.originalEvent.submitter !== undefined) {
        var button = e.originalEvent.submitter;
    } else {
        var button = '';
    }

    var formaction = $(button).attr('formaction');

    if (formaction !== undefined) {
        action = formaction;
    }

    var method = $(form).attr('method');

    if (method === undefined) {
        method = 'post';
    }

    var enctype = $(element).attr('enctype');

    if (enctype === undefined) {
        enctype = 'application/x-www-form-urlencoded';
    }

    // https://github.com/opencart/opencart/issues/9690
    if (typeof CKEDITOR != 'undefined') {
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
    }

    // console.log(e);
    // console.log('element ' + element);
    // console.log('action ' + action);
    // console.log('button ' + button);
    // console.log('formaction ' + formaction);
    // console.log('method ' + method);
    // console.log('enctype ' + enctype);
    console.log($(form));
    $.ajax({
        url: action,
        type: method,
        data: $(form).serialize(),
        dataType: 'json',
        cache: false,
        contentType: enctype,
        processData: false,
        beforeSend: function () {
            $(button).prop('disabled', true).addClass('loading');
        },
        complete: function () {
            $(button).prop('disabled', false).removeClass('loading');
        },
        success: function (json) {
            $('.alert-dismissible').remove();
            $(form).find('.is-invalid').removeClass('is-invalid');
            $(form).find('.invalid-feedback').removeClass('d-block');

            //console.log(json);

            if (json['redirect']) {
                location = json['redirect'].replaceAll('&amp;', '&');
            }

            if (typeof json['error'] == 'string') {
                $('#alert').prepend('<div class="a-one"> <div class="alert alert-danger alert-dismissible"><svg width="20px" height="20px"> <use xlink:href="#successi"></use> </svg> ' + json['error'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>');
            }

            if (typeof json['error'] == 'object') {
                if (json['error']['warning']) {
                    $('#alert').prepend('<div class="a-one"> <div class="alert alert-danger alert-dismissible"><svg width="20px" height="20px"> <use xlink:href="#successi"></use> </svg> ' + json['error']['warning'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>');
                }

                for (key in json['error']) {
                    $('#input-' + key.replaceAll('_', '-')).addClass('is-invalid').find('.form-control, .form-select, .form-check-input, .form-check-label').addClass('is-invalid');
                    $('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');
                }
            }

            if (json['success']) {
                $('#alert').prepend('<div class="a-one"> <div class="alert alert-success alert-dismissible"><svg width="20px" height="20px"> <use xlink:href="#successi"></use> </svg> ' + json['success'] + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>');

                // Refresh
                var url = $(form).attr('data-oc-load');
                var target = $(form).attr('data-oc-target');

                if (url !== undefined && target !== undefined) {
                    $(target).load(url);
                }
            }

            // Replace any form values that correspond to form names.
            for (key in json) {
                $(form).find('[name=\'' + key + '\']').val(json[key]);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
});

// Upload
$(document).on('click', 'button[data-oc-toggle=\'upload\']', function () {
    var element = this;

    if (!$(element).prop('disabled')) {
        $('#form-upload').remove();

        $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" value=""/></form>');

        $('#form-upload input[name=\'file\']').trigger('click');

        $('#form-upload input[name=\'file\']').on('change', function (e) {
            if ((this.files[0].size / 1024) > $(element).attr('data-oc-size-max')) {
                alert($(element).attr('data-oc-size-error'));

                $(this).val('');
            }
        });

        if (typeof timer !== 'undefined') {
            clearInterval(timer);
        }

        var timer = setInterval(function () {
            if ($('#form-upload input[name=\'file\']').val() != '') {
                clearInterval(timer);

                $.ajax({
                    url: $(element).attr('data-oc-url'),
                    type: 'post',
                    data: new FormData($('#form-upload')[0]),
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $(element).prop('disabled', true).addClass('loading');
                    },
                    complete: function () {
                        $(element).prop('disabled', false).removeClass('loading');
                    },
                    success: function (json) {
                        console.log(json);

                        if (json['error']) {
                            alert(json['error']);
                        }

                        if (json['success']) {
                            alert(json['success']);
                        }

                        if (json['code']) {
                            $($(element).attr('data-oc-target')).attr('value', json['code']);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        }, 500);
    }
});

// Chain ajax calls.
class Chain {
    constructor() {
        this.start = false;
        this.data = [];
    }

    attach(call) {
        this.data.push(call);

        if (!this.start) {
            this.execute();
        }
    }

    execute() {
        if (this.data.length) {
            this.start = true;

            var call = this.data.shift();

            var jqxhr = call();

            jqxhr.done(function () {
                chain.execute();
            });
        } else {
            this.start = false;
        }
    }
}

var chain = new Chain();

// Autocomplete
(function ($) {
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            var $this = $(this);
            var $dropdown = $('#' + $this.attr('list'));

            this.timer = null;
            this.items = [];

            $.extend(this, option);

            // Focus
            $this.on('focus', function () {
                this.request();
            });

            // Keydown
            $this.on('input', function (e) {
                this.request();

                var value = $this.val();

                if (value && this.items[value]) {
                    this.select(this.items[value]);
                }
            });

            // Request
            this.request = function () {
                clearTimeout(this.timer);

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 50, this);
            }

            // Response
            this.response = function (json) {
                var html = '';
                var category = {};
                var name;
                var i = 0, j = 0;

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        // update element items
                        this.items[json[i]['label']] = json[i];

                        if (!json[i]['category']) {
                            // ungrouped items
                            html += '<option>' + json[i]['label'] + '</option>';
                        } else {
                            // grouped items
                            name = json[i]['category'];

                            if (!category[name]) {
                                category[name] = [];
                            }

                            category[name].push(json[i]);
                        }
                    }

                    for (name in category) {
                        for (j = 0; j < category[name].length; j++) {
                            html += '<option value="' + category[name][j]['label'] + '">' + name + '</option>';
                        }
                    }
                }

                $dropdown.html(html);
            }
        });
    }
})(window.jQuery);

/* alert */
$(document).ready(function() {
window.setTimeout(function() {
    $('.a-one').remove()
}, 50000);
$("button.btn-close").click(function() {
    $(".a-one").remove()
});
});
window.setTimeout(function () {
        $(".a-one").remove();
    }, 50000),
    $(document).on("click", "button.btn-close", function (t) {
        $(".a-one").remove();
    });
/* alert end*/
