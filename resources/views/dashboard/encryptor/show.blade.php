<!doctype html>
<html lang="en">
<head>
    <title>PHP加密</title>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous">

</head>
<body>
<div class="container">
    <h1>PHP源码加密器</h1>
    <form method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <!--<div class="form-group row">-->
        <!--    <label for="srcDir" class="col-sm-2 col-form-label">源码目录</label>-->
        <!--    <div class="col-sm-10">-->
        <!--        <input class="form-control" id="srcDir" value="/root/phpencrypt/src/ProjectName">-->
        <!--    </div>-->
        <!--</div>-->
        <!--<div class="form-group row">-->
        <!--    <label for="tarPath" class="col-sm-2 col-form-label">加密打包文件路径</label>-->
        <!--    <div class="col-sm-10">-->
        <!--        <input class="form-control" id="tarPath"-->
        <!--               value="/root/phpencrypt/dist/ProjectName.tar.gz">-->
        <!--    </div>-->
        <!--</div>-->
        <div class="form-group row">
            <label for="files" class="col-sm-2 col-form-label">文件</label>
            <div class="col-sm-10">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFile">
                    <label class="custom-file-label" for="customFile">请选择文件</label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="domain" class="col-sm-2 col-form-label">授权域名</label>
            <div class="col-sm-10">
                <input class="form-control" id="domain" name="domain" value="" placeholder="示例：k.xinlis.com">
            </div>
        </div>
        <fieldset class="form-group">
            <div class="row">
                <legend class="col-form-label col-sm-2 pt-0">PHP版本</legend>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion1" value="7.2">
                        <label class="form-check-label" for="phpVersion1">
                            7.2
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion2" value="7.1">
                        <label class="form-check-label" for="phpVersion2">
                            7.1
                        </label>
                    </div>
                    <div class="form-check ">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion3" value="7.0" checked>
                        <label class="form-check-label" for="phpVersion3">
                            7.0
                        </label>
                    </div>
                    <div class="form-check ">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion4" value="5.6">
                        <label class="form-check-label" for="phpVersion4">
                            5.6
                        </label>
                    </div>
                    <div class="form-check ">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion5" value="5.5">
                        <label class="form-check-label" for="phpVersion5">
                            5.5
                        </label>
                    </div>
                    <div class="form-check ">
                        <input class="form-check-input" type="radio" name="phpVersion"
                               id="phpVersion6" value="5.4">
                        <label class="form-check-label" for="phpVersion6">
                            5.4
                        </label>
                    </div>
                </div>
            </div>
        </fieldset>

        <hr>

        <div class="form-group row">
            <label for="ip" class="col-sm-2 col-form-label">授权IP地址</label>
            <div class="col-sm-10">
                <input class="form-control" id="ip" name="ip" value="0" placeholder="">
            </div>
        </div>
        <div class="form-group row">
            <label for="expire" class="col-sm-2 col-form-label">过期时间</label>
            <div class="col-sm-10">
                <input class="form-control" id="expire" name="expire" value="0" placeholder="">
            </div>
        </div>
        <div class="form-group row">
            <label for="mac" class="col-sm-2 col-form-label">授权Mac地址</label>
            <div class="col-sm-10">
                <input class="form-control" id="mac" name="mac" value="0" placeholder="示例：08:00:27:fe:74:52">
            </div>
        </div>
        <div class="form-group row">
            <label for="os" class="col-sm-2 col-form-label">授权域名</label>
            <div class="col-sm-10">
                <input class="form-control" id="os" name="os" value="linux" placeholder="示例：linux">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label"></label>
            <div class="col-sm-2">
                <input type="submit" class="form-control btn btn-primary">
            </div>
        </div>

    </form>
    <hr>

    <div class="form-group row">
        <label for="cmd" class="col-sm-2 col-form-label">生成命令：</label>
        <div class="col-sm-10">
            <input class="form-control" id="cmd" value="" placeholder="">
        </div>
    </div>

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<script>
    $('form').delegate("input", "change keydown", function () {
        genCommand();
    });
    function genCommand() {
        var command = 'swoole-compiler ' + $('input[name=phpVersion]:checked').val() + ' ' + $('#srcDir').val() + ' ' + $('#tarPath').val()
            + ' ' + $('#expire').val() + ' ' + $('#mac').val() + ' ' + $('#ip').val() + ' ' + $('#domain').val() + ' ' + $('#os').val();
        $('#cmd').val(command);
    }

    $('#customFile').on('change', function(){$('.custom-file-label').text($(this).val())})
</script>
</body>
</html>