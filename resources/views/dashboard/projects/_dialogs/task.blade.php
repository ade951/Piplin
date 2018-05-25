<div class="modal fade" id="task" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><i class="piplin piplin-deploy"></i> <span>{{ trans('tasks.deploy') }}</span></h4>
            </div>
            <form class="form-horizontal" role="form">
                <input type="hidden" name="project_id" value="{{ $project->id }}" />
                <input type="hidden" name="targetable_type" value="{{ $targetable_type }}" />
                <input type="hidden" name="targetable_id" value="{{ $targetable_id }}" />
                <div class="modal-body">

                    <div class="callout callout-danger">
                        <i class="icon piplin piplin-warning"></i> {{ trans('tasks.warning') }}
                    </div>
                    @if (isset($deployPlan))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_reason">{{ trans('tasks.version_name') }}</label>
                        <div class="col-sm-9">
                            <textarea rows="1" id="task_version_name" class="form-control" name="version_name" placeholder="{{ trans('tasks.describe_version_name') }}"></textarea>
                        </div>
                    </div>
                    @endif
                    @if (count($environments))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="environments">{{ trans('tasks.environments') }}</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($environments as $each)
                            <li>
                                <div class="checkbox">
                                    <label for="task_environment_{{ $each->id }}">
                                        <input type="checkbox" class="task-environment" name="environments[]" id="task_environment_{{ $each->id }}" value="{{ $each->id }}" @if ($each->default_on === true) checked @endif/> {{ $each->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
						</div>
                    </div>
                    @endif
                    @if (count($branches) || count($tags) || count($releases))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_source">{{ trans('tasks.source') }} <i class="piplin piplin-clock" data-toggle="tooltip" data-placement="right" title="{{ trans('projects.last_mirrored') }}: {{ $project->last_mirrored }}"></i></label>
						<div class="col-sm-9">
							<ul class="list-unstyled">
                                <li>
                                    <div class="radio">
                                        <label for="task_source_commit">
                                            <input type="radio" class="task-source" name="source" id="task_source_commit" value="commit" checked /> {{ trans('tasks.commit') }}

                                            <div class="task-source-container">
                                                <select class="form-control task-source" name="source_commit" id="task_commit">
                                                    @if (isset($commits))
                                                    @foreach ($commits as $commit)
                                                    <option value="{{ $commit['hash'] }}" data-msg="{{ $commit['message'] }}">{{ $commit['detail_msg'] }}</option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </label>
                                    </div>
                                </li>
								<li>
									<div class="radio">
										<label for="task_source_default">
											<input type="radio" class="task-source" name="source" id="task_source_default" value="{{ $project->branch }}" /> {{ trans('tasks.default', [ 'branch' => $project->branch ]) }}
										</label>
									</div>
								</li>
                                @if ($project->allow_other_branch)
								@if (count($branches))
								<li>
									<div class="radio">
										<label for="task_source_branch">
											<input type="radio" class="task-source" name="source" id="task_source_branch" value="branch" /> {{ trans('tasks.different_branch') }}

											<div class="task-source-container">
												<select class="form-control task-source" name="source_branch" id="task_branch">
													@foreach ($branches as $branch)
														<option value="{{ $branch }}">{{ $branch }}</option>
													@endforeach
												</select>
											</div>
										</label>
									</div>
								</li>
								@endif

								@if (count($tags))
								<li>
									<div class="radio">
										<label for="task_source_tag">
											<input type="radio" class="task-source" name="source" id="task_source_tag" value="tag" /> {{ trans('tasks.tag') }}
											<div class="task-source-container">
												<select class="form-control task-source" name="source_tag" id="task_tag">
													@foreach ($tags as $tag)
														<option value="{{ $tag }}">{{ $tag }}</option>
													@endforeach
												</select>
											</div>
										</label>
									</div>
								</li>
								@endif
                                @endif
                                @if(isset($releases) && count($releases))
                                <li>
                                    <div class="radio">
                                        <label for="task_source_release">
                                            <input type="radio" class="task-source" name="source" id="task_source_release" value="release" @if(isset($release_id)) checked @endif/> {{ trans('tasks.release') }}
                                            <div class="task-source-container">
                                                <select class="form-control task-source" name="source_release" id="task_release">
                                                    @foreach ($releases as $release)
                                                        <option value="{{ $release->id }}" @if(isset($release_id) && $release_id == $release->id) selected @endif>{{ $release->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </label>
                                    </div>
                                </li>
                                @endif
							</ul>
						</div>
                    </div>
                    <hr>
                    @if (isset($buildPlan))
                    <div class="form-group">
                        <label for="task_vsign" class="col-sm-3 control-label">项目签名 <i class="piplin piplin-info" data-toggle="tooltip" data-placement="right" title="填写客户信息，如填写公司名或域名，可用于盗版系统来源跟踪"></i></label>
                        <div class="col-sm-9">
                            <input class="form-control" type="text" id="task_vsign">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="task_encrypt" class="col-sm-3 control-label">源码加密</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="task_encrypt">
                                    <input type="checkbox" id="task_encrypt" name="is_encrypt" checked> 启用
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group task-encrypt-area">
                        <label for="task_encrypt_domain" class="col-sm-3 control-label">域名限制 <i class="piplin piplin-info" data-toggle="tooltip" data-placement="right" title="可运行加密代码的机器的hostname，此选项留空或者0代表不限制运行加密代码的机器的域名(可以是多个域名 用“,”隔离即可,支持*前缀 例如*.swoole.com代表允许所有swoole.com的二级域名运行加密文件)"></i></label>
                        <div class="col-sm-9">
                            <input class="form-control" type="text" id="task_encrypt_domain" name="domain_restriction">
                        </div>
                    </div>
                    <div class="form-group task-encrypt-area">
                        <label for="task_encrypt" class="col-sm-3 control-label">PHP版本 <i class="piplin piplin-info" data-toggle="tooltip" data-placement="right" title="加密后的代码只能在特定的PHP版本上运行"></i></label>
                        <div class="col-sm-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="php_version" id="phpVersion1" value="7.2">
                                    <label class="form-check-label" for="phpVersion1" style="width: 50px;">
                                        7.2
                                    </label>

                                    <input class="form-check-input" type="radio" name="php_version" id="phpVersion2" value="7.1">
                                    <label class="form-check-label" for="phpVersion2" style="width: 50px;">
                                        7.1
                                    </label>

                                    <input class="form-check-input" type="radio" name="php_version" id="phpVersion3" value="7.0" checked="">
                                    <label class="form-check-label" for="phpVersion3" style="width: 50px;">
                                        7.0
                                    </label>

                                    <input class="form-check-input" type="radio" name="php_version" id="phpVersion3" value="5.6">
                                    <label class="form-check-label" for="phpVersion3" style="width: 50px;">
                                        5.6
                                    </label>

                                    <!--<input class="form-check-input" type="radio" name="php_version" id="phpVersion3" value="5.5">-->
                                    <!--<label class="form-check-label" for="phpVersion3">-->
                                    <!--    5.5-->
                                    <!--</label>-->
                                    <!--<input class="form-check-input" type="radio" name="php_version" id="phpVersion3" value="5.4">-->
                                    <!--<label class="form-check-label" for="phpVersion3">-->
                                    <!--    5.4-->
                                    <!--</label>-->
                                </div>
                        </div>
                    </div>
                    <hr />
                    @endif
                    @endif
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_reason">{{ trans('tasks.reason') }}</label>
						<div class="col-sm-9">
                            <div class="checkbox">
                                <label for="btn_use_commit">
                                    <input type="checkbox" id="btn_use_commit" > 使用代码提交信息
                                </label>
                            </div>
                            <div style="padding-top: 7px;">
                                <textarea rows="3" id="task_reason" class="form-control" name="reason" placeholder="{{ trans('tasks.describe_reason') }}"></textarea>
                            </div>
						</div>
                    </div>
                    @if (count($optional))
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="task_commands">{{ trans('tasks.optional') }}</label>
						<div class="col-sm-9">
                        <ul class="list-unstyled">
                            @foreach ($optional as $command)
                            <li>
                                <div class="checkbox">
                                    <label for="task_command_{{ $command->id }}">
                                        <input type="checkbox" class="task-command" name="optional[]" id="task_command_{{ $command->id }}" value="{{ $command->id }}" @if ($command->default_on === true) checked @endif/> {{ $command->name }}
                                    </label>
                                </div>
                            </li>
                            @endforeach
                        </ul>
						</div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-save"><i class="piplin piplin-save"></i> <span>{{ trans('projects.deploy') }}</span></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
