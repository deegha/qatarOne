<?php
session_start();
require_once 'include/DbHandler.php';
require_once 'include/PassHash.php';
require_once 'include/SessionHandler.php';
require 'libs/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());

// User id from db - Global Variable
$user_id = NULL;

    header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	header('Content-Type: application/json');
	

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}



/**
 * Get all users
 * url - /userlist
 * method - GET
 * params - api Key*/

 $app->get('/userlist',  function() {
           
            $response = array();
            $db = new DbHandler();

            // fetching all users
            $result = $db->getAllUsers();

            $response["error"] = false;
            $response["user_list"] = array();
            
            while ($user = $result->fetch_assoc()) {
                $tmp = array();               
                $tmp["user_id"] = $user["user_id"];
				$tmp["user_username"] = $user["user_username"];
				$tmp["user_password"] = $user["user_password"];
				$tmp["user_email"] = $user["user_email"];                
                $tmp["user_fullname"] = $user["user_fullname"];
                $tmp["user_city"] = $user["user_city"];
                $tmp["user_country"] = $user["user_country"];
                $tmp["user_address1"] = $user["user_address1"];
                $tmp["user_address2"] = $user["user_address2"];
                $tmp["user_telephoneno1"] = $user["user_telephoneno1"];
                $tmp["user_telephoneno2"] = $user["user_telephoneno2"];
                $tmp["user_imageurl"] = $user["user_imageurl"];
                $tmp["user_entereddate"] = $user["user_entereddate"];
                $tmp["user_type"] = $user["user_type"];
                $tmp["user_status"] = $user["user_status"];
                $tmp["user_paymentstatus"] = $user["user_paymentstatus"];
                $tmp["user_category"] = $user["user_category"];
                $tmp["user_profiletype"] = $user["user_profiletype"];
                array_push($response["user_list"], $tmp);
            }

            echoRespnse(200, $response);
        });

