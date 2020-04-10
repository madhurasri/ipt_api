<?php
include 'core/headers.php';
include 'core/helpers.php';
require  'libraries/php-jwt/JWT.php';
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

				//for pagination
				$total_records=$database->count("user", [
					"role" => "student",
					"status" => 0
				]);
				$page=isset($_GET['page']) ? $_GET['page'] : '';
				$limit=isset($_GET['limit']) ? $_GET['limit'] : '';
				if (empty($page) || empty($limit)) {
					$page=1;
					$offset=0;
					$limit=5;
				}else{
					$offset=($page-1) * $limit;
				}
				$total_pages=ceil($total_records / $limit);

				$account_data = $database->select("user", [
					"id",
					"firstName",
					"lastName",
					"email",
					"organization"
				], [
					"role" => "student",
					"status" => 0,
					"LIMIT" => [$offset, $limit]
				]);

				if ($account_data) {
					$data_insert=array(
						"status" => "success",
						"current_page" => $page,
						"total_pages" => $total_pages,
						"total_results" => $total_records,
						"data" => $account_data
					);
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
	    	http_response_code(405);
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
	http_response_code(400);
	$data_insert=array(
		"status" => "error",
		"message" => "Please request with access token."
	);
}

header('Content-Type: application/json');
echo json_encode($data_insert);
?>