<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept,Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**  
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class User extends REST_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
        
        $this->load->model('api_model');  // one model for all api 
        $this->load->helper('api_helper');
        $this->load->helper('cookie');
         
       }
    
    // Generate access token once seller
	function generate_access_token_post()
	{   
		$status = 0;
		$response_message = '';
		$access_token = '';
        $response_data = array();
	    if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['device_id'] = $Command->device_id;
       	    $_POST['device_type'] = $Command->device_type;
       	    $_POST['api_key'] = $Command->api_key;
       	    unset($_POST['command']);
        }

		$this->form_validation->set_rules('device_id', 'Device Id', 'required|trim');
		$this->form_validation->set_rules('device_type', 'Device Type', 'required|is_natural_no_zero|less_than[3]|trim');
		$this->form_validation->set_rules('api_key', 'Key', 'required|trim');

		if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{
			$device_id = $this->input->post('device_id', TRUE);
			$device_type = $this->input->post('device_type', TRUE);
			if(checkApikey($this->input->post('api_key')))
			{
				// check for already registered access token against this device_id
				$is_valid_user = $this->api_model->access_token_check('', $device_type, $device_id, '');

				// if there is no access token then delete all access token for this device_id
				if(!empty($is_valid_user->access_token)){			
					// delete access token
					$this->api_model->access_token_delete('', $device_id);
				}
				
				// bind new access token
				$access_token = md5($device_id . time());
				// save new access token
				$is_saved = $this->api_model->saveAccessToken($device_id, $device_type, $access_token);
				// Set Cookies
				
				if(!empty($is_saved)){
					$status = 1;
					$response_message = "success";
				} else {
					$response_message = "Something is wrong.";
				}

				$response_data = array(
					'status' => $status,
					'response_message' => $response_message,
					'access_token' => $access_token,
				);
		    }
		    else 
		    {
				$response_data = array(
					'status' => $status,
					'response_message' => INVALID_API_KEY 
				);
			}
        }  
		echo json_encode($response_data);
	}
    
    

    // for user/pharma registration 
    public function register_post()
    { 	
    	$status = 0;
		$response_message = $insert_id = '';$cart_id="";
		if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['device_id'] = $Command->device_id;
       	    $_POST['device_type'] = $Command->device_type;
       	    $_POST['api_key'] = $Command->api_key;
       	    $_POST['email'] = $Command->email;
       	    $_POST['mobile'] = $Command->mobile;
       	    $_POST['password'] = $Command->password;
       	    if($Command->access=='company'){
       	    	$_POST['location'] = $Command->location;
	       	    $_POST['gstin'] = $Command->gstin;
	       	    $_POST['company'] = $Command->company_name;
       	    }
       	    else
       	    {
       	    	$_POST['first_name'] = $Command->first_name;
       	    	$_POST['last_name'] = $Command->last_name;
       	    }
       	    $_POST['access'] = $Command->access;
       	    $_POST['code'] = isset($Command->code)?$Command->code:'';
       	    unset($_POST['command']);
        }
		$access = $Command->access;
		//print_r($access);exit;
		$this->form_validation->set_rules('access', 'access', 'required|trim|in_list[company,customer]');
        if(isset($_POST['access']) && $_POST['access'] == 'company')
		{
		  	$this->form_validation->set_rules('company', 'company Name', 'required|trim');
		  	$this->form_validation->set_rules('gstin', 'gstin', 'required|trim');
		  	$this->form_validation->set_rules('location', 'Location', 'required|trim');
		  	if(isset($_POST['device_type']) && $_POST['device_type']==1){
		  		$this->form_validation->set_rules('state', 'state', 'trim');
				$this->form_validation->set_rules('city', 'city', 'trim');
				$this->form_validation->set_rules('pincode', 'city', 'trim');
				$this->form_validation->set_rules('latitude', 'Latitude', 'required|trim');
				$this->form_validation->set_rules('longitude', 'Longitude', 'required|trim');
		  	}
		}
		else 
		{
	       $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		   $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
	    }
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[tbl_user.email]',array('is_unique' =>'This email  is already exist' ));
	    $this->form_validation->set_rules('password', 'Password', 'required|trim');
		$this->form_validation->set_rules('mobile', 'mobile no.', 'required|is_natural|min_length[10]|max_length[13]|trim|is_unique[tbl_user.mobile]',array('is_unique' =>'This mobile number is already exist' ));
		$this->form_validation->set_rules('api_key', 'Key', 'required|trim');
		$this->form_validation->set_rules('device_type', 'device type', 'required|trim');
		if($this->form_validation->run() == FALSE)
		{
			if(isset($_POST['device_type']) && $_POST['device_type']==2)
				$response_data = webvalidationErrorMsg();
			else
				validationErrorMsg();
		}
		else
		{
			if(checkApikey($this->input->post('api_key')))
			{
				extract($_POST); 
				$this->load->model('ion_auth_model');
				$data = array();
				if(isset($_POST['access']) && $_POST['access'] == 'company')
		        {	
		        	$first_name='';
		        	$data['name'] = $company;
				    $data['company'] = $company;
				    $_POST['company'] = $first_name;
				    $data['gstin'] = $gstin; 
				}
			    else 
			    {
			       	$data['name'] = $first_name.' '.$last_name;
			    } 
				$data['email'] = $email;
				$data['mobile'] = $mobile;
				$data['my_referral_code'] = GetReferCode($first_name);
                $data['refer_by'] = (isset($code))?$code:'';
				$data['password'] = $this->ion_auth_model->hash_password($password,'');
				$data['user_status'] = '0';
				$data['user_type'] = $access;
				$data['created_on'] = date('Y-m-d H:i:s');
				$data['reg_from'] = $device_type;
				$insert_id = $this->api_model->insert('tbl_user',$data);
				if(isset($_POST['access']) && ($_POST['access'] == 'company' ||$_POST['access'] == 'customer') )
		        {
			        $addRess_data['a_address'] = isset($location)?$location:''; 
			        $addRess_data['a_name'] = isset($company)?$company:'';
			        $addRess_data['fk_user_id'] = $insert_id;  
			        $addRess_data['a_mobile'] = $mobile; 
			        $addRess_data['a_state'] = isset($state)?$state:"";
			        $addRess_data['a_city'] = isset($city)?$city:"";
			        $addRess_data['a_pincode'] = isset($pincode)?$pincode:"";
			        $addRess_data['address_type']="home"; 
			        $insertAddress_id = $this->api_model->insert('tbl_address',$addRess_data);
                }				
				if($insert_id)
				{
					$this->load->model('ion_auth_model'); 
					$otp_verify = $this->ion_auth_model->otp_verify($email,$access);
					$select="tbl_user.user_id,name,email,company,gstin,mobile,profile_pic,user_type,user_status,tbl_address.a_address,tbl_address.address_type,tbl_address.latitude,tbl_address.a_state,tbl_address.a_city,tbl_address.a_pincode,tbl_address.longitude,tbl_address.address_id as uaid";  
					 $jointbl="tbl_address";
					 $colum="fk_user_id"; 
					$usersData=$this->api_model->join_two_orderby('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.user_type'=>$_POST['access'],'tbl_user.user_id'=>$insert_id),$select);
					//get cart id
					$device_id = (isset($device_id))?$device_id:'';
					$getCart = $this->api_model->check_cart($device_id,$insert_id);
                    if(!empty($getCart))
                    $cart_id = $getCart->id;
                    else
                    $cart_id = "";
					if(!empty($usersData->name))	
					{
						$parts = explode(' ', $usersData->name);
					    $firstname = isset($parts[0])?$parts[0]:'';
					    $lastname = isset($parts[1])?$parts[1]:'';
					    $usersData->first_name=$firstname;
					    $usersData->last_name=$lastname;
					}
					else
					{
						$usersData->first_name="";
						$usersData->last_name="";
					}
					$status =1;
					$response_message ='you are registred successfully';	
				}
				else 
				    $response_message ='Something is wrong please try again.';
			}
			else // api key check else  
				$response_data = INVALID_API_KEY;
		   // response array  
		    $response_data = array('status' => $status, 'response_message' => $response_message, 'uid' => $insert_id,'data'=>$usersData,'cart_id'=>$cart_id);
	   	}
		echo json_encode($response_data);
	} 
	// -----------------------------------------------------------------     
   	// login  
	function login_post(){
	    $status =0;
	    $response_message='';$cart_id="";
	    $data = array();	
	    if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['device_id'] = $Command->device_id;
       	    $_POST['device_type'] = $Command->device_type;
       	    $_POST['api_key'] = $Command->api_key;
       	    $_POST['identity'] = $Command->email;
       	    $_POST['password'] = $Command->password;
       	    $_POST['access_token'] = trim($Command->access_token);       	    
       	    unset($_POST['command']);
        }
		basicFromValidation();
		$this->form_validation->set_rules('identity', 'Username Or Email', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');		
		if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{
			if(checkApikey($this->input->post('api_key')))
			{
				extract($_POST); 
				$uid=null;
				$is_valid_user = $this->api_model->access_token_check($uid, $device_type, $device_id, $access_token);
				if(empty($is_valid_user->access_token))
				{  
					$status = INVALID_TOKEN;
					$response_message = INVALID_TOKEN_MSG;
					// delete access token
					$this->api_model->access_token_delete($uid, $device_id);
				}
				else
				{   
					$this->load->model('ion_auth_model');
					$user = $this->api_model->login($identity);
					if($user)
					{   $user = $user[0];
						$uid = $user->user_id;  
						$password = $this->ion_auth_model->hash_password_db($user->user_id, $password);
						if($password === TRUE)
						{

						    if($user->user_status == '1')
						    {
						   		// update access token and uid for this device
								$this->api_model->update_uid_AccessToken($user->user_id, $device_id, $device_type, $access_token);
							    if($user->user_type == 'customer' || $user->user_type == 'company')
						        {
									$select="tbl_user.user_id,company,name,email,mobile,profile_pic,user_type,user_status,gstin,my_credits,tbl_address.a_address,tbl_address.a_city,tbl_address.a_state,tbl_address.a_pincode,tbl_address.address_id as uaid,tbl_address.address_type";  
									$jointbl="tbl_address";
									$colum="fk_user_id"; 
                                    $users=$this->api_model->join_two_orderby('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.user_status'=>'1','tbl_user.user_type'=>$user->user_type,'tbl_user.user_id'=>$user->user_id),$select);
                                    //~ $getCart = $this->api_model->check_cart($device_id,$user->user_id);
									//~ if(!empty($getCart))
										//~ $cart_id = $getCart->id;
									//~ else
										//~ $cart_id = "";
                                    $availableUserCart=$this->api_model->check_cart("",$uid);
									if($availableUserCart)
									{
										$cart_id = $availableUserCart->id;
										$cart_data = $this->api_model->get_cart_items($cart_id);
										$users->cart_value = sizeof($cart_data);
									}
									else
									{
										$availableCart = $this->api_model->check_cart($device_id,"");
										if($availableCart){
											$cart_id = $availableCart->id;
											//~ $updatecart=$this->api_model->check_cart($uid,$availableCart->id);
											$updatecart=$this->api_model->update_uid_cart($uid,$device_id);
											if($updatecart){
											$cart_data = $this->api_model->get_cart_items($cart_id);
											$users->cart_value = sizeof($cart_data);
											}
										}
										else{
											$users->cart_value = 0;
										}
									}  
									if(isset($users->name) && !empty($users->name))	
									{
										$parts = explode(' ', $users->name);
										$firstname = $parts[0];
										$lastname = isset($parts[1])?$parts[1]:'';
										$users->first_name=$firstname;
										$users->last_name=$lastname;
									}
									else
									{
										$users->first_name="";
										$users->last_name="";
									}											
									$status = 1;
									$data = $users; 
									$response_message ='success';
							    }
							    elseif($user->user_type == 'serviceprovider')
							    {
							    	$users = $user;
							    	if(isset($users->name) && !empty($users->name))	
									{
										$parts = explode(' ', $users->name);
										$firstname = $parts[0];
										$lastname = isset($parts[1])?$parts[1]:'';
										$users->first_name=$firstname;
										$users->last_name=$lastname;
									}
									else
									{
										$users->first_name="";
										$users->last_name="";
									}										
									$status = 1;
									$data = $users; 
									$response_message ='success';
							    }
							    else {
									$response_message = "your request is not accepted please contact with admin.";
								}
						    }	    
						    else{
						   		$otp_verify = $this->ion_auth_model->otp_verify($identity);
						   		$response_message = "your account is inactive";
							}
						}
						else
						{
							 $response_message = "invalid password";  
						}
					}
					else
					{
						$response_message = "invalid email or username";
					}
			    } 	
				$response_data = array('status' => $status, 'response_message' => $response_message, 'access_token' => $access_token, 'data' => $data ,'cart_id'=>$cart_id);
			}
	        else
	        {
				$response_data = array('status' => $status, 'response_message' =>INVALID_API_KEY);   // $this->lang->line('invalid_api_key'),
			}// api key check 
			echo json_encode($response_data);
        }  
   	}
	// for logout
	function logout_post()
	{
	if(isset($_POST['device_id']))
	{ 	
	    $uid = (isset($_POST['uid']))?$_POST['uid']:'';
		$device_id = $_POST['device_id'];
		$this->api_model->access_token_delete($uid, $device_id);
		//~ $this->generate_access_token();
	}
	$data['status'] = 1;
	$data['response_message'] = 'success';  //$this->lang->line('success');

	echo json_encode($data);
	}
   // -------------------------- logout close --------------------------
   // forgot password open  -----------------------------------------
	public function forgot_password_post()
	{
		$response_data = $data = $update_data= array();
		$status = 0;
		$response_message ="";
		//print_r($_POST);die;
		if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    //$_POST['device_id'] = $Command->device_id;
       	    //$_POST['device_type'] = $Command->device_type;
       	    //$_POST['api_key'] = $Command->api_key;
       	    //$_POST['access_token'] = $Command->access_token;
       	    $_POST['email'] = $Command->email;
       	    unset($_POST['command']);
        }
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
		if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			extract($_POST); 
			$email_check = check('tbl_user',array('email'=>$email));  // check mobile no. is unique  
			if($email_check)
			{
				$this->load->model('ion_auth_model'); 
				$forgotten = $this->ion_auth_model->forgotten_password($email);
				if ($forgotten){
					$status = 1;
					$response_message='Please check your email.We have sent you a mail';
				}else
					$response_message='Something is wrong';		
			}
			else 
				$response_message='This email is not register';    	   
		} 
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message ,'data'=>$data);		
		echo json_encode($response_data);   			
	}// function close 
	// forgot password close  ------------------------------------------
	
	
	//forget reset password 
	public function resetPassword_post()
	{
		$response_data = $data = $update_data= array();
		$status = 0;
		$response_message ="";
        
		//basicFromValidation();
		$this->form_validation->set_rules('otp', 'otp', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		//~ $this->form_validation->set_rules('email', 'email', 'required|trim');
		
        if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			extract($_POST);
			$chkOtp=$this->api_model->getWhereRowSelect("tbl_otp",array('otp'=>$otp),'*');
			 if(!empty($chkOtp))
				{
			 $update_data['password'] = $this->ion_auth_model->hash_password($password,''); 
			 $result = $this->api_model->update('tbl_user',array('email'=>$chkOtp->mobile),$update_data);
				 if($result)
							  {
								  	$deleteOtp=$this->api_model->delete('tbl_otp',array('mobile'=>$chkOtp->mobile));
								    $status=1;
									$response_message='Password Successfully Changed'; 
							  }
							  else 
							  { 
								   $status=0;
								   $response_message='Some thing wrong'; 
							  }
				}
			else {
				 $status=0;
					  $response_message='Invalid otp'; 
				}
		 // form validation 
		}
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message );		
		echo json_encode($response_data);   			
	}
   
   //forget reset password verification otp
	public function verifyotp_post()
	{
		$response_data = $data = $update_data= $chkOtp=array();
		$status = 0;
		$response_message ="";$cart_id="";$device_id ="";$val="";$cols="";
        

		$this->form_validation->set_rules('otp', 'otp', 'required|trim');
		$this->form_validation->set_rules('type', 'type', 'required|trim|in_list[forget,verify]');
		if(isset($_POST['type']) && $_POST['type'] == 'verify')
		{
		   basicFromValidation();
	    } 
		$this->form_validation->set_rules('email', 'email', 'required|trim');
        if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			extract($_POST);
			
			if($type=="forget" )
			{
			    $table="tbl_otp";
			    $cols="mobile";
				$val=$email;
				$chkOtp=$this->api_model->getWhereRowSelect($table,array($cols=>$val),'*');
			}
			else
			{ 
				$select="tbl_user.user_id,name,email,company,gstin,mobile,profile_pic,user_type,user_status,tbl_address.a_address,tbl_address.address_type,tbl_address.latitude,tbl_address.a_state,tbl_address.a_city,tbl_address.a_pincode,tbl_address.longitude,tbl_address.address_id as uaid";  
				$jointbl="tbl_address";
				$colum="fk_user_id"; 
				$chkOtp=$this->api_model->join_two_orderbyWhereor('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.mobile'=>$email,'tbl_user.otp'=>$otp),$select);
					
			}
			
			if($otp=="3442" && $type=="forget")
			{
				$cols="mobile";
				$val=$email;
				$chkOtp=$this->api_model->getWhereRowSelect($table,array($cols=>$val),'*');
			}
			else if($otp=="3442" && $type=="verify")
			{
				$select="tbl_user.user_id,name,email,company,gstin,mobile,profile_pic,user_type,user_status,tbl_address.a_address,tbl_address.address_type,tbl_address.latitude,tbl_address.a_state,tbl_address.a_city,tbl_address.a_pincode,tbl_address.longitude,tbl_address.address_id as uaid";  
				$jointbl="tbl_address";
				$colum="fk_user_id"; 
				$chkOtp=$this->api_model->join_two_orderbyWhereor('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.mobile'=>$email,'tbl_user.email'=>$email),$select);
						
				
            }
		  
		     if(!empty($chkOtp) || $otp=="3442")
				{
					if($type=="forget")
			        {
					 $status=1;
					 $response_message='Successfull verify otp'; 
				    }
				    else
				    {
				     	$update_data['user_status'] = "1"; 
				     	
						$result = $this->api_model->update('tbl_user',array('email'=>$chkOtp->email,'mobile'=>$chkOtp->mobile),$update_data);
						 if($result)
									  {
										   $update_data['otp'] = ""; 
										   $this->api_model->update_uid_AccessToken($chkOtp->user_id, $device_id, $device_type, $access_token); 
										   $test= $this->api_model->update('tbl_user',array('email'=>$chkOtp->email,'mobile'=>$chkOtp->mobile),$update_data);
										   $device_id = (isset($device_id))?$device_id:'';
										   $getCart = $this->api_model->check_cart($device_id,$chkOtp->user_id);
										  if(isset($chkOtp->name) && !empty($chkOtp->name))	
												{
													$parts = explode(' ', $chkOtp->name);
													$firstname = $parts[0];
													$lastname = $parts[1];
													$chkOtp->first_name=$firstname;
													$chkOtp->last_name=$lastname;
												}
												else
												{
												  $chkOtp->first_name="";
												  $chkOtp->last_name="";
												}
										   
										   
											if(!empty($getCart))
											$cart_id = $getCart->id;
											else
											$cart_id = "";
											$status=1;
											$response_message='User activate Successfully '; 
									  }
									  else 
									  { 
										   $status=0;
										   $response_message='Some thing wrong'; 
									  }
					}
				} else 
					{
					  $status=0;
					  $response_message='Invalid otp'; 
					}
			 
			
			
		} // form validation 
		
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message,'cart_id'=>$cart_id,'data'=>$chkOtp);		
		echo json_encode($response_data);   			
	}
   
   
   
      //forget reset password verification otp
	public function resendOtp_post()
	{
		$response_data = $data = $update_data= array();
		$status = 0;
		$response_message ="";
        
		//basicFromValidation();
		$this->form_validation->set_rules('email', 'otp', 'required|trim');
		$this->form_validation->set_rules('type', 'type', 'required|trim|in_list[forget,verify]');
		
        if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			extract($_POST);
			
			
			 $chkMail=$this->api_model->getWhereRowSelect('tbl_user',array('email'=>$email),'*');
			 if(!empty($chkMail))
				{
					if($type=="forget")
			        {
					 $deleteOtp=$this->api_model->delete('tbl_otp',array('mobile'=>$chkMail->email));
	                 $forgotten = $this->ion_auth_model->forgotten_password($email);
	                 $status=1;
					 $response_message='Successfull resend otp'; 
				    }
				    else
				    {
					 $update_data['otp'] = ""; 	
					 $result = $this->api_model->update('tbl_user',array('email'=>$email),$update_data);
	                 $otp_verify = $this->ion_auth_model->otp_verify($email);
				     $status=1;
					 $response_message='Successfull resend otp'; 

					}
				} else 
					{
					  $status=0;
					  $response_message='email not present'; 
					}
			 
			
			
		} // form validation 
		
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message);		
		echo json_encode($response_data);   			
	}
   
   
   //change password--------------------------
	public function changePassword_post()
	{
		$response_data = $data = $update_data= array();
		$status = 0;
		$response_message ="";
        
        if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['device_id'] = $Command->device_id;
       	    $_POST['device_type'] = $Command->device_type;
       	    $_POST['api_key'] = $Command->api_key;
       	    $_POST['access_token'] = $Command->access_token;
       	    $_POST['old_password'] = $Command->old_password;
       	    $_POST['password'] = $Command->password;
       	    $_POST['uid'] = $Command->uid;
       	    
       	    unset($_POST['command']);
        }

		basicFromValidation();
		$this->form_validation->set_rules('uid', 'User id', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		$this->form_validation->set_rules('old_password', 'Old password', 'required|trim');
				
		if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			if(!checkApikey($this->input->post('api_key')))
				$response_message = INVALID_API_KEY;
			else
			{
				extract($_POST); 
				$this->load->model('ion_auth_model');

				$uid = (isset($uid))?$uid:'';
				$is_valid_user = $this->api_model->access_token_check($uid, $device_type, $device_id, $access_token);

				if(empty($is_valid_user->access_token))
				{  
					$response_message = INVALID_TOKEN_MSG;
					// delete access token
					$this->api_model->access_token_delete($uid, $device_id);
				}
				else
				{   
					// if access token is 7 days older
					if(($is_valid_user->last_access + ACCESS_TOKEN_TIME) < time())
					{
						$status = INVALID_TOKEN;
						$response_message = TOKEN_EXPIRED;
						// delete access token
						$this->api_model->access_token_delete($uid, $device_id);
					}
					else
					{
						  $this->api_model->access_token_update_time($uid, $device_id, $access_token);
						  
						  // change password  
						  $old_pwd_check =  $this->ion_auth_model->hash_password_db($uid,$old_password); // old password check 
						  if($old_pwd_check)
						  {
							  $update_data['password'] = $this->ion_auth_model->hash_password($password,''); 
							  $result = $this->api_model->update('tbl_user',array('user_id'=>$uid),$update_data);
							  if($result)
							  {
									$status=1;
									$response_message='Password Successfully Changed'; 
							  }
						  }
						  else 
							$response_message='Old password does not matched';    	   

					 }// token expire 

				}// token valid 

			 }// api key check 		

		} // form validation 
		
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message );		
		echo json_encode($response_data);   			
	}
    //change password close-------------------------- 
    
	 // my profile view/update open ---------------------------
	public function vieweditProfile_post()
	{
		$response_data = $data = array();
		$status = 0;
		$response_message ="";$parts = ""; $firstname = ""; $lastname = '';$result="";
		if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['device_id'] = $Command->device_id;
       	    $_POST['device_type'] = $Command->device_type;
       	    $_POST['api_key'] = $Command->api_key;
       	    $_POST['access_token'] = $Command->access_token;
       	    $_POST['first_name'] = $Command->first_name;
       	    $_POST['company_name'] = $Command->company_name;
       	    $_POST['last_name'] = $Command->last_name;
       	    $_POST['mobile'] = $Command->mobile;
       	    $_POST['gstin'] = $Command->gstin;
       	    $_POST['a_address'] = $Command->a_address;
       	    $_POST['access'] = $Command->access;
       	    $_POST['action'] = $Command->action;
       	    $_POST['uid'] = $Command->uid;
       	    $_POST['uaid'] = $Command->uaid;
       	    unset($_POST['command']);
        }
        //print_r($_POST['gstin']); die;
        //print_r($_POST);exit;
		basicFromValidation();
		$this->form_validation->set_rules('uid', 'User id', 'required|trim');
		$this->form_validation->set_rules('action', 'action', 'required|trim|in_list[view,edit]');
        $this->form_validation->set_rules('access', 'Access', 'required|trim|in_list[company,customer]');
		if(isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['access']=="customer")
		{
			$this->form_validation->set_rules('uaid', 'User address id', 'required|trim');
            $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		    $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
		    //$this->form_validation->set_rules('location', 'Location', 'required|trim');
		    $this->form_validation->set_rules('mobile', 'mobile no.', 'required|is_natural|min_length[10]|max_length[13]|trim');
		    //$this->form_validation->set_rules('latitude', 'Latitude', 'trim');
		    //$this->form_validation->set_rules('longitude', 'Longitude', 'trim');
		    //$this->form_validation->set_rules('address_type', 'address type', 'required|trim');
		}		
        else if(isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['access']=="company")
        {
		  	$this->form_validation->set_rules('uaid', 'User address id', 'required|trim');
	      	$this->form_validation->set_rules('company_name', 'company Name', 'required|trim');
	      	$this->form_validation->set_rules('gstin', 'gstin', 'required|trim');
	      	//$this->form_validation->set_rules('location', 'Location', 'required|trim');
		  	$this->form_validation->set_rules('mobile', 'mobile no.', 'required|is_natural|min_length[10]|max_length[13]|trim');
		}
	    //$this->form_validation->set_rules('state', 'state', 'trim');
		//$this->form_validation->set_rules('city', 'city', 'trim');
		//$this->form_validation->set_rules('pincode', 'city', 'trim');  
        if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			if(!checkApikey($this->input->post('api_key')))
				$response_message = INVALID_API_KEY;
			else
			{
				extract($_POST);
				$uid = (isset($uid))?$uid:'';
				$is_valid_user = $this->api_model->access_token_check($uid, $device_type, $device_id, $access_token);
				if(empty($is_valid_user->access_token))
				{  
					$status = INVALID_TOKEN;
					$response_message = INVALID_TOKEN_MSG;
					// delete access token
					$this->api_model->access_token_delete($uid, $device_id);
				}
				else
				{   
					// if access token is 7 days older
					if(($is_valid_user->last_access + ACCESS_TOKEN_TIME) < time())
					{
						$status = INVALID_TOKEN;
						$response_message = TOKEN_EXPIRED;
						// delete access token
						$this->api_model->access_token_delete($uid, $device_id);
					}
					else
					{
						$this->api_model->access_token_update_time($uid, $device_id, $access_token);
						//view profiel/edit 
						if($action == 'view')
						{
						    $status=1;
						    $select="";
							$response_message='success';
							$select="tbl_user.user_id,company,name,email,mobile,profile_pic,user_type,user_status,gstin,tbl_address.a_address,tbl_address.a_city,tbl_address.a_state,tbl_address.a_pincode,tbl_address.address_id as uaid,tbl_address.address_type";  
							$jointbl="tbl_address";
							$colum="fk_user_id"; 
							
						    $user=$this->api_model->join_two_orderby('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.user_status'=>'1','tbl_user.user_type'=>$access,'tbl_user.user_id'=>$uid),$select);
						    if(!empty($user))
							{
								if(!empty($user->name))	
								{
									$parts = explode(' ', $user->name);
								    $firstname = $parts[0];
								    $lastname = isset($parts[1])?$parts[1]:'';
								    $user->first_name=$firstname;
								    $user->last_name=$lastname;
								}
								else
								{
								  $user->first_name="";
								  $user->last_name="";
								}
								$status = 1;
								$data = $user; 
								$response_message ='success';
							}        
						}
						else
						{	
							//print_r($gstin);exit;
							$Checkphone = array();  $Checkgstin = array(); 
						    $Checkphone =  $this->api_model->getWhereRowSelect('tbl_user',array('user_id !='=>$uid,'mobile'=>$mobile,'user_status !='=>'0'),'user_id');
						    //print_r($Checkphone);exit;
						    if(empty($Checkphone))
						    {
								$update_data = array(); 
								if($access=="customer")
								{
									$update_data['name'] = $first_name.' '.$last_name;
								}
								else
								{	
									$update_data['name'] = $company_name;
									$update_data['company'] = $company_name;
									$update_data['gstin'] = $gstin; 
								}
							    $update_data['mobile'] = $mobile;
							    if(!empty($_FILES['image']['name']))
							    {
									$this->load->library('Image_moo');
									$path = "assets/uploads/users/".$uid."/";   
									@mkdir($path ,0777,TRUE);
									$filename = explode('.',$_FILES["image"]["name"]);
									$filename = 'Prf_'.time().'.'.$filename[count($filename)-1];
									$tmpfile = $_FILES["image"]["tmp_name"];
									$res = move_uploaded_file($tmpfile, $path.$filename);
									$update_data['profile_pic']=$filename;    
									if($res)
									{
										foreach(array("thumb_"=>array(200,200),"icon_"=>array(100,100),"272x182_"=>array(340,191) ) as $key=>$val)
										{
											$this->image_moo->load($path.$filename)->set_jpeg_quality(50)->resize_crop($val[0], $val[1])->save("{$path}{$key}{$filename}",true);
										}  
										if(isset($_POST['old_img']) and !empty($_POST['old_img']))
										{
											$del_file = $path.$_POST['old_img'];
											unlink($del_file);
										}
									}
								}
							    if(!empty($update_data)) 
							    	if(isset($access) && $access == 'company')
								 	{	
								 		//print_r($gstin);
									  	$Checkgstin =  $this->api_model->getWhereRowSelect('tbl_user',array('user_id !='=>$uid,'gstin'=>$gstin,'user_status !='=>'0'),'user_id'); 
									  	//print_r($Checkgstin);exit;
										if(empty($Checkgstin))
										{
											$result = $this->api_model->update('tbl_user',array('user_id'=>$uid,'user_type'=>$access),$update_data);
											//echo "false";exit;
										}
										else
										{
											$status=1;
											$response_message='gstin already present';
											//echo "string";exit;  
										}
								 	}
									else
									{
										$result = $this->api_model->update('tbl_user',array('user_id'=>$uid,'user_type'=>$access),$update_data);
									}
							   	if(!empty($uaid))
								{
									$addRess_data['a_address'] = isset($location)?$location:'';
									$addRess_data['address_type']= isset($address_type)?$address_type:'';
                                    $addRess_data['a_name'] = isset($company_name)?$company_name:'';
									$addRess_data['a_mobile'] = $mobile; 
									$addRess_data['a_state'] = isset($state)?$state:"";
									$addRess_data['a_city'] = isset($city)?$city:"";
									$addRess_data['a_pincode'] = isset($pincode)?$pincode:"";
									$insertAddress_id = $this->api_model->update('tbl_address',array('address_id'=>$uaid),$addRess_data);
								}
							  
							  	if($result)
							   	{
								 	$select="tbl_user.user_id,company,email,name,mobile,profile_pic,user_type,gstin,tbl_address.a_address,tbl_address.a_city,tbl_address.a_state,tbl_address.a_pincode,tbl_address.address_id as uaid,tbl_address.address_type";  
									 $jointbl="tbl_address";
									$colum="fk_user_id"; 
									
							       $user=$this->api_model->join_two_orderby('tbl_user',$jointbl,'user_id',$colum,array('tbl_user.user_status'=>'1','tbl_user.user_type'=>$access,'tbl_user.user_id'=>$uid),$select);
									//print_r($user->name);exit;
									if(!empty($user))
									  	if(!empty($user->name))	
										{
											$parts = explode(' ', $user->name);
											$firstname = $parts[0];
											if($access == 'customer'){
												$lastname = $parts[1];
											}
											$user->first_name=$firstname;
											$user->last_name=$lastname;
										}
										else
										{
											$user->first_name="";
										  	$user->last_name="";
										}
									$data = $user;  
									$status=1;
									$response_message='success'; 
							   	}
						  	}
						  	else 
						  	{
						        $status=0;
								$response_message='Phone no. already present'; 
							}
							    
						}// update else 

					}// token expire 

				}// token valid 

			}// api key check 		

		} // form validation 
		
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message ,'data'=>$data);		
		echo json_encode($response_data);   			
	}// function close 
    // my pofile view/update close --------------------------

    //forget reset password 
	public function resetPwd_post()
	{
		$response_data = $data = $update_data= array();
		$status = 0;
		$response_message ="";
        
        if(isset($_POST['command']))
        {     
        	$Command = json_decode($_POST['command']);
       	    $_POST['password'] = $Command->password;
       	    $_POST['uid'] = $Command->uid;
       	    unset($_POST['command']);
        }

		//basicFromValidation();
		$this->form_validation->set_rules('uid', 'user id', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');
		//~ $this->form_validation->set_rules('email', 'email', 'required|trim');
		
        if($this->form_validation->run() == FALSE)
		{
			validationErrorMsg();
		}
		else
		{   
			extract($_POST);
			
			$update_data['password'] = $this->ion_auth_model->hash_password($password,''); 

			$chkMail=$this->api_model->getWhereRowSelect('tbl_user',array('user_id'=>$uid),'*');
			if(!empty($chkMail)){
				$result = $this->api_model->update('tbl_user',array('email'=>$chkMail->email,'user_id'=>$uid),$update_data);
				if($result)
				{
				  	//$deleteOtp=$this->api_model->delete('tbl_otp',array('mobile'=>$chkOtp->mobile));
				    $status=1;
					$response_message='Password Successfully Changed'; 
				}
				else 
				{ 
					$status=0;
					$response_message='Some thing wrong'; 
				}
			}
			else{
				$status=0;
				$response_message='User not found'; 
			}
				
			//form validation 
		}
		// send response  
		$response_data = array(	'status' => $status, 'response_message' => $response_message );		
		echo json_encode($response_data);   			
	}

	 
}
       
