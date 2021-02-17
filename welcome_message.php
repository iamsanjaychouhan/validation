<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<style type="text/css">
		::selection { background-color: #E13300; color: white; }
		::-moz-selection { background-color: #E13300; color: white; }
	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}
	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}
	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}
	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}
	#body {
		margin: 0 15px 0 15px;
	}
	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
</head>
<body>

<div id="container">
	<div id="res_message" class="alert alert-danger" style="display: none;"></div>
	<form class="form-control" name="register-form" id="register-form" action="" method="post" enctype="multipart/form-data">
		<input type="text" name="name" id="name" class="form-control" placeholder="Name">
		<input type="email" name="email" id="email" class="form-control" placeholder="Email">
		<input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile">
		<input type="file" name="image_file" id="image_file" class="form-control">
		<button name="Register" class="btn btn-primary" id="btn-register">Register</button>
		<span id="all_field_err"><?php echo form_error('all_field');?></span>
		<span id="name_err"><?php echo form_error('name');?></span>
		<span id="email_err"><?php echo form_error('email');?></span>
		<span id="mobile_err"><?php echo form_error('mobile');?></span>
		<span id="image_err"><?php echo form_error('image');?></span>
	</form>
	<!-- <br><br><br><br>
	<form method="post" id="upload_form" align="center" enctype="multipart/form-data">  
	    <input type="file" name="image_file" id="image_file" />  
	    <br/>  
	    <br/>  
	    <input type="submit" name="upload" id="upload" value="Upload" class="btn btn-info" /> 
	</form>  
		<br/>  
		<br/>  
	<div id="uploaded_image">  
	</div> --> 


	<!-- <div class="container">
		<h2>Codeigniter Ajax Validation</h2>
		<div class="alert alert-danger print-error-msg" style="display:none">
	    </div>
		<form>
			<div class="form-group">
				<label>First Name:</label>
				<input type="text" name="first_name" class="form-control" placeholder="First Name">
			</div>
			<div class="form-group">
				<label>Last Name:</label>
				<input type="text" name="last_name" class="form-control" placeholder="Last Name">
			</div>
			<div class="form-group">
				<strong>Email:</strong>
				<input type="text" name="email" class="form-control" placeholder="Email">
			</div>
			<div class="form-group">
				<strong>Mobile:</strong>
				<input type="text" name="mobile" class="form-control" placeholder="mobile">
			</div>
			<div class="form-group">
				<strong>Address:</strong>
				<textarea class="form-control" name="address" placeholder="Address"></textarea>
			</div>
			<div class="form-group">
				<button class="btn btn-success btn-submit">Submit</button>
			</div>
		</form>
	</div> -->
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$("#register-form").on('submit',function(e){
			e.preventDefault();
			$("#image_err").text();
			$("#name_err").text();
			$("#email_err").text();
			$("mobile_err").text();
			var token = true;
			var name = $("#name").val();
			var email = $("#email").val();
			var mobile = $("#mobile").val();
			var image_file = $('#image_file').val();
			if(name== ''){
				$("#name_err").text('Please fill name field');token=false;
			}
			if(email == ''){
				$("#email_err").text('Please fill email field');token=false;
			}
			if(mobile == ''){
				$("#mobile_err").text('Please fill mobile field');token=false;
			}
			if(image_file == ''){
				$("#image_err").text('Please choose image');
				token=false;
			}
			if(token == true){
				$.ajax({
					url:"<?php echo base_url('welcome/register');?>",
					method:"POST",
					dataType: "json",
					data:new FormData(this),
					contentType: false,  
				    cache: false,  
				    processData:false, 
					success: function(data) {
		                if($.isEmptyObject(data.error)){
		                	$(".print-error-msg").css('display','none');
		                	alert(data.success);
		                }else{
							$("#res_message").css('display','block');
		                	$("#res_message").html(data.error);
		                }
		            }
				});
			}
		});
	});
</script>
<!-- <script type="text/javascript">
	$(document).ready(function() {
	    $(".btn-submit").click(function(e){
	    	e.preventDefault();
	    	var _token = $("input[name='_token']").val();
	    	var first_name = $("input[name='first_name']").val();
	    	var last_name = $("input[name='last_name']").val();
	    	var email = $("input[name='email']").val();
	    	var mobile = $("input[name='mobile']").val();
	    	var address = $("textarea[name='address']").val();
	        $.ajax({
	        	url:"<?php echo base_url('welcome/itemForm');?>",
	            type:'POST',
	            dataType: "json",
	            data: {first_name:first_name, last_name:last_name, email:email, mobile:mobile,address:address},
	            success: function(data) {
	                if($.isEmptyObject(data.error)){
	                	$(".print-error-msg").css('display','none');
	                	alert(data.success);
	                }else{
						$(".print-error-msg").css('display','block');
	                	$(".print-error-msg").html(data.error);
	                }
	            }
	        });
	    }); 
	});
</script> -->
<!-- <script>  
	$(document).ready(function(){  
	$('#upload_form').on('submit', function(e){  
	e.preventDefault();  
	var img = $('#image_file').val();
	alert(img);
	if($('#image_file').val() == '')  
	{  
	alert("Please Select the File");  
	}  
	else  
	{  
	$.ajax({  
	     url:"<?php echo base_url(); ?>welcome/ajax_upload",   
	    method:"POST",  
	    data:new FormData(this),  
	    contentType: false,  
	    cache: false,  
	    processData:false,  
	    success:function(data)  
	    {  
	        $('#uploaded_image').html(data);  
	    }  
	});  
	}  
	});  
	});  
</script> -->  
</body>
</html>