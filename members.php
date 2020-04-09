<?php
include 'core/headers.php';
include 'core/helpers.php';
require  'libraries/php-jwt/JWT.php';
use \Firebase\JWT\JWT;
include 'core/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if ($method == 'POST') {
	if ($request==NULL && json_last_error() !== JSON_ERROR_NONE) {
		$data_insert=array(
			"status" => "error",
			"message" => "Incorrect Data"
		);
	}else{
		
		$firstName = isset($request->firstName) ? $request->firstName : '';
		$lastName = isset($request->lastName) ? $request->lastName : '';
		$role = isset($request->role) ? $request->role : '';
		$email = isset($request->email) ? $request->email : '';
		$password = isset($request->password) ? $request->password : '';
		$organization = isset($request->organization) ? $request->organization : '';

		//validation
		//disable registering accounts with admin role
		if ($role!='student' && $role!='expert') {
			$role='';
		}
		$required_data = array(
			$firstName,
			$lastName,
			$role,
			$email,
			$password,
			$organization,
		);

		if (emptyElementExists($required_data)) {
			//if any value is empty
			$data_insert=array(
				"data" => "0",
				"status" => "error",
				"message" => "Please recheck required values."
			);

		}else{
			//if all values are set
			$database->insert("user", [
				"firstName" => $firstName,
				"lastName" => $lastName,
				"role" => $role,
				"email" => $email,
				"password" => $password,
				"organization" => $organization
			]);

			$error=$database->error();

			if ( empty( $error[1] ) ) {
				$data_insert=array(
					"status" => "success",
					"message" => "Account created successfully."
				);	
			}else{
				$data_insert=array(
					"status" => "error",
					"message" => $error[2]
				);			
			}

		}
	}

}elseif ($method == 'GET') {

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
						"organization",
						"category",
						"languages",
						"ides",
						"qualifications"					
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
			"status" => "error",
			"message" => "Please request with access token."
		);
	}

}elseif ($method == 'PUT') {

	$jwt=getBearerToken();

	if ($jwt) {

		try {
			JWT::$leeway = 10;
			    $decoded = JWT::decode($jwt, SECRET_KEY, array(ALGORITHM));
			    // Access is granted.
			    $user_role=$decoded->data->role;
			    $user_id=$decoded->data->id;

				if ($request==NULL && json_last_error() !== JSON_ERROR_NONE) {
					$data_insert=array(
						"status" => "error",
						"message" => "Incorrect Data"
					);
				}else{
					$category = isset($request->category) ? $request->category : '';
					$languages = isset($request->languages) ? $request->languages : '';
					$ides = isset($request->ides) ? $request->ides : '';
					$qualifications = isset($request->qualifications) ? $request->qualifications : '';

					$required_data = array(
						$category,
						$languages,
						$ides,
						$qualifications
					);

					if (emptyElementExists($required_data)) {
						//if any value is empty
						$data_insert=array(
							"data" => "0",
							"status" => "error",
							"message" => "Please recheck required values."
						);

					}else{

						//if all values are set
						$database->update("user", [
							"category" => $category,
							"languages" => $languages,
							"ides" => $ides,
							"qualifications" => $qualifications,
						], [
							"id" => $user_id
						]);

						$error=$database->error();

						if ( empty( $error[1] ) ) {
							$data_insert=array(
								"status" => "success",
								"message" => "Details updated successfully."
							);	
						}else{
							$data_insert=array(
								"status" => "error",
								"message" => $error[2]
							);			
						}
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
		$data_insert=array(
			"status" => "error",
			"message" => "Please request with access token."
		);
	}

}

header('Content-Type: application/json');
echo json_encode($data_insert);
?>