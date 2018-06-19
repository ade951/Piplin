<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>打包助手-下载</title>
</head>
<body>
<h2>
    项目名：{{ $project->name }}
</h2>

@if($errorOutput)
<div>
    错误调试信息：
    <pre>
        {{ $errorOutput }}
    </pre>
    <hr>
    <pre>
        {{ $output }}
    </pre>
</div>
@else
<div>
    点击<a href="{{ $url }}">下载</a>
</div>
@endif


</body>
</html>