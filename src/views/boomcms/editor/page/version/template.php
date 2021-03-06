<form id="b-page-version-template" name="pageversion-template">
    <h1><?= trans('boomcms::settings.template.heading') ?></h1>

	<div class="b-template">
        <p><?= trans('boomcms::settings.template.about') ?></p>

		<label for="template_id"><?= trans('boomcms::settings.template.template') ?></label>
		<select id='template' name='template_id'>
            <?php if (!$current->getId()): ?>
                <option value="" selected><?= trans('boomcms::settings.template.default') ?></option>
            <?php endif ?>

			<?php foreach ($templates as $t): ?>
				<option value='<?= $t->getId() ?>' data-description="<?= $t->getDescription() ?>" data-count='<?= $countPages(['template' => $t]) ?>'<?php if ($t->is($current)): ?> selected='selected'<?php endif ?>><?= $t->getTheme() ?> - <?= $t->getName() ?></option>
			<?php endforeach ?>
		</select>

		<div id='description'><strong><?= trans('boomcms::settings.template.description') ?></strong><p></p></div>
		<div id='count'><strong><?= trans('boomcms::settings.template.count') ?></strong><p></p></div>
        
        <?= $button('refresh', 'reset', ['class' => 'b-template-cancel b-button-withtext']) ?>
        <?= $button('save', 'save', ['class' => 'b-template-save b-button-withtext']) ?>
    </div>
</form>
