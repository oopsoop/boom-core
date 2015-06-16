	<?= View::make('boom::header', ['title' => 'Templates']) ?>
	<?= $menu() ?>

	<div id="b-topbar" class="b-toolbar">
		<?= $menuButton() ?>
	</div>

    <div>
        <table>
            <tr>
                <th>Page title</th>
                <th>URL</th>
            </tr>
            <?php foreach ($pages as $p): ?>
                    <tr>
                        <td><?= $p->getTitle() ?></td>
                        <td><a href='<?= $p->url() ?>'><?= $p->url()->location ?></a></td>
                    </tr>
            <?php endforeach ?>
        </table>
    </div>

	<script type="text/javascript">
		//<![CDATA[
		(function ($) {
			$.boom.init();
		})(jQuery);
		//]]>
	</script>
</body>
</html>