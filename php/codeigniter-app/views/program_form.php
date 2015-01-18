<?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal')); ?>
	<fieldset>
		<legend>Applicant Info</legend>
		<?php echo $applicant; ?>
	</fieldset>
	<fieldset>
		<legend>Proposal Info</legend>
		<?php $db_field = 'funding_programs';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Funding Program(s)</span>
			<div class="controls">
				<p>Please check the program(s) to which you are applying. If this proposal applies to more than one program simultaneously, please mark the relevant boxes</p>
				<?php 
				foreach(array('Pre-College Program', 'Public Outreach Program', 'Teacher Training Program') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $field = 'new_app'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('New/Continuation Proposal', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_dropdown($field, array(
				'' => '',
				'This is a new proposal' => 'This is a new proposal',
				'This proposal is for continuation of an effort funded last year' => 'This proposal is for continuation of an effort funded last year',
			), set_value($field, $form->{$field}));
			?>
			</div>
		</div>
		
		
		<?php $db_field = 'targets';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Target(s)</span>
			<div class="controls">
				<p>This Proposal Targets (please select all that apply)</p>
				<?php 
				foreach(array('K-5', '6-8', '9-12', 'college', 'general public', 'under-represented minorities', 'women', 'disabled individuals') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $field = 'focus'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Focus', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_dropdown($field, array(
				'' => '',
				'aerospace' => 'aerospace',
				'space science' => 'space science',
				'Earth system science' => 'Earth system science',
				'other' => 'other',
			), set_value($field, $form->{$field}));
			?>
			</div>
		</div>
		
			
		<?php $field = 'activities_occur'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('The activities involved occur on', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_dropdown($field, array(
				'' => '',
				'a single day' => 'a single day',
				'multiple days with the same students' => 'multiple days with the same students',
				'multiple days with different students' => 'multiple days with different students',
			), set_value($field, $form->{$field}));
			?>
			</div>
		</div>
		
   			
		<?php $field = 'activity_involves'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('This activity will involve', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_dropdown($field, array(
				'' => '',
				'less than 20 individuals' => 'less than 20 individuals',
				'20-50 individuals' => '20-50 individuals',
				'51-100 individuals' => '51-100 individuals',
				'100+' => '100+',
			), set_value($field, $form->{$field}));
			?>
			</div>
		</div>
		

		<?php $field = 'teacher_parents'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Teacher/Parents</span>
			<div class="controls">
				<p>Teachers or parents are involved in some aspect of this program</p>
				<?php 
				foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		

		<?php $field = 'followup'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Followup</span>
			<div class="controls">
				<p>There is followup with participants after their program experience </p>
				<?php 
				foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		

		<?php $field = 'augmentation'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Augmentation</span>
			<div class="controls">
				<p>Augmented support is requested</p>
				<?php 
				foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		

		<?php $db_field = 'augmentation_targets';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>If so, the program proposed specifically targets</p>
				<?php 
				foreach(array('under-represented minorities', 'women', 'disabled individuals') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $field = 'augmentation_amount'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>I would like to augment my funding in the amount of</p>
				<div class="input-prepend">
					<span class="add-on">$</span>
					<?php echo form_input(array(
						'name' => $field, 
						'id' => $field,
						'value' => set_value($field, $form->{$field}),
						'class' => 'span1',
					));	?>
				</div>
				<p>(not to exceed $5,000), and I shall include an extra page describing in detail why additional funds are necessary to assure the success of this program.</p>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Pre-College Only</legend>
		<?php $db_field = 'pre_college_support';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Funds requested to support</span>
			<div class="controls">
				<?php 
				foreach(array('development of curriculum resources', 'implementation of curriculum resources', 'testing of curriculum resources', 'other activity') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $field = 'pre_college_support_other'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
			<p>If other, please specify</p>
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
			));	?>
			</div>
		</div>
		
		
		<?php $field = 'pre_college_assoc'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Association</span>
			<div class="controls">
				<p>This project is associated with one or more pre-college institutions</p>
				<?php 
				foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
		
		
		<?php $db_field = 'pre_college_occur';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">This project will occur</span>
			<div class="controls">
				<?php 
				foreach(array('in the classroom', 'outside the classroom') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Public Outreach Only</legend>
		<?php $db_field = 'public_outreach_support';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Funds requested to support</span>
			<div class="controls">
				<?php 
				foreach(array('conferences or workshops', 'non-technical courses', 'publications', 'science fairs', 'lecture series', 'radio and television programs', 'other activity') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>


		<?php $field = 'public_outreach_support_other'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
			<p>If other, please specify</p>
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
			));	?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Teacher Training Only</legend>
		<?php $db_field = 'teacher_support';
		$field = $db_field . '[]'; ?>
		<div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<span class="control-label">Funds requested to support</span>
			<div class="controls">
				<?php 
				foreach(array('teacher training', 'resource development', 'conference or workshop', 'other activity') as $value)
					echo '<label class="checkbox">' . form_checkbox($field, $value, set_checkbox($field, $value, in_array($value, (array) $form->{$db_field}))) . ' ' . $value . '</label>';
				?>
			</div>
		</div>


		<?php $field = 'teacher_support_other'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
			<p>If other, please specify</p>
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
			));	?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Collaborative Effort</legend>
		<?php $field = 'collab_effort'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
				<p>This is a collaborative effort between institutions</p>
				<?php 
				foreach(array('Yes', 'No') as $value)
					echo '<label class="radio">' . form_radio($field, $value, set_radio($field, $value, $value == $form->{$field})) . ' ' . $value . '</label>';
				?>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Proposal Details</legend>
		<?php $field = 'proposal_title'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Proposal Title', $field, array('class' => 'control-label'));	?>
			<div class="controls">
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->{$field}),
				'class' => 'span5',
			));	?>
			</div>
		</div>


		<?php $field = 'budget_requested'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Budget Request', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">$</span>
					<?php echo form_input(array(
						'name' => $field, 
						'id' => $field,
						'value' => set_value($field, $form->{$field}),
						'class' => 'span2',
					));	?>
				</div>
			</div>
		</div>


		<?php $field = 'proposal'; ?><div class="control-group <?php echo isset($proposal_error) && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Proposal', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<?php if(!empty($form->{$field})) : ?>
					<p class="uploaded-file"><?php echo anchor('program/download/' . $form->app_id . '/' . $field, $form->{$field}); ?>
					<label class="checkbox inline"><?php echo form_checkbox($field . '_remove', 1); ?> <a class="btn btn-mini remove-file">Remove</a></label></p>
				<?php endif; ?>
				<div>
					<?php echo form_upload(array(
						'name' => $field, 
						'id' => $field,
					));	?>
					<p class="muted"><small>PDF format only</small></p>
				</div>
			</div>
		</div>


		<?php $field = 'certification'; ?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<?php echo form_label('Certification', $field, array('class' => 'control-label'));	?>
			<div class="controls">
				<p>Please type your full name in this box if the affiliate representative on your campus reviewed and approved this proposal for submission:</p>
				<?php echo form_input(array(
					'name' => $field, 
					'id' => $field,
					'value' => set_value($field, $form->{$field}),
				));	?>
				<p class="muted"><small>Please enter your full name.</small></p>
			</div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input type="submit" name="submit" id="submit" value="Submit Application" class="btn btn-primary"/>
		<input type="submit" name="save" id="save" value="Save for Later" class="btn"/>
	</div>
<?php echo form_close();?>
