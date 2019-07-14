
function add_file_action(media_type) {
    url = basePath + '/admin/bulk-import-files/index/add-file';

    var form_data = {
        'media_type': media_type,
    }

    jQuery.ajax({
        url: url,
        data: form_data,
        type: 'post',
        success: function (response) {
            jQuery('.response').addClass('success');
            jQuery('.response').html(response);
            jQuery('html, body').animate({scrollTop: 0}, 'slow');
            location.reload();
        },
        error: function (response) {
            jQuery('.response').html(response);
        }
    });
}

function delete_file_action(media_type) {
    event.preventDefault();
    event.stopPropagation();

    url = basePath + '/admin/bulk-import-files/index/delete-file';

    var form_data = {
        'media_type': media_type,
    }

    if(!confirm("Do you want to delete this file type?")){
        return;
    }

    jQuery.ajax({
        url: url,
        data: form_data,
        type: 'post',
        success: function (response) {
            response = jQuery.parseJSON(response);
            if(response.state == true){
                location.href = response.reloadURL;
            }else{
                jQuery('.response').addClass('warning');
                jQuery('.response').html(response);
                jQuery('html, body').animate({scrollTop: 0}, 'slow');
            }
        },
        error: function (response) {
            response = jQuery.parseJSON(response.responseText);
            if(response.state == true){
                location.href = response.reloadURL;
            }else{
                jQuery('.response').addClass('warning');
                jQuery('.response').html(response);
                jQuery('html, body').animate({scrollTop: 0}, 'slow');
            }
        }
    });
}

