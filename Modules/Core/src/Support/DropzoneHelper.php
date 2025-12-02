<?php

namespace Modules\Core\Support;

class DropzoneHelper
{
    /**
     * Render dropzone HTML for file uploads.
     *
     * @origin Modules/Core/Helpers/dropzone_helper.php
     *
     * @param bool $read_only Whether to display in read-only mode
     */
    public static function _dropzone_html(bool $read_only = true): string
    {
        $hideClass = $read_only ? ' hide' : '';
        $disabled  = $read_only ? 'disabled' : '';

        return <<<BLADE
<div class="panel panel-default no-margin">
    <div class="panel-heading">{{ trans('attachments') }}</div>

    <div class="panel-body clearfix">
        <button type="button" class="btn btn-sm btn-default fileinput-button{$hideClass}" {$disabled}>
            <i class="fa fa-plus"></i> {{ trans('add_files') }}
        </button>

        @unless(\$read_only)
            <button type="button" class="btn btn-sm btn-danger removeAllFiles-button pull-right hidden">
                <i class="fa fa-trash-o"></i> {{ trans('delete_attachments') }}
            </button>
        @endunless

        <div class="row">
            <div id="actions" class="col-xs-12">
                <div class="col-xs-12 col-md-6 col-lg-7"></div>
                <div class="col-xs-12 col-md-6 col-lg-5">
                    <div class="fileupload-process">
                        <div id="total-progress" class="progress progress-striped active"
                             role="progressbar"
                             aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                        </div>
                    </div>
                </div>

                <div id="previews" class="table table-condensed files no-margin">
                    <div id="template" class="row file-row">
                        <div class="col-xs-3 col-md-4">
                            <span class="preview pull-left"><img data-dz-thumbnail/></span>
                        </div>
                        <div class="col-xs-5 col-md-4">
                            <p class="size pull-left" data-dz-size></p>
                            <div class="progress progress-striped active pull-right" role="progressbar"
                                 aria-valuemin="0"
                                 aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%" data-dz-uploadprogress></div>
                            </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                            <div class="pull-right btn-group">
                                <button data-dz-download class="btn btn-sm btn-primary">
                                    <i class="fa fa-download"></i>
                                    <span>{{ trans('download') }}</span>
                                </button>
                                @unless(\$read_only)
                                    <button data-dz-remove class="btn btn-sm btn-danger delete">
                                        <i class="fa fa-trash-o"></i>
                                        <span>{{ trans('delete') }}</span>
                                    </button>
                                @endunless
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-8">
                            <p class="name pull-left" data-dz-name></p>
                            <strong class="error text-danger pull-right" data-dz-errormessage></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
BLADE;
    }

