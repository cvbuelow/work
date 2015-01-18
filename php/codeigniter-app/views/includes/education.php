<tr>
	<td>
		<?php $db_field = 'school';
		$field = $db_field . '[]';
		?><div class="control-group <?php echo form_error($field) !== '' && $show_errors ? 'error' : '' ?>">
			<div class="controls">
			<?php echo form_input(array(
				'name' => $field, 
				'id' => $field,
				'value' => set_value($field, $form->education[$row_num]->{$db_field}),
			));	?>
			</div>
		</div>
	</td>
</tr>