jQuery(document).ready(function () {

    // available_maps = jQuery.parseJSON(jQuery('.bulkimportfiles_maps_settings').val());
    //
    // console.log(available_maps);
    //
    //
    // available_maps_html = '<div class="title">Bulk import files current maps:';
    // available_maps_html += '</div>';
    // available_maps_html += '<div>';
    //
    // jQuery.each(available_maps, function (key, val) {
    //     available_maps_html += '<div class="field js-maps-' + val + '">'+key+' - <a class="button">' + val + '</a></div>';
    // })
    //
    // available_maps_html += '</div>';
    //
    //
    // jQuery('.modulePreContent.module_BulkImportFiles').append(available_maps_html);



    jQuery('#flup').change(function (event) {

        event.preventDefault();

        var files = event.target.files;
        var path = files[0].webkitRelativePath;
        var Folder = path.split('/');

        // console.log(files);
        // console.log(path);
        // console.log(Folder);

        importform = jQuery('#importform');

        // var form_data = new FormData(importform);
        // form_data.append('file', files);

        //var formData = new FormData(jQuery(this).parents('form')[0]);

        url = basePath + '/admin/bulk-import-files/index/get-files';

        var form_data = new FormData();
        var ins = document.getElementById('multiFiles').files.length;
        for (var x = 0; x < ins; x++) {
            form_data.append('files[]', document.getElementById('multiFiles').files[x]);
        }

        // console.log(form_data);

        jQuery.ajax({
            url: url, // point to server-side PHP script
            dataType: 'text', // what to expect back from the PHP script
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                jQuery('#msg').html(response); // display success response from the PHP script
            },
            error: function (response) {
                jQuery('#msg').html(response); // display error response from the PHP script
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
        // jQuery.ajax({
        //     url: url,
        //     type: 'POST',
        //     xhr: function() {
        //         var myXhr = jQuery.ajaxSettings.xhr();
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


        // jQuery.each(files , function() {
        //
        // });
        //
        // jQuery('.selected-files-source').append('<input type="file" name="source">');

        // jQuery.ajax({
        //     url: url,
        //     type: 'POST',
        //     data: form_data,
        //     dataType: 'text',
        //     cache: false,
        //     contentType: false,
        //     processData: false,
        // }).done(function (data) {
        //
        //     //jQuery('.selected-files').html(data);
        //     // console.log(data);
        // }).fail(function (err) {
        //     console.log(err);
        // });

    })

    jQuery('#multiFiles').change(function (event) {
        // console.log('change');

        jQuery('#upload').click();
    });

    jQuery('#upload').on('click', function (e) {

        e.preventDefault();
        e.stopPropagation();

        url = basePath + '/admin/bulk-import-files/index/get-files';

        var form_data = new FormData();
        var ins = document.getElementById('multiFiles').files.length;
        for (var x = 0; x < ins; x++) {
            form_data.append('files[]', document.getElementById('multiFiles').files[x]);
        }

        jQuery.ajax({
            url: url,
            dataType:'html',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function (response) {
                jQuery('.files-map-block').html(response);
                jQuery('#table-selected-files .o-icon-more.sidebar-content').click(function () {
                    jQuery(this).parent().parent().find('.full_info').toggle();
                })

                add_button_action();
            },
            error: function (response) {
                jQuery('.response').html(response);
            }
        });


        // jQuery.ajax({
        //     url: url,
        //     dataType: 'html',
        //     cache: false,
        //     contentType: false,
        //     processData: false,
        //     data: form_data,
        //     type: 'post',
        //     success: function (response) {

        //         jQuery('.files-map-block').html("HELOW");
        //         // jQuery('.files-map-block').html(response);
        //         // jQuery('#table-selected-files .o-icon-more.sidebar-content').click(function () {
        //         //     jQuery(this).parent().parent().find('.full_info').toggle();
        //         // })

        //         // add_button_action();
        //     },
        //     error: function (response) {
        //         //jQuery('#msg').html(response); // display error response from the PHP script
        //     }
        // });
    });

    function add_button_action() {
        listterms = jQuery('.listterms').html();

        jQuery('.omeka_property .js-add-action').unbind('click');

        jQuery('.omeka_property .js-add-action').on('click', function () {

            var row_td = jQuery(this).parent().parent().parent();
            row_td.find('.omeka_list_property').append(listterms);

            count = parseInt(row_td.parent().data('property-count'));

            count++;
            row_td.parent().data('property-count', count);

            add_button_action();

            if (!row_td.hasClass('js-prepare_to_save')) {
                row_td.addClass('js-prepare_to_save');
                // row_td.parent().find('.js-save-button').prepend('<button type="submit" name="add-item-submit">Save</button>');
                row_td.parent().addClass('listterms_with_action_row');
            }

            jQuery('.omeka_property .js-single-remove-action').unbind('click');


            jQuery('.omeka_list_property .js-single-remove-action').on('click', function () {

                listterms_with_action_row = jQuery(this).parent().parent().parent();

                count = parseInt(listterms_with_action_row.parent().parent().data('property-count'));
                --count;
                listterms_with_action_row.parent().parent().data('property-count', count);

                if (count == 0) {
                    listterms_with_action_row.parent().parent().removeClass('listterms_with_action_row').find('.js-save-button').html('');
                    listterms_with_action_row.parent().parent().find('.js-prepare_to_save').removeClass('js-prepare_to_save');
                }

                jQuery(this).parent().parent().remove();

            });

        });

        jQuery('.omeka_property .js-remove-action').on('click', function () {
            jQuery(this).parent().parent().remove();
        });

        jQuery('.full_info .js-save-button button').off('click');
        jQuery('.full_info .js-save-button button').on('click', function () {
            save_action(jQuery(this));
            event.preventDefault();
        });

    }

    function save_action(row) {
        // Omeka_file_id is the filename.
        omeka_file_id = row.parents('.selected-files-row').find('.omeka_file_id').val();
        media_type = row.parents('.selected-files-row').find('.media_type').val();

        listterms_select_total = [];

        /**
         * First find new added fields
         */
        row.parents('tr.selected-files-row').find('.listterms_with_action_row').each(function () {
            listterms_select = [];
            file_field_property = jQuery(this).find('.js-file_field_property').html();
            jQuery(this).find('.listterms_with_action').each(function () {
                listterms_select.push(jQuery(this).find('.listterms_select').val());
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
            file_field_property = jQuery(this).find('.js-file_field_property').html();
            listterms_select = [];
            jQuery(this).find('.omeka_property_name').each(function () {
                var selected_option = jQuery(this).html();
                // A check is done with the list because the value may be displayed differently.
                var selected_option_check = selected_option.replace(/\s+/g, '');
                var selected_option_key = null;
                jQuery('.listterms option').each(function(index, obj){
                    if (jQuery(obj).html().replace(/\s+/g, '') == selected_option_check) {
                        selected_option_key = jQuery(obj).attr('value');
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
        jQuery('.response').removeClass('error');
        property_for_check = [];

        jQuery.each(listterms_select_total, function (key, val) {
            jQuery.each(val['property'], function (pr_key, pr_val) {
                check_same_property = jQuery.inArray(pr_val, property_for_check);
                if (check_same_property == 0) {
                    jQuery('.response').addClass('error');
                    jQuery('.response').html('Omeka property can’t be same!');
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
            jQuery('html, body').animate({scrollTop: 0}, 'slow');
            return false;
        }

        url = basePath + '/admin/bulk-import-files/index/save-options';

        var form_data = {
            'omeka_file_id': omeka_file_id,
            'media_type': media_type,
            'file_field_property': file_field_property,
            'listterms_select': listterms_select_total,
        }

        jQuery.ajax({
            url: url,
            data: form_data,
            type: 'post',
            success: function (response) {
                jQuery('.response').addClass('success');
                jQuery('.response').html(response);
                jQuery('html, body').animate({scrollTop: 0}, 'slow');
            },
            error: function (response) {
                jQuery('.response').html(response);
            }
        });

    }

    directory = '';

    jQuery('.make_import_form .check_button').click(function () {

        directory = {'folder' : jQuery('.make_import_form #directory').val()};

        url = basePath + '/admin/bulk-import-files/index/check-folder';

        jQuery.ajax({
            url: url,
            data: directory,
            type: 'post',
            beforeSend: function() {
                jQuery('.modal-loader').show();
            },
            success: function (response) {
                jQuery('.response').html(response);
            },
            error: function (response) {
                jQuery('.response').html(response);
            },
            complete: function () {
                jQuery('.modal-loader').hide();
                action_for_recognize_files();
            }
        });

        // console.log(directory);

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
        directory = jQuery('.make_import_form #directory').val();
        jQuery('.directory').val(directory);

        if ((file_position_upload >= total_files_for_upload) || (typeof data_for_recognize['filenames'][file_position_upload] == 'undefined')) {
            clearTimeout(create_action);
            jQuery('.response').append('<p>Import launched.</p>');
            jQuery('.response').append('<p>Note that the possible Omeka errors during import are reported in the logs.</p>');
            jQuery('.response').find('.total_info').remove();
        } else {
            if (make_action == true) {
                var rowId = data_for_recognize['row_id'][file_position_upload];
                var row = jQuery('.response .isset_yes.row_id_' + rowId);
                data_for_recognize_single = {
                    'data_for_recognize_single' : data_for_recognize['filenames'][file_position_upload],
                    'directory': directory,
                    'delete-file': jQuery('#delete-file').val(),
                    'data_for_recognize_row_id' : rowId,
                };

                jQuery.ajax({
                    url: url,
                    data: data_for_recognize_single,
                    type: 'post',
                    beforeSend: function() {
                        make_action = false;
                        clearTimeout(create_action);
                    },
                    success: function (response) {
                        if (response.length > 1) {
                            var resp = jQuery.parseJSON(response);
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
                        jQuery('.response').html(response);
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
        jQuery('.js-recognize_files').click(function () {

            filenames = [];
            row_id = [];

            jQuery('.response').find('.total_info').remove();

            jQuery('.response .isset_yes').each(function () {
                filenames.push(jQuery(this).find('.filename').text());
                row_id.push(jQuery(this).find('.filename').data('row-id'));
            });

            data_for_recognize = {
                'directory': directory,
                'filenames': filenames,
                'row_id': row_id
            }

            // console.log(data_for_recognize);

            total_files_for_upload = data_for_recognize['filenames'].length;
            make_action = true;
            create_action = setTimeout(make_single_file_upload(file_position_upload), 1000);

        });

    }

    jQuery('#delete-file').click(function () {
        if_checked = jQuery(this).val();

        if (if_checked == 'no') {
            jQuery(this).val('yes');
        } else {
            jQuery(this).val('no')
        }
    })

});