    /**
     * Render dropzone JavaScript configuration.
     *
     * @origin Modules/Core/Helpers/dropzone_helper.php
     *
     * @param string|null $url_key      URL key for upload endpoint
     * @param int         $client_id    Client ID
     * @param string      $site_url     Site URL
     * @param array|null  $acceptedExts Accepted file extensions
     */
    public static function _dropzone_script($url_key = null, $client_id = 1, $site_url = '', $acceptedExts = null): void
    {
        $site_url = url(mb_rtrim($site_url ?? 'upload', '/') . '/');

        $content_types = [];
        if ($acceptedExts === null) {
            $content_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
        } elseif (is_array($acceptedExts)) {
            $content_types = $acceptedExts;
        }

        $guest = $acceptedExts === false ? 'true' : 'false';
        ?>
        <script>
            const
                site_url        = '<?php echo $site_url; ?>',
                client_id       = '/<?php echo $client_id; ?>',
                url_key         = '/<?php echo $url_key; ?>',
                url_get_file    = site_url + 'get_file'    + url_key,
                url_show_file   = site_url + 'show_files'  + url_key,
                url_delete_file = site_url + 'delete_file' + url_key,
                url_upload_file = site_url + 'upload_file' + client_id + url_key,
                acceptedExts    = '.<?php echo implode(',.', $content_types); ?>';

            function getIcon(fullname) {
                var fileFormat = fullname.match(/\.([A-z0-9]{1,5})$/);
                if (fileFormat) {
                    fileFormat = fileFormat[1].toLowerCase();
                } else {
                    fileFormat = '';
                }

                var fileIcon = 'default';

                switch (fileFormat) {
                    case 'pdf':
                        fileIcon = 'file-pdf';
                        break;
                    case 'mp3':
                    case 'wav':
                    case 'oga':
                    case 'ogg':
                        fileIcon = 'file-audio';
                        break;
                    case 'doc':
                    case 'docx':
                    case 'odt':
                        fileIcon = 'file-document';
                        break;
                    case 'xls':
                    case 'xlsx':
                    case 'ods':
                        fileIcon = 'file-spreadsheet';
                        break;
                    case 'ppt':
                    case 'pptx':
                    case 'odp':
                        fileIcon = 'file-presentation';
                        break;
                }
                return asset('assets/core/img/file-icons/') . $fileIcon . '.svg';
            }

            function sanitizeName(filename) {
                return filename.trim().replace(/[^\p{L}\p{N}\s\-_'\’.]/gu, '');
            }

            const is_guest = <?php echo $guest; ?>;
            const removeAllFilesButton = document.querySelector('.removeAllFiles-button');

            var previewNode = document.querySelector('#template');
            previewNode.id = '';
            var previewTemplate = previewNode.parentNode.innerHTML;
            previewNode.parentNode.removeChild(previewNode);

            var myDropzone = new Dropzone(document.body, {
                url: url_upload_file,
                thumbnailWidth: 80,
                thumbnailHeight: 80,
                parallelUploads: 20,
                uploadMultiple: false,
                dictFileTooBig: `{{ trans('upload_dz_invalid_file_size') }}`,
                dictFileSizeUnits: {{{ trans('upload_dz_size_units') }}},
                dictRemoveFileConfirmation: `{{ trans('delete_attachment_warning') }}`,
                dictInvalidFileType: `{{ trans('upload_dz_invalid_file_type') }}`,
                acceptedFiles: acceptedExts,
                previewTemplate: previewTemplate,
                autoQueue: true,
                previewsContainer: '#previews',
                clickable: '.fileinput-button',
                init: function () {
                    thisDropzone = this;
                    $.getJSON(
                        url_show_file,
                        function (data) {
                            $.each(data, function (index, val) {
                                displayExistingFile(val);
                            });
                            thisDropzone.files.length && !is_guest && removeAllFilesButtonShow(true);
                        }
                    );
                },
            });

            myDropzone.on('complete', function (file) {
                if (file.xhr && file.xhr.responseURL.match(/sessions\/login/) !== null) {
                    this.emit('error', file, `{{ trans('upload_dz_disconnected') }}`);
                }
            });

            myDropzone.on('error', function (file, message) {
                <?php echo (config('app.debug') ? 'console.log("dropzone error", file, message, this);' : '') . PHP_EOL; ?>
                alert(file.name + "\n\n" + message + (file.accepted ? '' : "\n\n(📎👌: " + this.options.acceptedFiles.replace(/\./g, ' ').trim() + ')'));
                file.previewElement.remove();
                this.files.pop();
            });

            myDropzone.on('addedfile', function (file) {
                changeTextName(file);
                createDownloadButton(file);
                this.emit('thumbnail', file, getIcon(file.name));
            });

            myDropzone.on('totaluploadprogress', function (progress) {
                document.querySelector('#total-progress .progress-bar').style.width = progress + '%';
            });

            <?php if ($acceptedExts !== false): ?>
            myDropzone.on('sending', function (file, xhr, formData) {
                formData.append('_token', '{{ csrf_token() }}');
                document.querySelector('#total-progress').style.opacity = '1';
            });

            myDropzone.on('queuecomplete', function () {
                document.querySelector('#total-progress').style.opacity = '0';
                window.setTimeout(function () {
                    document.querySelector('#total-progress .progress-bar').style.width = '0%';
                }, 300);
                this.files.length && removeAllFilesButtonShow(true);
            });

            myDropzone.on('removedfile', function (file) {
                removeAllFilesButton.disabled = true;
                var val = file;
                $.post({
                    url: url_delete_file,
                    data: {
                        name: encodeURIComponent(sanitizeName(file.name))
                    },
                    statusCode: {
                        410: function (response) {
                            alert(sanitizeName(val.name) + "\n\n" + response.responseText);
                            displayExistingFile(val);
                        }
                    }
                })
                    .done(function (response) {
                        if (response.match(/DOCTYPE/i) !== null) {
                            alert(sanitizeName(val.name) + "\n\n" + `{{ trans('upload_dz_disconnected') }}`);
                            displayExistingFile(val);
                            return;
                        }
                        removeAllFilesButton.disabled = false;
                    })
                    .always(function () {
                        myDropzone.files.length || removeAllFilesButtonShow(false);
                        document.querySelector('#total-progress .progress-bar').style.width = '0%';
                    });
            });

            removeAllFilesButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                confirm(`{{ trans('delete_attachments_warning') }}`) && myDropzone.removeAllFiles();
            });

            function removeAllFilesButtonShow(show) {
                if (show) {
                    removeAllFilesButton.classList.remove('hidden');
                } else {
                    removeAllFilesButton.classList.add('hidden');
                }
            }
            <?php endif; ?>

            function displayExistingFile(val) {
                var name = sanitizeName(val.name);
                var imageUrl = !name.match(/\.(avif|gif|jpe?g|png|svg|webp)$/i)
                    ? getIcon(name)
                    : url_get_file + '_' + encodeURIComponent(name);

                var mockFile = {
                    size: val.size,
                    name: name,
                    imageUrl: url_get_file + '_' + encodeURIComponent(name)
                };

                myDropzone.displayExistingFile(
                    mockFile,
                    imageUrl,
                    null,
                    null,
                    false
                );
                myDropzone.files.push(mockFile);
                myDropzone.emit('success', mockFile);
            }

            function changeTextName(file) {
                for (var node of file.previewElement.querySelectorAll('[data-dz-name]')) {
                    node.textContent = sanitizeName(file.name);
                }
            }

            function createDownloadButton(file) {
                for (var node of file.previewElement.querySelectorAll('[data-dz-download]')) {
                    node.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        location.href = url_get_file + '_' + encodeURIComponent(sanitizeName(file.name));
                        return false;
                    });
                }
            }
        </script>
        <?php
    }
}
