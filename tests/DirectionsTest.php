<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\RequestOptions as GuzzleConvert;

class DirectionsTest extends TestCase
{

    protected $client;

    protected $ESTIMATE_BY_GEOCODE_URL = "/api/v1/libs/geolocation/admin/geocode/get_estimate";
    protected $POLYLINE_BY_GEOCODE_URL = "/api/v1/libs/geolocation/admin/geocode/get_polyline_and_estimate";
    protected $POLYLINE_BY_ADDRESS_URL = "/api/v1/libs/geolocation/admin/address/get_polyline_and_estimate";
    protected $POLYLINE_WAYPOINTS_URL = "/api/v1/libs/geolocation/admin/get_polyline_waypoints";

   
    protected $TEST_DATA_ORIGIN_DEST = [
        GuzzleConvert::JSON => array(       
            "startLat" => -19.922724,
            "startLng" => -43.940326,
            "srcAddress" => "Rua dos Goitacazes, 365 - Centro, Belo Horizonte - MG, 30190-050, Brasil",            
            "destLat" => -19.9191248,
            "destLng" => -43.9408178,
            "destAddress" => "Praça Sete de Setembro - Praça Sete de Setembro - Centro, Belo Horizonte - MG"
        )
    ];
    protected $TEST_DATA_WAYPOINTS = [
        GuzzleConvert::JSON => array(       
        "waypoints" => "[[-19.922724,-43.940326],[-19.9191248,-43.9408178]]",
    )];
            
    protected function setUp(): void {
        $this->client = new Guzzle([
            'base_uri' => "localhost:8000"
        ]);
    }

    public function tearDown():  void {
        $this->client = null;
    }

     /**
     * Teste Get Estimate Geocode
     *
     * @return void
     */
    public function testDirectionsDistanceAndTimeApi()
    {           
        $response = $this->client->request('GET', $this->ESTIMATE_BY_GEOCODE_URL, $this->TEST_DATA_ORIGIN_DEST);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());
       
        $distance = $result->data->distance;
        $duration = $result->data->time_in_minutes;
        
        $this->assertEquals(true, $result->success);  //success deve ser true  main_text     
        $this->assertLessThanOrEqual(5, $distance);
        $this->assertLessThanOrEqual(8, $duration);
    }

    /**
     * Teste Get Polyline And Estimate Geocode
     *
     * @return void
     */
    public function testPolylineAndEstimateByGeocode()
    {           
        $response = $this->client->request('GET', $this->POLYLINE_BY_GEOCODE_URL, $this->TEST_DATA_ORIGIN_DEST);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $pointOne = $result->points[0];
        $pointTwo = $result->points[1];
        $distance = $result->distance_value;
        $duration = $result->duration_value;

        
        $this->assertObjectHasAttribute('lat', $pointOne);
        $this->assertObjectHasAttribute('lng', $pointOne);

        $this->assertObjectHasAttribute('lat', $pointTwo);
        $this->assertObjectHasAttribute('lng', $pointTwo);

        $this->assertLessThanOrEqual(5, $distance);
        $this->assertLessThanOrEqual(8, $duration);
    }
    

     /**
     * Teste Get Polyline And Estimate Addresses
     *
     * @return void
     */
    public function testPolylineAndEstimateByAddresses()
    {           
        $response = $this->client->request('GET', $this->POLYLINE_BY_ADDRESS_URL, $this->TEST_DATA_ORIGIN_DEST);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $pointOne = $result->points[0];
        $pointTwo = $result->points[1];
        $distance = $result->distance_value;
        $duration = $result->duration_value;

        
        $this->assertObjectHasAttribute('lat', $pointOne);
        $this->assertObjectHasAttribute('lng', $pointOne);

        $this->assertObjectHasAttribute('lat', $pointTwo);
        $this->assertObjectHasAttribute('lng', $pointTwo);

        $this->assertLessThanOrEqual(5, $distance);
        $this->assertLessThanOrEqual(8, $duration);
    }


    /**
     * Teste Polyline And Estimate WayPoints
     *
     * @return void
     */
    public function testPolylineAndEstimateWayPoints()
    {           
        $response = $this->client->request('GET', $this->POLYLINE_WAYPOINTS_URL, $this->TEST_DATA_WAYPOINTS);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $pointOne = $result->points[0];
        $pointTwo = $result->points[1];
        $distance = $result->distance_value;
        $duration = $result->duration_value;

        $this->assertEquals(true, $result->success);  //success deve ser true  main_text     
        $this->assertObjectHasAttribute('lat', $pointOne);
        $this->assertObjectHasAttribute('lng', $pointOne);

        $this->assertObjectHasAttribute('lat', $pointTwo);
        $this->assertObjectHasAttribute('lng', $pointTwo);

        $this->assertLessThanOrEqual(5, $distance);
        $this->assertLessThanOrEqual(8, $duration);
    }
       
}