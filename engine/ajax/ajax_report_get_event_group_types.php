<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
class Ajax_report_get_event_group_types extends CPageCorpus {

	function execute(){
		global $user;
		$group_id = intval($_POST['group_id']);

		$sql = "SELECT event_types.event_type_id, event_types.name " .
				"FROM event_groups " .
				"JOIN event_types " .
					"ON (event_groups.event_group_id={$group_id} AND event_groups.event_group_id=event_types.event_group_id)";
		$result = $this->getDb()->fetch_rows($sql);
		return $result;
	}
	
}
