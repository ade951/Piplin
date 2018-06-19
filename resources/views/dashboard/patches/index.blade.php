<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>打包助手</title>
</head>
<body>
<h2>
    项目名：{{ $project->name }}
</h2>
<form action="{{ $urlCreatePatch }}" method="get">
    <div>
        起始行：
        <select id="select-from" name="from">
            @if (is_array($tags))
            @foreach ($tags as $tag)
                <option value="{{$tag}}">{{$tag}}</option>
            @endforeach
            @endif
        </select>
    </div>
    <div>
        结束行：
        <select id="select-to" name="to">
            @if (is_array($tags))
            @foreach ($tags as $tag)
                <option value="{{$tag}}">{{$tag}}</option>
            @endforeach
            @endif
        </select>
    </div>
    <div>
        <button type="submit">打包</button>
    </div>
</form>
</body>
</html>