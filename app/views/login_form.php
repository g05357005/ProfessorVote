<div class="container" >	<?php	echo form_open('login/validate_credentials');	?>	<div class="hero-unit">		<form class="form-horizontal">			<fieldset>				<div class="control-group">					<div class="page-header">						<h1>Please Login</h1>					</div>					<div class="controls" style="margin-bottom: 1em">						<?php						echo form_error('username');						$usernameAttributes = array('id' => 'username', 'class' => 'input-xlarge', 'placeholder' => 'Username', 'type' => 'text', 'name' => 'username');						echo form_input($usernameAttributes);						?>					</div>					<div class="controls">						<?php						echo form_error('password');						$passwordAttributes = array('id' => 'password', 'class' => 'input-xlarge', 'placeholder' => 'Password', 'type' => 'text', 'name' => 'password');						echo form_password($passwordAttributes);						?>					</div>				</div>				<div class="form-actions">					<div>						<?php						$submitAttributes = array('id' => 'submit', 'class' => 'btn btn-large btn-primary', 'placeholder' => 'Password', 'value' => 'Login', 'type' => 'submit');						echo form_submit($submitAttributes);						?>						<?php						$anchorAttributes = array('id' => 'signup', 'class' => 'btn');						echo anchor('login/signup', 'or Create Account', $anchorAttributes);						?>					</div>				</div>			</fieldset>		</form>		<?php		form_close();		?>	</div></div>