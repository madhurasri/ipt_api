<?php
include 'core/headers.php';
include 'core/helpers.php';
require  'libraries/php-jwt/jwt.php';
use \Firebase\JWT\JWT;
include 'core/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

	$jwt=getBearerToken();

	if ($jwt) {

		try {
			JWT::$leeway = 10;
			    $decoded = JWT::decode($jwt, SECRET_KEY, array(ALGORITHM));

			    // Access is granted.

			    $member_id=isset($_GET['id']) ? $_GET['id'] : '';
			    if (empty($member_id)) {
			    	//get all members
					$member_data = $database->select("user", [
						"id",
						"firstName",
						"lastName",
						"email",
						"organization"
					], [
						"role" => "student",
						"status" => 1
					]);
			    }else{
			    	//get requested member
					$member_data = $database->get("user", [
						"id",
						"firstName",
						"lastName",
						"email",
						"organization"
					], [
						"id" => $member_id,
						"role" => "student",
						"status" => 1
					]);		    	
			    }
			    
			    if ($member_data) {
			    	$data_insert=$member_data;
			    }else{
					$data_insert=array(
					"status" => "success",
					"message" => "No results."
					);
			    } 

		} catch (Exception $e){

			http_response_code(401);

			$data_insert=array(
				"jwt" => $jwt,
				"status" => "error",
				"message" => $e->getMessage()
			);

		}

	}else{
		$data_insert=array(
			"data" => "0",
			"status" => "error",
			"message" => "Please request with access token."
		);
	}

header('Content-Type: application/json');
echo json_encode($data_insert);