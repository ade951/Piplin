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
                <th>{{ trans('app.status') }}</th>
                <th class="text-right">{{ trans('app.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($publish_versions as $version)
            <tr id="version_{{ $version->id }}">
                <td>{{ $version->version_name }}</td>
                <td>{{ $version->description }}</td>
                <td class="status">
                    <span class="text-{{$version->css_class}}"><i class="piplin piplin-{{ $version->icon }}"></i> <span>{{ $version->readable_status }}</span></span>
                </td>
                <td>
                    <div class="btn-group pull-right">
                        <button>发布</button>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        {!! $publish_versions->render() !!}
    </div>

    @endif
</div>
