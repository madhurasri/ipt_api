<?php
include 'core/headers.php';
include 'core/helpers.php';
require  'libraries/php-jwt/JWT.php';
use \Firebase\JWT\JWT;
include 'core/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if ($method == 'GET') {

	$jwt=getBearerToken();

	if ($jwt) {

		try {
			JWT::$leeway = 10;
			    $decoded = JWT::decode($jwt, SECRET_KEY, array(ALGORITHM));

			    // Access is granted.
			    $category_id=isset($_GET['id']) ? $_GET['id'] : '';
			    if (empty($category_id)) {
			    	//get all categoris
					$category_data = $database->select("category", [
						"cat_id",
						"cat_name",
					]);

					if ($category_data) {
						$data_insert=$category_data;
					}else{
						$data_insert=array(
						"status" => "success",
						"message" => "No results."
						);
					} 

			    }else{
			    	//get users in requested category

					//for pagination
					$total_records=$database->count("user", [
						"category" => $category_id,
						"role" => "student",
						"status" => 1
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

					$category_data = $database->select("user", [
						"id",
						"firstName",
						"lastName",
						"email",
						"organization"
					], [
						"category" => $category_id,
						"role" => "student",
						"status" => 1,
						"LIMIT" => [$offset, $limit]
					]);

					if ($category_data) {
						$data_insert=array(
							"status" => "success",
							"current_page" => $page,
							"total_pages" => $total_pages,
							"total_results" => $total_records,
							"data" => $category_data
						);
					}else{
						$data_insert=array(
						"status" => "success",
						"message" => "No results."
						);
					} 

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



}

header('Content-Type: application/json');
echo json_encode($data_insert);
?>