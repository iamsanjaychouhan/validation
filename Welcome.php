<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library(['Form_validation','session']);
		$this->load->helper(['url', 'form']);
		$this->load->model('Other_model','other');
	}
	public function index()
	{
		$this->load->view('welcome_message');
	}
	public function register(){
		if(!empty($_POST)){
			$name = $_POST['name'];
			$email = $_POST['email'];
			$mobile = $_POST['mobile'];
			$this->form_validation->set_rules('name','name','required|trim');
			$this->form_validation->set_rules('email','email','required|trim|is_unique[tbl_user.email]',array('is_unique'=>'this email is already exist'));
			$this->form_validation->set_rules('mobile','mobile','required|trim|is_unique[tbl_user.mobile]',array('is_unique'=>'this mobile is already exist'));
			if($this->form_validation->run() === false){
				$errors = validation_errors();
				$data = array('success'=>false,'msg'=>$errors);
			}else{
				extract($_POST);
				print_r($_FILES["image"]["name"]);exit;
				if(isset($_FILES["image"]["name"]))  
	           	{  
	                $config['upload_path'] = './upload/';  
	                $config['allowed_types'] = 'jpg|jpeg|png|gif';  
	                $this->load->library('upload', $config);  
	                if(!$this->upload->do_upload('image'))  
	                {  
	                     echo $this->upload->display_errors();  
	                }  
	                else  
	                {  
	                     $data = $this->upload->data();  
	                     echo '<img src="'.base_url().'upload/'.$data["file_name"].'" width="300" height="225" class="img-thumbnail" />';  
	                }  
	           }
				print_r($_POST);

			}
			echo json_encode($data);
		}else{
			echo "error";
		}
	}
}
