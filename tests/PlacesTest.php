<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\RequestOptions as GuzzleConvert;

class PlacesTest extends TestCase
{

    protected $client;
    protected $PLACE_URL = "/api/v1/libs/geolocation/admin/get_address_string";
    protected $GEOCODE_URL = "/api/v1/libs/geolocation/admin/geocode";
    protected $REVERSE_GEOCODE_URL = "/api/v1/libs/geolocation/admin/geocode_reverse";
    protected $GET_BY_PLACE_ID_URL = "/api/v1/libs/geolocation/admin/get_place_details";

    protected $TEST_DATA_PLACE = [
        GuzzleConvert::JSON => array(       
        "latitude" => -19.922724,
        "longitude" => -43.940326,
        "place" => "Rua dos Goitacazes, 375 - Centro, Belo Horizonte",
    )];

    protected $TEST_DATA_GEOCODE = [
        GuzzleConvert::JSON => array(      
        "latitude" => -19.922724,
        "longitude" => -43.940326,
        "address" => "Rua dos Goitacazes, 375 - Centro, Belo Horizonte - MG, 30190-050",
    )];

    protected $TEST_RESPONSE_PLACE = array(
        "latitude" => -19.922724,
        "longitude" => -43.940326,
        "main_text" => '/(?i)Rua Dos Goitacazes/', // (?i) Ignore upper case
        "secondary_text" => '/(?i)Belo Horizonte/',
    );

    protected $TEST_RESPONSE_GEOCODE = array(
        "latitude" => -19.922724,
        "longitude" => -43.940326,
        "address" => '/(?i)Rua Dos Goitacazes/', // (?i) Ignore upper case
    );

    protected function setUp(): void {
        $this->client = new Guzzle([
            'base_uri' => "localhost:8000"
        ]);
    }

    public function tearDown():  void {
        $this->client = null;
    }
    
    /**
     * Teste Places API getAddressByTextWithLatLng()
     *
     * @return void
     */
    public function testPlacesApi()
    {           
        $response = $this->client->request('GET', $this->PLACE_URL, $this->TEST_DATA_PLACE);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $firstAddress = $result->data[0];
        
        $this->assertEquals(true, $result->success);  //success deve ser true  main_text     
        $this->assertMatchesRegularExpression($this->TEST_RESPONSE_PLACE['main_text'], $firstAddress->main_text);
        $this->assertMatchesRegularExpression($this->TEST_RESPONSE_PLACE['secondary_text'], $firstAddress->secondary_text);
    }

    /**
     * Teste Geocode API getGeocodeWithAddress()
     *
     * @return void
     */
    public function testGeocodeApi()
    {           
        $response = $this->client->request('GET', $this->GEOCODE_URL, $this->TEST_DATA_GEOCODE);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $firstAddress = $result->data;
        $this->assertEquals(true, $result->success);  //success deve ser true
        $this->assertNotNull($firstAddress->latitude);  //Não pode ser null
        $this->assertNotNull($firstAddress->longitude); //Não pode ser null
        $this->assertMatchesRegularExpression($this->TEST_RESPONSE_GEOCODE['address'], $firstAddress->address); 
    }

    /**
     * Teste Reverse Geocode API getGeocodeByLatLng()
     *
     * @return void
     */
    public function testReverseGeocodeApi()
    {           
        $response = $this->client->request('GET', $this->REVERSE_GEOCODE_URL, $this->TEST_DATA_GEOCODE);
        
        //Validando Status HTTP       
        $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

        //Validando Cabeçalho        
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
        //Validando o BODY da resposta
        $result = json_decode($response->getBody());

        $firstAddress = $result->data;
        $this->assertEquals(true, $result->success);  //success deve ser true
        $this->assertNotNull($firstAddress->latitude);  //Não pode ser null
        $this->assertNotNull($firstAddress->longitude); //Não pode ser null
        $this->assertMatchesRegularExpression($this->TEST_RESPONSE_GEOCODE['address'], $firstAddress->address); 
    }

    /**
     * Teste Reverse Geocode API getDetailsById()
     *
     * @return void
     */
    // public function testGetDetailsByPlaceId()
    // {      
    //     $place_id = "EkhSdWEgZG9zIEdvaXRhY2F6ZXMsIDM3NSAtIENlbnRybywgQmVsbyBIb3Jpem9udGUgLSBNRywgMzAxOTAtMDUwLCBCcmF6aWwiMRIvChQKEgn1N8u945mmABEd-zZxejfncBD3AioUChIJg_BKPVmXpgARym6KWRoBSiA";     
    //     $response = $this->client->request('GET', $this->GET_BY_PLACE_ID_URL, 
    //         [
    //             GuzzleConvert::JSON => array(      
    //                 "place_id" => $place_id
    //         )]    
    //     );
        
    //     //Validando Status HTTP       
    //     $this->assertEquals(200, $response->getStatusCode());  //Status deve ser 200

    //     //Validando Cabeçalho        
    //     $contentType = $response->getHeaders()["Content-Type"][0];
    //     $this->assertEquals("application/json", $contentType); //Deve ser um JSON
        
    //     //Validando o BODY da resposta
    //     $result = json_decode($response->getBody());
    //     $firstAddress = $result->data;
    //     $this->assertEquals(true, $result->success);  //success deve ser true
    //     $this->assertEquals($place_id, $firstAddress->place_id);  //O place ID deve ser o mesmo enviado
    //     $this->assertNotNull($firstAddress->latitude);  //Não pode ser null
    //     $this->assertNotNull($firstAddress->longitude); //Não pode ser null
    //     $this->assertMatchesRegularExpression($this->TEST_RESPONSE_GEOCODE['address'], $firstAddress->address); 
    // }
}