/**
 * Get user by user id
 * url - /userlist
 * method - GET
 * params -user id*/

 $app->get('/userlist/:id',  function($user_id) {
            $response = array();
            $db = new DbHandler();

            // fetch user
            $result = $db->GetUserDetail($user_id);

            if ($result != NULL) {
                $response["error"] = false;
                $tmp["user_id"] = $result["user_id"];
                $tmp["user_username"] = $result["user_username"];
                $tmp["user_password"] = $result["user_password"];
                $tmp["user_email"] = $result["user_email"];                
                $tmp["user_fullname"] = $result["user_fullname"];
                $tmp["user_city"] = $result["user_city"];
                $tmp["user_country"] = $result["user_country"];
                $tmp["user_address1"] = $result["user_address1"];
                $tmp["user_address2"] = $result["user_address2"];
                $tmp["user_telephoneno1"] = $result["user_telephoneno1"];
                $tmp["user_telephoneno2"] = $result["user_telephoneno2"];
                $tmp["user_imageurl"] = $result["user_imageurl"];
                $tmp["user_entereddate"] = $result["user_entereddate"];
                $tmp["user_type"] = $result["user_type"];
                $tmp["user_status"] = $result["user_status"];
                $tmp["user_paymentstatus"] = $result["user_paymentstatus"];
                $tmp["user_category"] = $result["user_category"];
                $tmp["user_profiletype"] = $result["user_profiletype"];                 
                echoRespnse(200, $tmp);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });
		

/**
 * Create user 
 * url - /userlist
 * method - POST
 * params -user object*/

$app->post('/userlist', function() use ($app) {
            // check for required params
           // verifyRequiredParams(array('task'));

            $response = array();
           // $user = $app->request->post('user');
            $request = \Slim\Slim::getInstance()->request();
            $body = $request->getBody();

            $db = new DbHandler();

            // creating new user
            $user_id = $db->createUser($body);
            //echo $user_id;
            
            if ($user_id == 0) {
                $response["error"] = false;
                $response["message"] = "user created successfully";                
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Failed to create user. Please try again";
                echoRespnse(200, $response);
            }            
        });

/**
 * Update user 
 * url - /userlist
 * method - PUT
 * params -user object, user_id */
		
	$app->put('/userlist/:id',  function($user_id) {
            $request = \Slim\Slim::getInstance()->request();
			$body = $request->getBody();
			
			$db = new DbHandler();
            $response = array();
			
			 // updating user
            $result = $db->updateUser($user_id, $body);
			
            if ($result) {
                // user updated successfully
                $response["error"] = false;
                $response["message"] = "User updated successfully";
            } else {
                // user failed to update
                $response["error"] = true;
                $response["message"] = "User failed to update. Please try again!";
            }
			
            echoRespnse(200, $response);		
				
        });
 		
/**
 * Delete user 
 * url - /userlist/:id'
 * method - DELETE
 * params - user_id */
$app->delete('/userlist/:id',  function($user_id) use($app) {
          
            $db = new DbHandler();
            $response = array();
            $result = $db->deleteUser($user_id);
			
            if ($result) {
                // user deleted successfully				
                $response["error"] = false;
                $response["message"] = "User deleted succesfully";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "User failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });
		
/**
 * Retreive Fixed advertisment list 
 * url - /fixedadlist
 * method - GET
 * params - */

$app->get('/fixedadlist',  function() {
           
            $response = array();
            $db = new DbHandler();

            // fetching all users
            $result = $db->getAllFixedAd();

            $response["error"] = false;
            $response["fixedadlist_list"] = array();
            
            while ($user = $result->fetch_assoc()) {
                $tmp = array();               
                $tmp["fixedads_type"] = $user["fixedads_type"];
                $tmp["fixedads_description"] = $user["fixedads_description"];
                $tmp["fixedads_note"] = $user["fixedads_note"];
                $tmp["fixedads_imageurl"] = $user["fixedads_imageurl"];                
                $tmp["fixedads_videourl"] = $user["fixedads_videourl"];
                $tmp["fixedads_enetreddate"] = $user["fixedads_enetreddate"];
                $tmp["fixedads_enteredby"] = $user["fixedads_enteredby"];
                $tmp["fixedads_approvedstatus"] = $user["fixedads_approvedstatus"];
                $tmp["fixedads_status"] = $user["fixedads_status"];    
                array_push($response["fixedadlist_list"], $tmp);
            }

            echoRespnse(200, $response);
        });

/**
 * Get Fixed advertisment by advertisment id
 * url - /fixedadlist/:id
 * method - GET
 * params -fixedads_id */

 $app->get('/fixedadlist/:id',  function($fixedads_id) {
            $response = array();
            $db = new DbHandler();

            // fetch user
            $result = $db->GetFixedAdvertismentDetail($fixedads_id);

            if ($result != NULL) {
                $response["error"] = false;
                $tmp["fixedads_type"] = $result["fixedads_type"];
                $tmp["fixedads_description"] = $result["fixedads_description"];
                $tmp["fixedads_note"] = $user["fixedads_note"];
                $tmp["fixedads_imageurl"] = $result["fixedads_imageurl"];                
                $tmp["fixedads_videourl"] = $result["fixedads_videourl"];
                $tmp["fixedads_enetreddate"] = $result["fixedads_enetreddate"];
                $tmp["fixedads_enteredby"] = $result["fixedads_enteredby"];
                $tmp["fixedads_approvedstatus"] = $result["fixedads_approvedstatus"];
                $tmp["fixedads_status "] = $result["fixedads_status "];                 
                echoRespnse(200, $tmp);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });


/**
 * Create Fixed advertisment 
 * url - /fixedadlist
 * method - POST
 * params -fixed advertisment  object*/

$app->post('/fixedadlist', function() use ($app) {
            // check for required params
           // verifyRequiredParams(array('task'));

            $response = array();           
            $request = \Slim\Slim::getInstance()->request();
            $body = $request->getBody();

            $db = new DbHandler();

            // creating new user
            $user_id = $db->createFixedAdvertisment($body);
            //echo $user_id;
            
            if ($user_id == 0) {
                $response["error"] = false;
                $response["message"] = "Fixed Advertisment created successfully";                
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Failed to create Fixed Advertisment. Please try again";
                echoRespnse(200, $response);
            }            
        });


/**
 * Update  Fixed advertisment  
 * url - /fixedadlist/:id
 * method - PUT
 * params -Fixed advertisment object, fixedads_id */
        
    $app->put('/fixedadlist/:id',  function($fixedads_id) {
            $request = \Slim\Slim::getInstance()->request();
            $body = $request->getBody();
            
            $db = new DbHandler();
            $response = array();
            
             // updating user
            $result = $db->updateFixedAdvertisment($fixedads_id, $body);
            
            if ($result) {
                // user updated successfully
                $response["error"] = false;
                $response["message"] = "Fixed Advertisment updated successfully";
            } else {
                // user failed to update
                $response["error"] = true;
                $response["message"] = "Fixed Advertisment failed to update. Please try again!";
            }
            
            echoRespnse(200, $response);        
                
        });
        

        /**
 * Delete Fixed advertisment   
 * url - /userlist/:id'
 * method - DELETE
 * params - user_id */
$app->delete('/fixedadlist/:id',  function($fixedads_id) use($app) {
          
            $db = new DbHandler();
            $response = array();
            $result = $db->deleteFixedAdvertisment($fixedads_id);
            
            if ($result) {
                // user deleted successfully                
                $response["error"] = false;
                $response["message"] = "Fixed Advertisment deleted succesfully";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Fixed Advertisment failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });
        
/**
 * User Registration
 * url - /userlist
 * method - GET
 * params - api Key*/
  
 $app->get('/userlist','authenticate',  function() {
           
            $response = array();
            $db = new DbHandler();

            // fetching all user books
            $result = $db->getAllUsers();

            $response["error"] = false;
            $response["user_list"] = array();

            // looping through result and preparing user list array
            while ($user = $result->fetch_assoc()) {
                $tmp = array();               
                $tmp["id"] = $user["id"];
				$tmp["user_name"] = $user["user_name"];
				$tmp["first_name"] = $user["first_name"];
				$tmp["last_name"] = $user["last_name"];                
                $tmp["designation"] = $user["designation"];
                array_push($response["user_list"], $tmp);
            }

            echoRespnse(200, $response);
        });
		
$app->post('/login','setUserAccessToken', function() use ($app) {    
 						
				// reading post params						 
				$email = $app->request()->post('email');
				$password = $app->request()->post('password');
				$response = array();
	
				$db = new DbHandler();
				// check for correct email and password
				if ($db->checkLogin($email, $password)) {
					// get the user by email
					$user = $db->getUserByEmail($email);
	
					if ($user != NULL) {
						$response["error"] = false;						
						$response['apiKey'] = $user['api_key'];
						$response['accessToken'] = $_SESSION['user_access_token'];
					} else {
						// unknown error occurred
						$response['error'] = true;
						$response['message'] = "An error occurred. Please try again";
					}
				} else {
					// user credentials are wrong
					$response['error'] = true;
					$response['message'] = 'Login failed. Incorrect credentials';
				}
	
				echoRespnse(200, $response);
				//}
	});


/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
		
		
?>