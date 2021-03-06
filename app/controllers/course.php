<?php

class course extends CI_Controller {
/*
 * if no method is called from the coure controller the homepage is returned
 */
	function index() {
		$data['main_content'] = 'HomePage';		
		$this -> load -> view('includes/template', $data);
	}
/*
 * loads a single course view. If any of the parameters are invalid, the user is
 * returned to the most appropriate page.
 */
	function view($state, $collegeName, $professorFirstName, $professorLastName, $department, $catalogNumber) {
		$this -> load -> model("College_model");
		$this -> load -> model("Course_model");
		$this -> load -> model("Professor_model");
		$this -> load -> model("State_model");
		$courseID;
		$professorID;

		if ($collegeName != null && $professorFirstName != null && $professorLastName != null && $department != null && $catalogNumber != null) {
				$collegeName = urldecode($collegeName);
			$professorFirstName = urldecode($professorFirstName);
			$professorLastName = urldecode($professorLastName);
			$department = urldecode($department);
			$state = urldecode($state);
			$catalogNumber = urldecode($catalogNumber);
			
			
			if ($this -> State_model -> stateExists($state) == FALSE) {
				$data['main_content'] = 'HomePage';
				$this -> load -> view('includes/template', $data);
			} else if ($this -> College_model -> collegeExists($collegeName, $state) == FALSE) {
				$data['main_content'] = 'HomePage';
				$this->session->set_flashdata('state', urldecode($state));
				$this -> load -> view('includes/template', $data);
			} else if ($this -> Professor_model -> professorExists($professorFirstName, $professorLastName, $department) == FALSE) {
				redirect(site_url("CollegePage/" . urlencode($state) . "/" . urlencode($collegeName)));
				//$courseID=$this->Course_model->getID();
				//$professorID = $this->Professor_model->getID($professorFirstName,$professorLastName,$department);
			} else if ($this -> Course_model -> courseProfessorExists($this -> Course_model -> getID($catalogNumber, $this -> College_model -> getID($collegeName, $state)), $this -> Professor_model -> getID($professorFirstName, $professorLastName, $department)) == FALSE) {
				redirect(site_url("Professor/view/" . urlencode($state). "/" . urlencode($collegeName). "/" . urlencode($professorFirstName) . "/" . urlencode($professorLastName) . "/" . urlencode($department)));
			} else {
				$data['professorFirstName'] = $professorFirstName;
				$data['professorLastName'] = $professorLastName;
				$data['department'] = $department;
				$data['state'] = $state;
				$data['catalogNumber'] = $catalogNumber;
				$data['collegeName'] = $collegeName;
				$collegeID = $this -> College_model -> getID($collegeName, $state);
				$professorID = $this -> Professor_model -> getID($professorFirstName, $professorLastName, $department);
				$courseID = $this -> Course_model -> getID($catalogNumber, $collegeID);

				$courseID = $this -> Course_model -> getCourseID($collegeID, $catalogNumber);
				$data['courseName'] = $this -> Course_model -> getCourseName($courseID);
				$professorID = $this -> Professor_model -> getID($professorFirstName, $professorLastName, $department);
				$data['comments'] = $this -> Course_model -> getComments($courseID, $professorID);
				log_message("debug", $this -> Course_model -> getComments($courseID, $professorID));
				$data['professorID'] = $professorID;
				$data['courseID'] = $courseID;
				$data['main_content'] = 'CourseView';
				$this -> load -> view('includes/template', $data);
			}
		} else {
			$data['main_content'] = 'HomePage';
			$this -> load -> view('includes/template', $data);
		}

	}
/*
 * AJAX method to add a course to the database.  
 * All validation and error messages are handled in this message.
 */

