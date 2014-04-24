<form class="b-form-settings narrow">
	<?= Form::hidden('csrf', Security::token()) ?>

	<div>
		<p>
			<label for="b-page-visible"><?=__('Visible')?></label>
			<?= Form::select('visible', array(1 => 'Yes', 0 => 'No'), (int) $page->visible, array('id' => 'b-page-visible')) ?>
		</p>
		<p>
			<label for="visible-from"><?=__('Visible from')?></label>
			<?= Form::input('visible_from', date("d F Y H:i", $page->visible_from), array('id' => 'visible-from', 'class' => 'boom-datepicker')) ?>
		</p>
		<p>
			<label for="toggle-visible"><?=__('Visible until')?></label>
			<?= Form::checkbox('toggle_visible_to', 1, $page->visible_to != 0, array('id' => 'toggle-visible')) ?>
			<?= Form::input('visible_to', ($page->visible_to)? date("d F Y H:i", $page->visible_to) : date("d F Y H:i", time()), array('id' => 'visible-to', 'class' => 'boom-datepicker')) ?>
		</p>
	</div>
</form>
