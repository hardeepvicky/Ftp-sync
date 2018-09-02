<style>
    .panel-body
    {
        padding: 0;
    }
    .table
    {
        margin-bottom: 0;
    }

    .error-msg
    {
        display: none;
    }

    .gly-spin {
        -webkit-animation: spin 2s infinite linear;
        -moz-animation: spin 2s infinite linear;
        -o-animation: spin 2s infinite linear;
        animation: spin 2s infinite linear;
    }
    @-moz-keyframes spin {
        0% {
            -moz-transform: rotate(0deg);
        }
        100% {
            -moz-transform: rotate(359deg);
        }
    }
    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(359deg);
        }
    }
    @-o-keyframes spin {
        0% {
            -o-transform: rotate(0deg);
        }
        100% {
            -o-transform: rotate(359deg);
        }
    }
    @keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(359deg);
            transform: rotate(359deg);
        }
    }
</style>

<div id="progress">
    <div class="row">
        <div class="col-md-4">
            Total Data : <strong><span class="total-bytes">0</span></strong>,
            Sent : <strong><span class="upload-bytes">0</span></strong>
        </div>
        <div class="col-md-4 text-center">
            Upload Speed : <strong><span class="upload-speed">0</span></strong>
        </div>
        <div class="col-md-4 text-right">
            <strong><span class="percentage">0%</span></strong> complete
        </div>
    </div>
    <div class="progress">    
        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
            <span class="sr-only">0% Complete</span>
        </div>
    </div>
</div>

