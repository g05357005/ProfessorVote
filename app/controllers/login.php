<?php

/**
 *
 */
class Login extends CI_Controller {

	function index() {
		$data['main_content'] = 'login_form';
		$this -> load -> view('includes/template', $data);
	}
/*
 * validates a users credentials.  If they are corrent, the user is logged in 
 * and returned to the home page.  If they are incorrect, they are redirected 
 * back to the login page with the appprorate error message.
 */
	function validate_credentials() {
		$this -> load -> library('form_validation');
		$this -> form_validation -> set_error_delimiters('<div class="alert alert-error">', '</div>');
		$this -> form_validation -> set_rules('username', 'Username', 'trim|required|min_length[4]');
		$this -> form_validation -> set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');

		if ($this -> form_validation -> run() == FALSE) {
			$data['main_content'] = 'login_form';
			$this -> load -> view('includes/template', $data);
		} else {
			$this -> load -> model('User_model');
			$username = $this -> input -> post('username');
			$password = $this -> input -> post('password');
			$query = $this -> User_model -> validate($username, $password);
			if ($query)// if the user's credentials validated...
			{
				$id = $this->User_model->getID($username);
				$data = array('username' => $this -> input -> post('username'), 'is_logged_in' => true, 'userid'=>$id);
				$this -> session -> set_userdata($data);
				redirect('home');
			} else// incorrect username or password
			{
				$data['main_content'] = 'login_form';
				$this -> load -> view('includes/template', $data);
			}
		}
	}
/*
 * loads the sign up page
 */
	function signup() {
		//$data['main_content'] = 'signup_form';
		$data['main_content'] = 'auth/register_form';
		$this -> load -> view('includes/template', $data);
	}
/*
 * validates users input for registration.  If successfull, the user is redirected home.
 * If unsucessful, the user is redirected to the create user form.
 */
	function create_user() {
		$this -> load -> library('form_validation');
		$this -> form_validation -> set_error_delimiters('<div class="alert alert-error">', '</div>');
		// field name, error message, validation rules
		$this -> form_validation -> set_rules('first_name', 'Name', 'trim|required');
		$this -> form_validation -> set_rules('last_name', 'Last Name', 'trim|required');
		$this -> form_validation -> set_rules('email_address', 'Email Address', 'trim|required|valid_email|is_unique[user.email_address]');
		$this -> form_validation -> set_rules('username', 'Username', 'trim|required|min_length[4]|is_unique[user.username]');
		$this -> form_validation -> set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
		$this -> form_validation -> set_rules('password2', 'Password Confirmation', 'trim|required|matches[password]');

		if ($this -> form_validation -> run() == FALSE) {
			$data['main_content'] = 'signup_form';
			$this -> load -> view('includes/template', $data);
		} else {
			$this -> load -> model('User_model');
			$first_name = $this -> input -> post('first_name');
			$last_name = $this -> input -> post('last_name');
			$email_address = $this -> input -> post('email_address');
			$username = $this -> input -> post('username');
			$password = $this -> input -> post('password');

			if ($this -> User_model -> create_user($first_name, $last_name, $email_address, $username, $password)) {
				$data = array('username' => $this -> input -> post('username'), 'is_logged_in' => true);
				$this -> session -> set_userdata($data);
				redirect('home');
				//TODO:log in user
			} else {
				$data['main_content'] = 'signup_form';
				$this -> load -> view('includes/template', $data);
			}
		}

	}
/*
 * logs the user out.
 */
	function logout() {
		$this -> session -> sess_destroy();
		redirect('home');
	}
/*
 * AJAX method to validate a users credentials
 */
	function ajax_check() {
		if ($this -> input -> post('ajax') == '1') {
			$this -> load -> library('form_validation');
			$this -> form_validation -> set_error_delimiters('<div class="alert alert-error">', '</div>');
			$this -> form_validation -> set_rules('username', 'Username', 'trim|required|min_length[4]');
			$this -> form_validation -> set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
			if ($this -> form_validation -> run() == FALSE) {
				echo validation_errors();
			} else {
				$this -> load -> model('User_model');
				$username = $this -> input -> post('username');
				$password = $this -> input -> post('password');
				$query = $this -> User_model -> validate($username, $password);
				if ($query)// if the user's credentials validated...
				{
					$id = $this->User_model->getID($username);
					$data = array('username' => $this -> input -> post('username'), 'is_logged_in' => true, 'userid'=>$id);
					$this -> session -> set_userdata($data);
					echo 'true';
				} else// incorrect username or password
				{
					echo "<div class=\"alert alert-error\">Wrong Username or password.</div>";
				}
			}
		}
	}
/*
 * AJAX method to validate create user input
 */
	function create_user_ajax() {
		if ($this -> input -> post('ajax') == '1') {
			$this -> load -> library('form_validation');
			$this -> form_validation -> set_error_delimiters('<div class="alert alert-error">', '</div>');
			// field name, error message, validation rules
			$this -> form_validation -> set_rules('first_name', 'Name', 'trim|required');
			$this -> form_validation -> set_rules('last_name', 'Last Name', 'trim|required');
			$this -> form_validation -> set_rules('email_address', 'Email Address', 'trim|required|valid_email');
			$this -> form_validation -> set_rules('username', 'Username', 'trim|required|min_length[4]');
			$this -> form_validation -> set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
			$this -> form_validation -> set_rules('password2', 'Password Confirmation', 'trim|required|matches[password]');

			if ($this -> form_validation -> run() == FALSE) {
				echo validation_errors();
			} else {
				$this -> load -> model('User_model');
				$first_name = $this -> input -> post('first_name');
				$last_name = $this -> input -> post('last_name');
				$email_address = $this -> input -> post('email_address');
				$username = $this -> input -> post('username');
				$password = $this -> input -> post('password');

				if ($this -> User_model -> checkUniqueEmail($email_address)) {
					echo "<div class=\"alert alert-error\">Email is already registered.</div>"; 
				} else if ($this -> User_model -> checkUniqueUser($username)) {
					echo "<div class=\"alert alert-error\">Username already registered.</div>";
				} else {

					if ($this -> User_model -> create_user($first_name, $last_name, $email_address, $username, $password)) {
						echo 'true';
					} else {
						echo "<div class=\"alert alert-error\">Unknown Error occured.</div>";
					}
				}
			}

		}
	}

}
