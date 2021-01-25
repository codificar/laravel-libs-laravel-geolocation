<?php $layout = '.master'; ?>
       
@extends('layout'.$layout)

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">
		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans("setting.conf") }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans("dashboard.home") }}</a></li>
			<li class="breadcrumb-item active">{{ trans("setting.conf") }}</li>
			<li class="breadcrumb-item active">{{$title}}</li>
		</ol>
	</div>
</div>	
@stop
	
@section('content')
	<div id="VueJs">		
		<geolocationsettings
			enum-data  = "{{ $enum }}"
			model = "{{ $model }}"
			place-save-route = "{{ URL::Route($enviroment.'GeolocationSettingSave') }}"
		/>
	</div>

@stop

@section('javascripts')
<script>

function clearRowClass() {
  var element = document.getElementById("layout-row-id");
  element.classList.remove("row");
}
clearRowClass()

</script>

<script src="/libs/geolocation/lang.trans/geolocation"></script>
<script src="{{ elixir('vendor/codificar/geolocation/geolocation.vue.js') }}"> </script> 
@stop
