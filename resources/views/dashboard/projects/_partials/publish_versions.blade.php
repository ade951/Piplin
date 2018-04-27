<div class="box">
    <div class="box-header">
        <h3 class="box-title">{{ trans('publish_versions.label') }}</h3>
    </div>

    @if (!count($publish_versions))
    <div class="box-body">
        <p>{{ trans('publish_versions.none') }}</p>
    </div>
    @else
    <div class="box-body table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>{{ trans('publish_versions.version_no') }}</th>
                <th>{{ trans('publish_versions.description') }}</th>
                <th>{{ trans('publish_versions.git_version') }}</th>
                <th>{{ trans('app.status') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($publish_versions as $version)
            <tr id="version_{{ $version->id }}">
                <td>{{ $version->version_name }}</td>
                <td>{{ $version->description }}</td>
                <td>
                    <a href="http://gitee.com/YiXinKeJi/QianBao/commit/{{$version->commit}}" target="_blank">{{ substr($version->commit, 0, 7) }}</a>({{ $version->branch }})
                </td>
                <td class="status">
                    <span class="text-{{$version->css_class}}"><i class="piplin piplin-{{ $version->icon }}"></i> <span>{{ $version->readable_status }}</span></span>
                </td>
                <td>
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-success" onclick="enable_version({{$version->id}})">启用</button>
                        <button type="button" class="btn btn-danger" onclick="disable_version({{$version->id}})">禁用</button>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        {!! $publish_versions->render() !!}
    </div>

    @endif
    <script>
        function enable_version(id) {
            $.ajax({
                url: 'enable_version/' + id,
                type: 'post',
                success: function (data) {
                    alert(data);
                    location.reload();
                },
            });
        }
        function disable_version(id) {
            $.ajax({
                url: 'disable_version/' + id,
                type: 'post',
                success: function (data) {
                    alert(data);
                    location.reload();
                },
            });
        }
    </script>
</div>