<div class="alert alert-danger error-msg" role="alert">
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading clearfix">
                <h4 class="panel-title pull-left" style="width : 70%; padding-top: 7px;">
                    Local Changes
                    <span id="track-changes-loader" class="glyphicon glyphicon-refresh gly-spin"></span>
                    <br/>
                    Project Path : <b><?= PROJECT_PATH; ?></b> 
                </h4>
                <div class="pull-right" style="width : 20%; text-align: right;">
                    <button class="btn btn-primary" id="get-local-file-chages">
                        <i class="glyphicon glyphicon-refresh"></i>
                    </button>
                    <button class="btn btn-primary" id="ftp-link-upload">
                        <i class="glyphicon glyphicon-cloud-upload"></i>
                    </button>
                </div>
            </div>
            <div id="local" class="panel-body tf-loader-wrapper" style="height : 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <td>Type</td>
                            <td>File</td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6" id="ftp-block">

        <div class="panel panel-info">
            <div class="panel-heading clearfix">         
                <h4 class="panel-title pull-left" style="width : 70%; padding-top: 7px;">
                    FTP (<?= FTP_SERVER ?>)
                    <span id="track-ftp-files-loader" class="glyphicon glyphicon-refresh gly-spin"></span>
                    <br/>
                    FTP Path : <b><?= FTP_PROJECT_PATH ?></b>
                </h4>
                <div class="pull-right" style="width : 20%; text-align: right;">
                    <button class="btn btn-primary" id="get-ftp-files">
                        <i class="glyphicon glyphicon-refresh"></i>
                    </button>
                </div>
            </div>
            <div id="ftp" class="panel-body tf-loader-wrapper" style="height : 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <td>File</td>
                            <td>Size</td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var errorObj, get_changes_table, get_ftp_file_table;
    var track_write_changes_contiue = false, track_write_ftp_files_continue = false;
    var last_upload_size = 0;
    $(document).ready(function()
    {
        $("#track-changes-loader, #track-ftp-files-loader").hide();
        
        get_changes_table = $("#local table").DataTable({
            bSearchable: true,
            bFilter: true,
            bSort: true,
            columnDefs: [
                {"width": "10%", "targets": 0}
            ]
        });
        
        get_ftp_file_table = $("#ftp table").DataTable({
            bSearchable: true,
            bFilter: true,
            bSort: true,
            columnDefs: [
                {"width": "75%", "targets": 0},
                {"width": "25%", "targets": 0}
            ]
        });

        $("#progress").hide();
        errorObj = $(".error-msg");
        errorObj.hide();

        $("#get-local-file-chages").click(function()
        {
            $.get("<?= url("write_changes", array(), "ajax") ?>", function(data, status)
            {
                track_write_changes_contiue = false;
                if (status != "success")
                {
                    errorObj.html(data).show();
                    return;
                }
                
                if (data != "1")
                {
                    errorObj.html(data).show();
                    return;
                }

            }).fail(function(data)
            {
                errorObj.html(data.responseText).show();
            });

            track_write_changes_contiue = true;
            $("#track-changes-loader").show();
            setTimeout(function() {
                track_write_changes(0);
            }, 1000);
        });

        function track_write_changes(counter)
        {
            $.get('<?= url("get_changes", array(), "ajax") ?>', function(data, status)
            {
                if (status != "success")
                {
                    errorObj.html(data).show();
                    return;
                }
                else
                {
                    try
                    {
                        data = JSON.parse(data);
                    }
                    catch (e)
                    {
                        errorObj.html(data).show();
                        return;
                    }

                    get_changes_table.rows().remove();
                    var list = {
                        A : "Add",
                        C : "Change",
                        D : "Delete"
                    }
                    for (var i in data)
                    {
                        get_changes_table.row.add([
                            list[data[i]['type']],
                            data[i]['name']
                        ]);
                    }

                    get_changes_table.draw(false);

                    if (!track_write_changes_contiue)
                    {
                        $("#track-changes-loader").hide();
                        return;
                    }
                    else
                    {
                        setTimeout(function() {
                            track_write_changes(counter + 1);
                        }, 2000);
                    }
                    
                }
            }).fail(function(data)
            {
                $("#track-changes-loader").hide();
                track_changes_contiue = false;
                errorObj.html(data.responseText).show();
            });
        }

        $("#get-ftp-files").click(function()
        {
            $.get("<?= url("write_ftp_files", array(), "ajax") ?>", function(data, status)
            {
                track_write_ftp_files_continue = false;
                if (status != "success")
                {
                    errorObj.html(data).show();
                    return;
                }
                
                if (data != "1")
                {
                    errorObj.html(data).show();
                    return;
                }

            }).fail(function(data)
            {
                errorObj.html(data.responseText).show();
            });

            track_write_ftp_files_continue = true;
            $("#track-ftp-files-loader").show();
            setTimeout(function() {
                track_write_ftp_files(0);
            }, 1000);
        });

        function track_write_ftp_files(counter)
        {
            $.get('<?= url("get_ftp_files", array(), "ajax") ?>', function(data, status)
            {
                if (status != "success")
                {
                    errorObj.html(data).show();
                    return;
                }
                else
                {
                    try
                    {
                        data = JSON.parse(data);
                    }
                    catch (e)
                    {
                        errorObj.html(data).show();
                        return;
                    }

                    get_ftp_file_table.rows().remove();
                    for (var i in data)
                    {
                        get_ftp_file_table.row.add([
                            data[i]['name'],
                            niceBytes(data[i]['size'])
                        ]);
                    }

                    get_ftp_file_table.draw(false);

                    if (!track_write_ftp_files_continue)
                    {
                        $("#track-ftp-files-loader").hide();
                        return;
                    }
                    else
                    {
                        setTimeout(function() {
                            track_write_ftp_files(counter + 1);
                        }, 2000);
                    }
                    
                }
            }).fail(function(data)
            {
                $("#track-ftp-files-loader").hide();
                track_write_ftp_files_continue = false;
                errorObj.html(data.responseText).show();
            });
        }

        $("#ftp-link-upload").click(function()
        {
            if (!get_changes_table.data().count())
            {
                alert("No Data to Upload");
                return;
            }
            
            errorObj.hide();

            upload_continue = true;
            $("#progress").show();

            var filename = "files/log_push_" + (new Date()).getTime() + ".csv";
            $.post('<?= url("ftp_upload", array(), "ajax") ?>', {filename: filename}, function(data, status)
            {
                upload_continue = false;
                if (status != "success")
                {
                    errorObj.html(data).show();
                    return;
                }

                if (data != "1")
                {
                    errorObj.html(data).show();
                    return;
                }
            }).fail(function(data)
            {
                upload_continue = false;
                errorObj.html(data.responseText).show();
            });

            track_ftp_upload(filename);
        });
    });

    
    function track_ftp_upload(filename)
    {
        $.post('<?= url("ftp_track_upload", array(), "ajax") ?>', {filename: filename}, function(data, status)
        {
            if (status != "success")
            {
                errorObj.html(data).show();
                return;
            }
            else
            {
                try
                {
                    data = JSON.parse(data);
                }
                catch (e)
                {
                    errorObj.html(data).show();
                    return;
                }

                var per = Math.round(data.done_count * 100 / data.total_count);
                $("#progress .progress-bar").css("width", per + "%");
                $("#progress .percentage").html(per + "%");

                var diff = data['upload_bytes'] - last_upload_size;

                if (diff == 0 && last_upload_size > 0)
                {
                }
                else
                {
                    $("#progress .upload-speed").html(niceBytes(diff) + "/sec");
                }

                $("#progress .total-bytes").html(niceBytes(data['total_bytes']));
                $("#progress .upload-bytes").html(niceBytes(data['upload_bytes']));

                last_upload_size = data['upload_bytes'];

                if (upload_continue)
                {
                    setTimeout(function() {
                        track_ftp_upload(filename);
                    }, 1000);
                }
            }
        }).fail(function(data)
        {
            process_contiue = false;
            errorObj.html(data.responseText).show();
        });
    }

    function niceBytes(bytes, count)
    {
        if (typeof count == "undefined")
        {
            count = 0;
        }

        if (bytes > 1024)
        {
            return niceBytes(Math.round(bytes / 1024, 2), count + 1)
        }

        var sizes = ["Byte", "Kb", "Mb", "Gb"];

        return bytes + " " + sizes[count];
    }
</script>
