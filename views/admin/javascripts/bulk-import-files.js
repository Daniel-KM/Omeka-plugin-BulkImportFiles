jQuery(document).ready(function ($) {

    $('#multiFiles').change(function (event) {
        $('#upload').click();
    });

    $('.map-edit #upload').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var url = basePath + '/admin/bulk-import-files/index/get-files';

        var form_data = new FormData();
        var ins = document.getElementById('multiFiles').files.length;
        for (var x = 0; x < ins; x++) {
            form_data.append('files[]', document.getElementById('multiFiles').files[x]);
        }

        $.ajax({
            url: url,
            dataType: 'html',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                $('.files-map-block').html(response);
                $('#table-selected-files .o-icon-more.sidebar-content').click(function () {
                    $(this).parent().parent().find('.full_info').toggle();
                })
                add_button_action();
            },
            error: function (response) {
                $('.response').html(response);
            }
        });
    });

    function add_button_action() {
        var listterms = $('.listterms').html();

        $('.omeka_property .js-add-action').unbind('click');
        $('.omeka_property .js-add-action').on('click', function () {
            var row_td = $(this).parent().parent().parent();
            row_td.find('.omeka_list_property').append(listterms);

            var count = parseInt(row_td.parent().data('property-count'));
            ++count;

            row_td.parent().data('property-count', count);

            add_button_action();

            if (!row_td.hasClass('js-prepare_to_save')) {
                row_td.addClass('js-prepare_to_save');
                row_td.parent().addClass('listterms_with_action_row');
            }

            $('.omeka_property .js-single-remove-action').unbind('click');
            $('.omeka_list_property .js-single-remove-action').on('click', function () {
                var listterms_with_action_row = $(this).parent().parent().parent();

                var count = parseInt(listterms_with_action_row.parent().parent().data('property-count'));
                --count;

                listterms_with_action_row.parent().parent().data('property-count', count);

                if (count == 0) {
                    listterms_with_action_row.parent().parent().removeClass('listterms_with_action_row').find('.js-save-button').html('');
                    listterms_with_action_row.parent().parent().find('.js-prepare_to_save').removeClass('js-prepare_to_save');
                }

                $(this).parent().parent().remove();
            });
        });

        $('.omeka_property .js-remove-action').on('click', function () {
            $(this).parent().parent().remove();
        });

        $('.full_info .js-save-button button').off('click');
        $('.full_info .js-save-button button').on('click', function () {
            save_action($(this));
            event.preventDefault();
        });
    }

    function save_action(row) {
        // Omeka_file_id is the filename.
        var omeka_file_id = row.parents('.selected-files-row').first().data('file-item-id');
        var media_type = row.parents('.selected-files-row').first().data('file-type');
        var listterms_select_total = [];

        /**
         * First find new added fields
         */
        row.parents('tr.selected-files-row').find('.listterms_with_action_row').each(function () {
            var listterms_select = [];
            var file_field_property = $(this).find('.js-file_field_property').html();
            $(this).find('.listterms_with_action').each(function () {
                listterms_select.push($(this).find('.listterms_select').val());
            });

            if (listterms_select.length > 0) {
                listterms_select_total.push({
                    'field': file_field_property,
                    'property': listterms_select
                });
            }
        });

        /**
         * Add existing fields.
         */
        row.parents('tr.selected-files-row').find('.with_property').each(function () {
            var listterms_select = [];
            var file_field_property = $(this).find('.js-file_field_property').html();
            $(this).find('.omeka_property_name').each(function () {
                var selected_option = $(this).html();
                listterms_select.push(selected_option);
            });

            if (listterms_select.length > 0) {
                listterms_select_total.push({
                    'field': file_field_property,
                    'property': listterms_select
                });
            }
        });

        /**
         * Check double omeka property fields.
         */
        var properties_for_check = [];
        var check_same_property = '';
        $('.response').removeClass('error');

        $.each(listterms_select_total, function (key, val) {
            $.each(val['property'], function (pr_key, pr_val) {
                check_same_property = $.inArray(pr_val, properties_for_check);
                if (check_same_property == 0) {
                    $('.response').addClass('error');
                    $('.response').html('Omeka property can’t be same!');
                    return false;
                } else {
                    properties_for_check.push(pr_val);
                }
            });

            if (check_same_property == 0) {
                return false;
            }
        });

        if (check_same_property == 0) {
            $('html, body').animate({scrollTop: 0}, 'slow');
            return false;
        }

        var url = basePath + '/admin/bulk-import-files/index/save-options';

        var form_data = {
            'omeka_file_id': omeka_file_id,
            'media_type': media_type,
            'listterms_select': listterms_select_total,
        }

        $.ajax({
            url: url,
            data: form_data,
            type: 'post',
            beforeSend: function() {
                $('.response').html('');
                $('.response').removeClass('success warning error');
            },
            success: function (response) {
                $('.response').html(response.msg);
                $('html, body').animate({scrollTop: 0}, 'slow');
                if (response.state == true) {
                    $('.response').addClass('success');
                } else {
                    $('.response').addClass('warning');
                }
            },
            error: function (response) {
                $('.response').html(response.msg);
                $('html, body').animate({scrollTop: 0}, 'slow');
                if (response.state == false) {
                    $('.response').addClass('error');
                }
            }
        });
    }

    // Import by a directory on the server.
    $('.make_import_form .check_button').click(function () {
        var url = basePath + '/admin/bulk-import-files/index/check-folder';
        var directory = $('.make_import_form #directory').val();
        var data = {'folder' : directory};
        $.ajax({
            url: url,
            data: data,
            type: 'post',
            beforeSend: function() {
                $('.modal-loader').show();
                $('.response').html('');
            },
            success: function (response) {
                $('.response').html(response);
            },
            error: function (response) {
                $('.response').html(response.responseText);
            },
            complete: function () {
                $('.modal-loader').hide();
                action_for_recognize_files();
            }
        });

        return false;
    });

    // Import by a directory on the computer.
    $('.make-import #upload').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        var url = basePath + '/admin/bulk-import-files/index/check-files';

        var form_data = new FormData();
        var ins = document.getElementById('multiFiles').files.length;
        for (var x = 0; x < ins; x++) {
            form_data.append('files[]', document.getElementById('multiFiles').files[x]);
        }

        $.ajax({
            url: url,
            dataType: 'html',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            beforeSend: function() {
                $('.modal-loader').show();
                $('.response').html('');
            },
            success: function (response) {
                $('.response').html(response);
            },
            error: function (response) {
                $('.response').html(response.responseText);
            },
            complete: function () {
                $('.modal-loader').hide();
                action_for_recognize_files();
            }
        });

        return false;
    });

    var data_for_recognize  = {};
    var make_action = false;
    var create_action = '';
    var file_position_upload = 0;
    var total_files_for_upload = 0;

    function make_single_file_upload(file_position_upload) {
        var url = basePath + '/admin/bulk-import-files/index/process-import';
        var directory = $('.make_import_form #directory').val();
        $('.directory').val(directory);
        var isServer = data_for_recognize['is_server'];
        var importUnmapped = $('#import_unmapped').is(':checked');

        if ((file_position_upload >= total_files_for_upload) || (typeof data_for_recognize['filenames'][file_position_upload] == 'undefined')) {
            clearTimeout(create_action);
            $('.response').append('<p>Import launched.</p>');
            $('.response').append('<p>Note that the possible Omeka errors during import are reported in the logs.</p>');
            $('.response').find('.total_info').remove();
        } else {
            if (make_action == true) {
                var rowId = data_for_recognize['row_id'][file_position_upload];
                var row = importUnmapped
                    ? $('.response .file_data.row_id_' + rowId)
                    : $('.response .isset_yes.row_id_' + rowId);
                var data_process = {
                    'is_server': isServer,
                    'row_id' : rowId,
                    'filename' : data_for_recognize['filenames'][file_position_upload],
                    'source' : data_for_recognize['sources'][file_position_upload],
                    'directory': isServer ? directory : null,
                    'import_unmapped': importUnmapped,
                    'delete_file': isServer ? $('#delete_file').is(':checked') : true,
                };
                $.ajax({
                    url: url,
                    data: data_process,
                    type: 'post',
                    beforeSend: function() {
                        make_action = false;
                        clearTimeout(create_action);
                    },
                    success: function (response) {
                        if (response.length > 1) {
                            var resp = $.parseJSON(response);
                            row.addClass(resp.severity);
                            if (resp.severity === 'notice') {
                                row.find('.status').html('Notice');
                            } else if (resp.severity === 'warning') {
                                row.find('.status').html('Warning');
                            } else {
                                row.find('.status').html('Error');
                            }
                            if (resp.message) {
                                row.after('<tr class="message row_id_' + rowId + '"><td class="' + resp.severity + '" colspan="6"></td></tr>');
                                row.next().find('td').html(resp.message);
                            }
                        } else {
                            row.addClass('success');
                            row.find('.status').html('OK');
                        }
                    },
                    error: function (response) {
                        $('.response').html(response);
                    },
                    complete: function (response) {
                        make_action = true;
                        ++file_position_upload;
                        create_action = setTimeout(make_single_file_upload(file_position_upload), 1000);
                    }
                });
            } else {
                clearTimeout(create_action);
            }
        }
    }

    function action_for_recognize_files() {
        $('.js-recognize_files').click(function () {
            var filenames = [];
            var sources = [];
            var row_id = [];
            var directory = $('.make_import_form #directory').val();
            var isServer = $('.response').find('.total_info .origin').data('origin') === 'server';

            $('.response').find('.total_info').remove();
            var importUnmapped = $('#import_unmapped').is(':checked');
            var rows = importUnmapped
                ? $('.response .file_data')
                : $('.response .isset_yes')
            rows.each(function () {
                var filedata = $(this).find('.filename');
                filenames.push(filedata.data('filename'));
                sources.push(filedata.data('source'));
                row_id.push(filedata.data('row-id'));
            });

            data_for_recognize = {
                'is_server': isServer,
                'directory': isServer ? directory : null,
                'filenames': filenames,
                'sources': sources,
                'row_id': row_id,
            }

            total_files_for_upload = data_for_recognize['filenames'].length;
            make_action = true;
            create_action = setTimeout(make_single_file_upload(file_position_upload), 1000);
        });
    }

    $('.bulk-import-files.map-edit').on('click', '.file-type', function(e) {
        e.preventDefault();

        var url;

        if ($(this).hasClass('add-file-type')) {
            url = basePath + '/admin/bulk-import-files/index/add-file-type';
        } else if ($(this).hasClass('delete-file-type')) {
            if (!confirm('Do you want to delete this file type?')) {
                return;
            }
            url = basePath + '/admin/bulk-import-files/index/delete-file-type';
        } else {
            return;
        }

        var media_type = $(this).parents('.selected-files-row').first().data('file-type');
        var form_data = {
            'media_type': media_type,
        }

        $.ajax({
            url: url,
            data: form_data,
            type: 'post',
            beforeSend: function() {
                $('.response').html('');
                $('.response').removeClass('success warning error');
            },
            success: function (response) {
                $('.response').html(response.msg);
                $('html, body').animate({scrollTop: 0}, 'slow');
                if (response.state == true) {
                    $('.response').addClass('success');
                    location.href = response.reloadURL;
                } else {
                    $('.response').addClass('warning');
                }
            },
            error: function (response) {
                $('.response').html(response.msg);
                $('html, body').animate({scrollTop: 0}, 'slow');
                if (response.state == true) {
                    location.href = response.reloadURL;
                } else {
                    $('.response').addClass('error');
                }
            }
        });
    });

});
