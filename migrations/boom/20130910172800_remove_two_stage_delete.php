<?php

class Migration_Boom_20130910172800 extends Minion_Migration_Base
{
	public function up(Kohana_Database $db)
	{
		$db->query(null, "alter table assets drop deleted");	}

	public function down(Kohana_Database $db)
	{
	}
}