$(function () {
    var inputs = document.querySelectorAll('input[type=file].fileinput');
    for (var i = 0; i < inputs.length; i++) {
        handleFileinput(inputs.item(i));
    }
});


function handleFileinput(input) {
    var maxFileSize = input.hasAttribute('data-max-file-size') ? input.getAttribute('data-max-file-size') : 100000;

    var deletedFiles = document.getElementById(input.getAttribute('_delete'));

    var removeImage = function (id, thumbnail) {
        var deletedFileIds = deletedFiles.value.length > 0 ? deletedFiles.value.split(',') : [];
        deletedFileIds.push(id);
        deletedFiles.value = deletedFileIds.join(',');

        thumbnail.remove();
        return false;
    };

    var bindRemoveButton = function (button) {
        var clone = button.cloneNode(true);
        button.parentNode.replaceChild(clone, button);
        clone.disabled = false;
        clone.classList.remove('disabled');
        clone.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            return removeImage(this.getAttribute('data-key'), this.parentNode.parentNode.parentNode.parentNode);
        });
    };

    var layoutTemplates = {};
    var legend = input.getAttribute('_name');
    if (legend && legend.length > 0) {
        legend = legend + (input.multiple ? '[{dataKey}]' : '');
        layoutTemplates.footer = '<div class="file-thumbnail-footer">\n' +
            '<div class="input-group"><input type="text" name="' + legend + '" placeholder="Name" class="form-control">{actions}</div>\n' +
            '</div>';

        layoutTemplates.actions = '<div class="input-group-btn">{delete} {zoom} {drag}</div>';
        layoutTemplates.actionDelete = '<button type="button" class="kv-file-remove btn btn-default" title="{removeTitle}"{dataKey}>{removeIcon}</button>\n';
    }

    var files = JSON.parse(input.getAttribute('data-files'));
    $(input).fileinput({
        language: input.hasAttribute('data-locale') ? input.getAttribute('data-locale') : 'en',
        initialPreview: files.map(function (file) {
            return '<img src="' + file.path + '" class="file-preview-image"/>';
        }),
        initialPreviewConfig: files.map(function (file) {
            return {key: file.id};
        }),
        maxFileSize: maxFileSize,
        initialPreviewCount: true,
        browseIcon: '<i class="icon-browse"></i>',
        fileActionSettings: {
            removeIcon: '<i class="icon-remove"></i>',
        },
        showUpload: false,
        showRemove: input.multiple,
        validateInitialCount: true,
        overwriteInitial: !input.multiple,
        layoutTemplates: layoutTemplates,
        previewFileIconSettings: {
            doc: '<i class="fa fa-file-word-o text-primary"></i>',
            xls: '<i class="fa fa-file-excel-o text-success"></i>',
            ppt: '<i class="fa fa-file-powerpoint-o text-danger"></i>',
            jpg: '<i class="fa fa-file-photo-o text-warning"></i>',
            pdf: '<i class="fa fa-file-pdf-o text-danger"></i>',
            zip: '<i class="fa fa-file-archive-o text-muted"></i>',
            htm: '<i class="fa fa-file-code-o text-info"></i>',
            txt: '<i class="fa fa-file-text-o text-info"></i>',
            mov: '<i class="fa fa-file-movie-o text-warning"></i>',
            mp3: '<i class="fa fa-file-audio-o text-warning"></i>',
        },
        previewFileExtSettings: {
            doc: function (ext) {
                return ext.match(/(doc|docx)$/i);
            },
            xls: function (ext) {
                return ext.match(/(xls|xlsx)$/i);
            },
            ppt: function (ext) {
                return ext.match(/(ppt|pptx)$/i);
            },
            jpg: function (ext) {
                return ext.match(/(jp?g|png|gif|bmp)$/i);
            },
            zip: function (ext) {
                return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i);
            },
            htm: function (ext) {
                return ext.match(/(php|js|css|htm|html)$/i);
            },
            txt: function (ext) {
                return ext.match(/(txt|ini|md)$/i);
            },
            mov: function (ext) {
                return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i);
            },
            mp3: function (ext) {
                return ext.match(/(mp3|wav)$/i);
            }
        }
    })
            .on('fileimageloaded', function (event) {
                var deletedFiles = document.getElementById(input.getAttribute('_delete'));
                var deletedFileIds = deletedFiles.value.length > 0 ? deletedFiles.value.split(',') : [];

                $fileinput
                        .find('.kv-file-remove')
                        .each(function (item) {
                            if (deletedFileIds.indexOf($(this).data('key').toString()) > -1) {
                                $(this.parentNode.parentNode.parentNode.parentNode).remove();
                            } else {
                                bindRemoveButton(this);
                            }
                        });

            })
            .on('fileloaded', function (event, file, previewId, index, reader) {
                console.log(event, previewId);
                if (input.multiple && legend) {
                    var preview = document.getElementById(previewId);
                    console.log(preview);
                    var name = preview.querySelector('input[name="' + legend + '"]');
                    name.setAttribute('name', legend.replace('{dataKey}', file.name));
                }
            })
            .on('fileimagesloaded', function (event) {
                console.log(event);
                $fileinput
                    .find('.kv-file-remove')
                    .each(function (item) {
                        var preview = this.parentNode.parentNode.parentNode.parentNode;
                        var name = preview.querySelector('input[name="' + legend + '"]');
                        if (name !== null) {
                            if (typeof(files[item]) === 'object' && files[item].name !== null) {
                                name.value = files[item].name;
                            }
                            if (input.multiple && legend) {
                                name.setAttribute('name', legend.replace('{dataKey}', this.getAttribute('data-key')));
                            }
                        }
                        bindRemoveButton(this);
                    });
            });

    var $fileinput = $(input.parentNode.parentNode.parentNode.parentNode);

    $(input).trigger('fileimagesloaded');

    if (!input.multiple) {
        $(input.parentNode.parentNode.parentNode.parentNode)
                .addClass("file-input-small");
    }
}
