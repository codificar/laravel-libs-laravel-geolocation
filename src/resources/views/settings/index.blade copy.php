@extends('layout.master') 
@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">
		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans("setting.conf") }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans("dashboard.home") }}</a></li>
			<li class="breadcrumb-item active">{{ trans("setting.conf") }}</li>
			<li class="breadcrumb-item active">{{ trans("setting.geolocation") }}</li>	
		</ol>
	</div>
</div>	
@stop
<link rel="stylesheet" href="{{ elixir('css/admin.css') }}">
@section('content')
<div class="col-lg-12">
	<div class="card card-outline-info">
		<div class="card-header">
			<h4 class="m-b-0 text-white">{{ trans("setting.places") }}</h4>
		</div>
		<div class="card-block">
				@if (session('alert'))
					<div class="alert alert-success">
						{{ session('alert') }}
					</div>
				@endif
			<form enctype="multipart/form-data" method="post" data-toggle="validator" action="/admin/settings/geolocation/places/save">
				<div class="form-group">
					<!--General Settings-->
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_provider">
											{{trans('setting.geolocation_provider')}}
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_provider')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span> 
										</label>
										<select class="form-control" id="places_provider" required name="places_provider">
												<?php
													if(is_array($enum['places_provider'])) foreach ($enum['places_provider'] as $item) {
														$selected = "";
														if($item['value'] == $model->places_provider->value)
															$selected = "selected";	
														echo '<option '. $selected .' value='.$item['value'].'>'. trans($item['name']) .'</option>';
													}
												?>
											</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_url">
											{{trans('setting.geolocation_url')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_url')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_url" required data-error="{{trans('setting.field')}}" name="places_url"
										value="<?php echo $model->places_url->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_key">
											{{trans('setting.geolocation_key')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_key')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_key" required data-error="{{trans('setting.field')}}" name="places_key"
										value="<?php echo $model->places_key->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_application_id">
											{{trans('setting.geolocation_application_id')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_application_id')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_application_id" required data-error="{{trans('setting.field')}}" name="places_application_id"
										value="<?php echo $model->places_application_id->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
					</div><!-- / General Settings-->
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label>
											{{ trans('setting.able_redundancy_rule') }}
										</label><br />
										<label>
											<input type="radio" name="places_redundancy_rule" class="flat-red" value="0" {{ $model->places_redundancy_rule->value == 0 ? 'checked' : ''}}>
											{{ trans('setting.no') }}
										</label>
										<label>
											<input type="radio" name="places_redundancy_rule" class="flat-red" value="1" {{ $model->places_redundancy_rule->value == 1 ? 'checked' : ''}}>
											{{ trans('setting.yes') }}
										</label>@
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default" id="places_redundancy">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_provider_redundancy">
											{{trans('setting.provider_redundancy')}}
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_provider_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span> 
										</label>
										<select class="form-control" id="places_provider_redundancy" name="places_provider_redundancy">
											<?php
												if(is_array($enum['places_provider'])) foreach ($enum['places_provider'] as $item) {
													$selected = "";
													if($item['value'] == $model->places_provider_redundancy->value)
														$selected = "selected";
													echo '<option '. $selected .' value='.$item['value'].'>'. trans($item['name']) .'</option>';
												}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_url_redundancy">
											{{trans('setting.url_redundancy')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_url_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_url_redundancy" data-error="{{trans('setting.field')}}" name="places_url_redundancy"
										value="<?php echo $model->places_url_redundancy->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_key_redundancy">
											{{trans('setting.key_redundancy')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_key_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_key_redundancy" data-error="{{trans('setting.field')}}" name="places_key_redundancy"
										value="<?php echo $model->places_key_redundancy->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="places_application_id_redundancy">
											{{trans('setting.application_id_redundancy')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.places_application_id_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="places_application_id_redundancy" data-error="{{trans('setting.field')}}" name="places_application_id_redundancy"
										value="<?php echo $model->places_application_id_redundancy->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
					</div><!-- / General Settings-->
					<br id="espacamento2">
					<div class="form-group text-right">
						<button type="submit" class="btn btn-success">
							<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> {{trans('keywords.save')}}
						</button>
					</div>
				</div>	
					
			</form>
			
		</div>
		
	</div>
</div>
<div class="col-lg-12">
	<div class="card card-outline-info">
		<div class="card-header">
			<h4 class="m-b-0 text-white">{{ trans("setting.directions") }}</h4>
		</div>
		<div class="card-block">
				@if (session('alert'))
					<div class="alert alert-success">
						{{ session('alert') }}
					</div>
				@endif
			<form enctype="multipart/form-data" method="post" data-toggle="validator" action="/admin/settings/geolocation/directions/save">
				<div class="form-group">
					<!--General Settings-->
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_provider">
											{{trans('setting.geolocation_provider')}}
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_provider')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span> 
										</label>
										<select class="form-control" id="directions_provider" required name="directions_provider">
												<?php
													if(is_array($enum['directions_provider'])) foreach ($enum['directions_provider'] as $item) {
														$selected = "";
														if($item['value'] == $model->directions_provider->value)
															$selected = "selected";	
														echo '<option '. $selected .' value='.$item['value'].'>'. trans($item['name']) .'</option>';
													}
												?>
											</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_url">
											{{trans('setting.geolocation_url')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_url')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="directions_url" required data-error="{{trans('setting.field')}}" name="directions_url"
										value="<?php echo $model->directions_url->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_key">
											{{trans('setting.geolocation_key')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_key')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="directions_key" required data-error="{{trans('setting.field')}}" name="directions_key"
										value="<?php echo $model->directions_key->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
					</div><!-- / General Settings-->
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-12">
									<div class="form-group">
										<label>
											{{ trans('setting.able_redundancy_rule') }}
										</label><br />
										<label>
											<input type="radio" name="directions_redundancy_rule" class="flat-red" value="0" {{ $model->directions_redundancy_rule->value == 0 ? 'checked' : ''}}>
											{{ trans('setting.no') }}
										</label>
										<label>
											<input type="radio" name="directions_redundancy_rule" class="flat-red" value="1" {{ $model->directions_redundancy_rule->value == 1 ? 'checked' : ''}}>
											{{ trans('setting.yes') }}
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="panel panel-default" id="directions_redundancy">
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_provider_redundancy">
											{{trans('setting.provider_redundancy')}}
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_provider_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span> 
										</label>
										<select class="form-control" id="directions_provider_redundancy" required name="directions_provider_redundancy">
												<?php
													if(is_array($enum['directions_provider'])) foreach ($enum['directions_provider'] as $item) {
														$selected = "";
														if($item['value'] == $model->directions_provider_redundancy->value)
															$selected = "selected";	
														echo '<option '. $selected .' value='.$item['value'].'>'. trans($item['name']) .'</option>';
													}
												?>
											</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_url_redundancy">
											{{trans('setting.url_redundancy')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_url_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="directions_url_redundancy" required data-error="{{trans('setting.field')}}" name="directions_url_redundancy"
										value="<?php echo $model->directions_url_redundancy->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<label for="directions_key_redundancy">
											{{trans('setting.key_redundancy')}} 
											<a href="#" class="question-field" data-toggle="tooltip" title="{{trans('settingTableSeeder.directions_key_redundancy')}}"><span class="mdi mdi-comment-question-outline"></span></a> <span class="required-field">*</span>
										</label>
										<input type="text" class="form-control" id="directions_key_redundancy" required data-error="{{trans('setting.field')}}" name="directions_key_redundancy"
										value="<?php echo $model->directions_key_redundancy->value; ?>">
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</div>
					</div><!-- / General Settings-->
					<br id="espacamento2">
					<div class="form-group text-right">
						<button type="submit" class="btn btn-success">
							<span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> {{trans('keywords.save')}}
						</button>
					</div>
				</div>	
					
			</form>
			
		</div>
		
	</div>
</div>
@stop

@section('javascripts')
{{--  <script src="{{ elixir('js/settings_indication.js') }}"> </script>  --}}
<script src="{{URL::to('js/settings_indication.js')}}"> </script>
<script type="text/javascript">
	$(function(){
		var apis = { 
			'directions': <?php echo intval($model->directions_redundancy_rule->value);?>, 
			'places'	: <?php echo intval($model->places_redundancy_rule->value);?>
		};

		$.each(apis, function( value, index ) {

			/* EVENTS */
			$("#" + value + "_provider").change(function(){
				switch($(this).val()){
					case  "openroute_maps":
						$("#" + value + "_url").parent().css("display","block");
						$("#" + value + "_url").prop("required",true);
						break ;
					case  "pelias_maps":
						$("#" + value + "_url").parent().css("display","block");
						$("#" + value + "_url").prop("required",true);
						$("#" + value + "_key").prop("required",false);
						break ;
					case  "algolia_maps":
						$("#" + value + "_application_id").parent().css("display","block");
				 		$("#" + value + "_application_id").prop("required",true);
						break ;
					default:
						$("#" + value + "_url").parent().css("display","none");
						$("#" + value + "_url").prop("required",false);
						$("#" + value + "_key").prop("required",false);
						$("#" + value + "_application_id").parent().css("display","none");
				 		$("#" + value + "_application_id").prop("required",false);
						break;
				}

			});

			/* EVENTS REDUNDANCY */
			$("#" + value + "_provider_redundancy").change(function(){
				if($("input:radio[name=" + value + "_redundancy_rule][value=1]").is(':checked') === true){
					switch($(this).val()){
						case  "openroute_maps":
							$("#" + value + "_url_redundancy").parent().css("display","block");
							$("#" + value + "_url_redundancy").prop("required",true);
							break ;
						case  "pelias_maps":
							$("#" + value + "_url_redundancy").parent().css("display","block");
							$("#" + value + "_url_redundancy").prop("required",true);
							$("#" + value + "_key_redundancy").prop("required",false);
							break ;
						case  "algolia_maps":
							$("#" + value + "_application_id_redundancy").parent().css("display","block");
							$("#" + value + "_application_id_redundancy").prop("required",true);
							break ;
						default:
							$("#" + value + "_url_redundancy").parent().css("display","none");
							$("#" + value + "_url_redundancy").prop("required",false);
							$("#" + value + "_key_redundancy").prop("required",true);
							$("#" + value + "_application_id_redundancy").parent().css("display","none");
							$("#" + value + "_application_id_redundancy").prop("required",false);
							break;
					}
				}
			});

			$("input:radio[name=" + value + "_redundancy_rule]").change(function(){
				if($(this).val() == 0){
					$("#" + value + "_redundancy").css("display","none");
					$("#" + value + "_application_id_redundancy").prop("required",false);
					$("#" + value + "_url_redundancy").prop("required",false);
					$("#" + value + "_key_redundancy").prop("required",false);
				}else{
					$("#" + value + "_redundancy").css("display","block");
					$("#" + value + "_provider_redundancy").prop("required",true);
					$("#" + value + "_provider_redundancy").change();
				}
			});

			$("#" + value + "_provider").change();
			$("#" + value + "_provider_redundancy").change();
			$('input:radio[name=' + value + '_redundancy_rule][value=' + index + ']').change();

		});
	});
</script>
@stop