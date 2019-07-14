jQuery(document).ready(function ($) {

    // available_maps = $.parseJSON($('.bulkimportfiles_maps_settings').val());
    //
    // console.log(available_maps);
    //
    //
    // available_maps_html = '<div class="title">Bulk import files current maps:';
    // available_maps_html += '</div>';
    // available_maps_html += '<div>';
    //
    // $.each(available_maps, function (key, val) {
    //     available_maps_html += '<div class="field js-maps-' + val + '">' + key + ' - <a class="button">' + val + '</a></div>';
    // })
    //
    // available_maps_html += '</div>';
    //
    //
    // $('.modulePreContent.module_BulkImportFiles').append(available_maps_html);

    $('#flup').change(function (event) {

        event.preventDefault();

        var files = event.target.files;
        var path = files[0].webkitRelativePath;
        var Folder = path.split('/');

        // console.log(files);
        // console.log(path);
        // console.log(Folder);

        importform = $('#importform');

        // var form_data = new FormData(importform);
        // form_data.append('file', files);

        //var formData = new FormData($(this).parents('form')[0]);

        url = basePath + '/admin/bulk-import-files/index/get-files';

        var form_data = new FormData();
        var ins = document.getElementById('multiFiles').files.length;
        for (var x = 0; x < ins; x++) {
            form_data.append('files[]', document.getElementById('multiFiles').files[x]);
        }

        // console.log(form_data);

        $.ajax({
            url: url, // point to server-side PHP script
            dataType: 'text', // what to expect back from the PHP script
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                $('#msg').html(response); // display success response from the PHP script
            },
            error: function (response) {
                $('#msg').html(response); // display error response from the PHP script
            }
        });

        var formData = new FormData(importform);

        // console.log(importform);

        //var filedata = document.getElementsByName('file');

        // len = files.length;
        // var i = 0;
        //
        // //console.log(len);
        //
        // for (; i < len; i++) {
        //     file = files[i];
        //
        //     // console.log(file);
        //
        //     formData.append('files[]', file);
        // }

        //formData.append('file', files);

        // console.log(formData);

        // url = basePath + '/admin/bulk-import-files/index/get-files';
        //
        // $.ajax({
        //     url: url,
        //     type: 'POST',
        //     xhr: function() {
        //         var myXhr = $.ajaxSettings.xhr();
        //         return myXhr;
        //     },
        //     success: function (data) {
        //         //alert('Data Uploaded: ' + data);
        //     },
        //     data: formData,
        //     cache: false,
        //     contentType: false,
        //     processData: false
        // });
        // return false;

        // $.each(files , function() {
        //
        // });
        //
        // $('.selected-files-source').append('<input type="file" name="source">');

        // $.ajax({
        //     url: url,
        //     type: 'POST',
        //     data: form_data,
        //     dataType: 'text',
        //     cache: false,
        //     contentType: false,
        //     processData: false,
        // }).done(function (data) {
        //
        //     //$('.selected-files').html(data);
        //     // console.log(data);
        // }).fail(function (err) {
        //     console.log(err);
        // });

    })

    $('#multiFiles').change(function (event) {
        // console.log('change');
        $('#upload').click();
    });

    $('.map-edit #upload').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        url = basePath + '/admin/bulk-import-files/index/get-files';

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
        listterms = $('.listterms').html();

        $('.omeka_property .js-add-action').unbind('click');

        $('.omeka_property .js-add-action').on('click', function () {
            var row_td = $(this).parent().parent().parent();
            row_td.find('.omeka_list_property').append(listterms);
            count = parseInt(row_td.parent().data('property-count'));
            ++count;
            row_td.parent().data('property-count', count);

            add_button_action();

            if (!row_td.hasClass('js-prepare_to_save')) {
                row_td.addClass('js-prepare_to_save');
                // row_td.parent().find('.js-save-button').prepend('<button type="submit" name="add-item-submit">Save</button>');
                row_td.parent().addClass('listterms_with_action_row');
            }

            $('.omeka_property .js-single-remove-action').unbind('click');

            $('.omeka_list_property .js-single-remove-action').on('click', function () {
                listterms_with_action_row = $(this).parent().parent().parent();

                count = parseInt(listterms_with_action_row.parent().parent().data('property-count'));
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
        omeka_file_id = row.parents('.selected-files-row').first().data('file-item-id');
        media_type = row.parents('.selected-files-row').first().data('file-type');

        listterms_select_total = [];

        /**
         * First find new added fields
         */
        row.parents('tr.selected-files-row').find('.listterms_with_action_row').each(function () {
            listterms_select = [];
            file_field_property = $(this).find('.js-file_field_property').html();
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
            file_field_property = $(this).find('.js-file_field_property').html();
            listterms_select = [];
            $(this).find('.omeka_property_name').each(function () {
                var selected_option = $(this).html();
                // A check is done with the list because the value may be displayed differently.
                var selected_option_check = selected_option.replace(/\s+/g, '');
                var selected_option_key = null;
                $('.listterms option').each(function(index, obj){
                    if ($(obj).val().replace(/\s+/g, '') == selected_option_check) {
                        selected_option_key = $(obj).attr('value');
                    }
                });
                listterms_select.push(selected_option_key);
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
        check_same_property = '';
        $('.response').removeClass('error');
        property_for_check = [];

        $.each(listterms_select_total, function (key, val) {
            $.each(val['property'], function (pr_key, pr_val) {
                check_same_property = $.inArray(pr_val, property_for_check);
                if (check_same_property == 0) {
                    $('.response').addClass('error');
                    $('.response').html('Omeka property can’t be same!');
                    return false;
                } else {
                    property_for_check.push(pr_val);
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

        url = basePath + '/admin/bulk-import-files/index/save-options';

        var form_data = {
            'omeka_file_id': omeka_file_id,
            'media_type': media_type,
            'file_field_property': file_field_property,
            'listterms_select': listterms_select_total,
        }

        $.ajax({
            url: url,
            data: form_data,
            type: 'post',
            success: function (response) {
                $('.response').addClass('success');
                $('.response').html(response);
                $('html, body').animate({scrollTop: 0}, 'slow');
            },
            error: function (response) {
                $('.response').html(response);
            }
        });
    }

    directory = '';

    // Import by a directory on the server.
    $('.make_import_form .check_button').click(function () {
        directory = {'folder' : $('.make_import_form #directory').val()};
        url = basePath + '/admin/bulk-import-files/index/check-folder';
        $.ajax({
            url: url,
            data: directory,
            type: 'post',
            beforeSend: function() {
                $('.modal-loader').show();
                $('.response').html('');
            },
            success: function (response) {
                $('.response').html(response);
            },
            error: function (response) {
                $('.response').html(response);
            },
            complete: function () {
                $('.modal-loader').hide();
                action_for_recognize_files();
            }
        });

        // console.log(directory);
        return false;
    });

    // Import by a directory on the computer.
    $('.make-import #upload').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        url = basePath + '/admin/bulk-import-files/index/check-files';

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
                $('.response').html(response);
            },
            complete: function () {
                $('.modal-loader').hide();
                action_for_recognize_files();
            }
        });

        // console.log(form_data);
        return false;
    });

    make_action = false;
    data_for_recognize  = {};
    create_action = '';
    file_position_upload = 0;
    total_files_for_upload = 0;

    function make_single_file_upload(file_position_upload) {
        // console.log(make_action);
        // console.log(total_files_for_upload);
        // console.log(data_for_recognize['filenames'][file_position_upload]);

        url = basePath + '/admin/bulk-import-files/index/process-import';
        directory = $('.make_import_form #directory').val();
        $('.directory').val(directory);
        isServer = data_for_recognize['is_server'];
        importUnmapped = $('#import_unmapped').is(':checked');

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
                data_process = {
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
            filenames = [];
            sources = [];
            row_id = [];
            isServer = $('.response').find('.total_info .origin').data('origin') === 'server';

            $('.response').find('.total_info').remove();
            importUnmapped = $('#import_unmapped').is(':checked');
            rows =importUnmapped
                ? $('.response .file_data')
                : $('.response .isset_yes')
            rows.each(function () {
                filedata = $(this).find('.filename');
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

            // console.log(data_for_recognize);

            total_files_for_upload = data_for_recognize['filenames'].length;
            make_action = true;
            create_action = setTimeout(make_single_file_upload(file_position_upload), 1000);
        });
    }

    $('.bulk-import-files.map-edit').on('click', '.file-type', function(e) {
        e.preventDefault();

        var process;
        var url;

        if ($(this).hasClass('add-file-type')) {
            process = 'add';
            url = basePath + '/admin/bulk-import-files/index/add-file-type';
        } else if ($(this).hasClass('delete-file-type')) {
            if (!confirm('Do you want to delete this file type?')) {
                return;
            }
            process = 'delete';
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
                response = $.parseJSON(response);
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
                response = $.parseJSON(response.responseText);
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
