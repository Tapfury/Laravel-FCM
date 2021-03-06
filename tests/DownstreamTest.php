<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use LaravelFCM\Sender\FCMSender;

class ResponseTest extends FCMTestCase {

	/**
	 * @test
	 */
	public function it_send_a_notification_to_a_device()
	{
		$response = new Response(200, [], '{ 
						  "multicast_id": 216,
						  "success": 3,
						  "failure": 3,
						  "canonical_ids": 1,
						  "results": [
							    { "message_id": "1:0408" }
	                      ]
					}' );

		$client = Mockery::mock(Client::class);
		$client->shouldReceive('post')->once()->andReturn($response);
		$this->app->singleton('fcm.client', function($app) use($client) {
			return $client;
		});

		$tokens = 'uniqueToken';

		$fcm = new FCMSender();
		$fcm->sendTo($tokens);
	}


	/**
	 * @test
	 */
	public function it_send_a_notification_to_more_than_1000_devices()
	{
		$response = new Response(200, [], '{ 
						  "multicast_id": 216,
						  "success": 3,
						  "failure": 3,
						  "canonical_ids": 1,
						  "results": [
							    { "message_id": "1:0408" },
							    { "error": "Unavailable" },
							    { "error": "InvalidRegistration" },
							    { "message_id": "1:1516" },
							    { "message_id": "1:2342", "registration_id": "32" },
							    { "error": "NotRegistered"}
	                      ]
					}' );

		$client = Mockery::mock(Client::class);
		$client->shouldReceive('post')->times(10)->andReturn($response);
		$this->app->singleton('fcm.client', function($app) use($client) {
			return $client;
		});

		$tokens = [];
		for ($i=0 ; $i<10000 ; $i++) {
			$tokens[$i] = 'token_'.$i;
		}

		$fcm = new FCMSender();
		$fcm->sendTo($tokens);
	}
}