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
	    $user_role=$decoded->data->role;

	    if ($user_role=="admin") {

			// Access is granted.

			if ($method == "GET") {
				//get all new accounts
				$account_data = $database->select("user", [
					"id",
					"firstName",
					"lastName",
					"email",
					"organization"
				], [
					"role" => "student",
					"status" => 0
				]);

				if ($account_data) {
					$data_insert=$account_data;
				}else{
					$data_insert=array(
					"status" => "success",
					"message" => "No results."
					);
				}
			}elseif ($method == "PUT") {
				$account_id=isset($_GET['id']) ? $_GET['id'] : '';
				if (!empty($account_id)) {

					$database->update("user", [
						"status" => 1
					], [
						"id" => $account_id
					]);

					$error=$database->error();

					if ( empty( $error[1] ) ) {
						$data_insert=array(
							"status" => "success",
							"message" => "Account approved successfully."
						);	
					}else{
						$data_insert=array(
							"status" => "error",
							"message" => $error[2]
						);			
					}
				}
			}elseif ($method == "DELETE") {
				$account_id=isset($_GET['id']) ? $_GET['id'] : '';
				if (!empty($account_id)) {

					$database->delete("user", [
						"id" => $account_id
					]);

					$error=$database->error();

					if ( empty( $error[1] ) ) {
						$data_insert=array(
							"status" => "success",
							"message" => "Account deleted successfully."
						);	
					}else{
						$data_insert=array(
							"status" => "error",
							"message" => $error[2]
						);			
					}
				}
			}

 

	    }else{
			$data_insert=array(
				"status" => "error",
				"message" => "You don't have permission to access this data."
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
		"status" => "error",
		"message" => "Please request with access token."
	);
}

//header('Content-Type: application/json');
echo json_encode($data_insert);