	public function add() {
		$this -> load -> model('Course_model');
		$this -> load -> model('College_model');
		$this -> load -> model('Professor_model');
		$catalogNumber = urldecode($this -> input -> post('catalog_number'));
		$response = "";

		if ($catalogNumber === "" or $catalogNumber == null) {
			if ($response === "" or $response == null) {
				$response = "catalog_number_err#Please provide a course catalog number.";
			} else {
				$response .= ('|' . "catalog_number_err#Please provide a course catalog number.");
			}

		} elseif (strlen($catalogNumber) < 6) {
			if ($response === "" or $response == null) {
				$response = "catalog_number_err#The course catalog number must be atleast 6 characters.";
			} else {
				$response .= ('|' . "catalog_number_err#The course catalog number must be atleast 6 characters.");
			}
		} elseif (strlen($catalogNumber) > 12) {
			if ($response === "" or $response == null) {
				$response = "catalog_number_err#The course catalog number must not exceed 12 characters.";
			} else {
				$response .= ('|' . "catalog_number_err#The course catalog number must not exceed 12 characters.");
			}
		}
//TODO:sort by time or votes
		//log_message('debug',"catalog_number: " . $catalogNumber);
		$collegeName = $this -> input -> post('college_name');
		$courseName = $this -> input -> post('course_name');
		$firstName = $this -> input -> post('professor_first_name');
		$lastName = $this -> input -> post('professor_last_name');
		$department = $this -> input -> post('professor_department');
		$state = $this -> input -> post('state');
		$professorID = $this -> Professor_model -> getID($firstName, $lastName, $department);

		if ($courseName === "" or $courseName == null) {
			if ($response === "" or $response == null) {
				$response = $response . "course_name_err#Please provide a course name.";
			} else {
				$response .= ('|' . "course_name_err#Please provide a course name.");
			}

		} elseif (strlen($courseName) < 4) {
			if ($response === "" or $response == null) {
				$response = $response . "course_name_err#The course name must be atleast 4 characters.";
			} else {
				$response .= ('|' . "course_name_err#The course name must be atleast 4 characters.");
			}
		} elseif (strlen($courseName) > 64) {
			if ($response === "" or $response == null) {
				$response = $response . "course_name_err#The course name must not exceed 64 characters.";
			} else {
				$response .= ('|' . "course_name_err#The course name must not exceed 64 characters.");
			}
		}
		if ($response != "") {
			echo $response;
			return;
		}

		$collegeID = $this -> College_model -> getID($collegeName, $state);

		if ($collegeID == null || $collegeID == 'error' || $collegeID == '') {
			echo "error_msg#Error pulling Professor's College data";
			return;
		}
		if ($professorID == null || $professorID == 'error' || $professorID == '') {
			echo "error_msg#Error pulling Professor's data";
			return;
		}
		//need to check if course exists. if it does, see if course/professor exists
		$result = $this -> Course_model -> courseExists($catalogNumber, $collegeID);
		if ($result == TRUE) {
			//$this -> form_validation -> set_message('course_check', 'The course and college combination you entered already exists.');
			//handle error where there already exists that combo

			$courseID = $this -> Course_model -> getID($catalogNumber, $collegeID);
			$result2 = $this -> Course_model -> courseProfessorExists($courseID, $professorID);
			if ($result2 == TRUE) {
				echo "error_msg#This professor already teaches this class.";
				return;
			}

		}// else {
		//echo "true";
		//	return;
		//}

		//no error, add course
		//insert into DB
		$course_name = $this -> input -> post('course_name');
		$catalog_number = $this -> input -> post('catalog_number');
		$college_name = $this -> input -> post('college_name');
		$college_id = $this -> College_model -> getID($college_name, $state);

		if ($college_id == 'error' || $college_id == '' || $college_id == null) {
			echo 'college_error';
			return;
		}

		if ($this -> Course_model -> add_course($catalog_number, $course_name, $college_id, $professorID)) {
			//success
			echo 'success';
			return;
		} else {
			echo 'error';
			log_message("debug", "Error creating course for catalog number: '" . $catalog_number . "' course name: '" . $course_name . "' college name: '" . $college_name);
			return;
		}

	}
/*
 * AJAX method to add a comment to a course. Validation and error messages are
 * handled in this method.  If a comment is added, the most current comment
 * data for a course is returned to the page.
 */
	function comment() {
		$this -> load -> model('Course_model');
		$comment =urldecode(trim( $this -> input -> post('comment')));
		$courseID = urldecode($this -> input -> post('courseID'));
		$professorID = urldecode($this -> input -> post('professorID'));
		if ($this -> Course_model -> courseProfessorExists($courseID, $professorID) == false) {
			echo "Course and Professor combination do not exist.";
			return;
		}
		else if ($comment == null || $comment =="") {
			echo "null";
			return;
		}

		$result = $this -> Course_model -> addComment($comment, $professorID, $courseID);
		if ($result) {
			echo $this -> getCommentHtml($courseID, $professorID);
		} else {
			echo "System error adding comment.";
		}

	}
/*
 * method to return a string of HTML of all comments in a given course
 */
	private function getCommentHtml($courseID, $professorID) {
		$comments = $this -> Course_model -> getComments($courseID, $professorID);
		$result = "";
		foreach ($comments as $comment) {
			$result .= '<div class="well">' . '<div style="float:right;display:block-inline;"><h6>'.$comment -> DateString .'</h6></div>'. $comment -> Comment .'</div>';
		}
		return $result;
	}

}
