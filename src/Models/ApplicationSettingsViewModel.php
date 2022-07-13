<?php
namespace Codificar\Geolocation\Models;

use stdClass;

class ApplicationSettingsViewModel extends stdClass{
	public $id;
	public $key;
	public $value;
	public $tool_tip;
	public $page;
	public $category;
	public $sub_category;
    public $shortest_distance;

	// constructor
	function __construct() {  }

}