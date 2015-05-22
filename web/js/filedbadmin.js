jQuery(function ($) {
    var service_path = window.location.origin + '/admin/service.php';
    buildTablesMenu();
    buildTablesOverview();

    $('body')
        .delegate('.service-call', 'click', function (event) {
            event.preventDefault();
            var url = $(this).attr('href'),
                hash = url.substring(url.indexOf('#') + 1);

            $.getJSON(service_path + hash, function (data) {
                $("#content-wrapper").html(renderTable(data));
            });
        })
        .delegate('.nav li', 'click', function() {
            $('h1.page-header').html($(this).text());
            $('.nav li').removeClass('active');
            $(this).addClass('active');
        });


    function buildTablesMenu() {
        $.getJSON(service_path + '?op=showtables', function (data) {
            var menu = [];
            $.each(data.rows, function (x, table) {
                menu.push('<li><a href="#?op=show&table=' + table + '" class="service-call">' + table + '</a></li>');
            });

            $(".tables-tab .badge").html(data.rows.length);
            $(".nav-tables").html(menu.join(''));
        });
    }

    function buildTablesOverview() {
        $.getJSON(service_path + '?op=tables', function (data) {
            $.each(data.tables, function (x, table) {
                addBox({
                    classes: 'col-md-12',
                    top: '',
                    title: table.table,
                    text: renderTable(table)
                });
            });
        });
    }
});

function renderTable(data) {
    var content = [];

    if (!data.rows.length) {
        content = [setMessage('No data was found.', 'warning')];
    }
    else {
        var header = Object.keys(data.rows[0]),
            rows = data.rows,
            table = createTable(header, rows),
            execution_time = data.execution_time ? setMessage('Operation executed in ' + data.execution_time + ' seconds', 'success') : ''

        content = [
            execution_time,
            '<div class="panel panel-default">',
            '<div class="panel-heading">' + data.table + '</div>',
            '<div class="table-responsive">',
            table,
            '</div>',
            '</div>'
        ];
    }

    return content.join('');
}

function setMessage(message, type) {
    var content = [
        '<div class="alert alert-' + type + ' alert-dismissible" role="alert">',
        '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>',
        message,
        '</div>'
    ];

    return content.join('');
}

function createTable(header, rows, options) {
    $ = jQuery.noConflict();

    var defaults = {
        id: '',
        classes: ['table', 'table-striped'],
        title: ''
    };

    var settings = $.extend({}, defaults, options);

    var table = [];

    table.push('<table id="' + settings.id + '" class="' + settings.classes.join(' ') + '" title="' + settings.title + '">', '<thead>', '<tr>');

    $.each(header, function (i, data) {
        table.push('<th>', data, '</th>');
    });

    table.push('</tr>', '</thead>', '<tbody>');

    $.each(rows, function (i, row) {
        table.push('<tr>');
        $.each(row, function (i, data) {
            table.push('<td>', data, '</td>');
        });
        table.push('</tr>');
    });

    table.push('</tr>', '</tbody>', '</table>');

    return table.join('');
}

function addBox(data) {
    $ = jQuery.noConflict();

    var $box = $("#fdb-thumb").clone(),
        defaults = {
            classes: 'col-sm-6 col-md-4',
            top: '',
            title: '',
            text: '',
            footer: []
        };

    var data = $.extend({}, defaults, data);

    $box.find('.box-wrapper').addClass(data.classes);
    $box.find('.top').html(data.top);
    $box.find('h3').html(data.title);
    $box.find('.text').html(data.text);
    $box.find('.thumb-footer').html(data.footer.join(''));

    if (!$('#content-wrapper > .row').length) {
        $('#content-wrapper').append('<div class="row"></div>');
    }

    $('#content-wrapper > .row').append($box.html());
}