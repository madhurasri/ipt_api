<?php
include 'core/headers.php';
require  'libraries/php-jwt/JWT.php';
use \Firebase\JWT\JWT;
include 'core/config.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
	exit(0);
}

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$email = isset($request->email) ? $request->email : '';
$password = isset($request->password) ? $request->password : '';

if ( empty($email) || empty($password) ) {
	http_response_code(400);
	$data_insert=array(
		"status" => "error",
		"message" => "E-mail and Password is required"
	);

}else{

	$user = $database->get("user", [
		"id",
		"firstName",
		"lastName",
		"role",
		"email",
		"organization"
	], [
		"email" => $email,
		"password" => $password,
	]);

	if (empty($user)) {
		http_response_code(400);
		$data_insert=array(
			"status" => "error",
			"message" => "Wrong email or password"
		);
	}else{
		$iat = time(); // time of token issued at
		$nbf = $iat + 10; //not before in seconds
		$exp = $iat + 6000000; // expire time of token in seconds

		$token = array(
			"iss" => "https://madhurasri.com",
			"aud" => "https://madhurasri.com",
			"iat" => $iat,
			"nbf" => $nbf,
			"exp" => $exp,
			"data" => array(
				"id" => $user["id"],
				"email" => $user["email"],
				"role" => $user["role"]
			)
		);

		http_response_code(200);

		$jwt = JWT::encode($token, SECRET_KEY);

		$data_insert=array(
			'access_token' => $jwt, 
			'id'   => $user["id"],
			'firstName' => $user["firstName"],
			'lastName' => $user["lastName"],
			"role" => $user["role"],
			'organization' => $user["organization"],
			'time' => time(),
			'email' => $user["email"],
			'status' => "success",
			'message' => "Successfully Logged In"
		);
	}

}

header('Content-Type: application/json');
echo json_encode($data_insert);
?>