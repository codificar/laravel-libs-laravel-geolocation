<?php $layout = '.master'; ?>
       
@extends('layout'.$layout)

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">
		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans("invoice.data") }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans("dashboard.home") }}</a></li>
			<li class="breadcrumb-item active">{{ trans("keywords.Settings") }}</li>
			<li class="breadcrumb-item active">{{ trans("invoice.data") }}</li>
		</ol>
	</div>
</div>
@stop
	
@section('content')
	<div id="VueJs">
		
	<teste
        autocomplete-url = "{{ URL::Route($enviroment.'AutocompleteUrlGeolocationLib') }}"
		geocode-url = "{{ URL::Route($enviroment.'GeocodeUrlGeolocationLib') }}"
    />
	
	</div>

@stop

@section('javascripts')
<script src="/libs/geolocation/lang.trans/settings,basic"></script> 
<script src="{{ asset('vendor/codificar/geolocation/geolocation.vue.js') }}"> </script> 
@stop
