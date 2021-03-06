<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
class Ajax_relation_type_update extends CPageAdministration {

    function __construct(){
        parent::__construct();
        $this->anySystemRole[] = ROLE_SYSTEM_EDITOR_SCHEMA_RELATIONS;
    }
	
	function execute(){
		global $user;

		if (!intval($user['user_id'])){
			throw new Exception("Brak identyfikatora użytkownika");
		}
		$name_str = $_POST['name_str'];
		$desc_str = $_POST['desc_str'];
		$element_id = intval($_POST['element_id']);
		
		$element_type = $_POST['element_type'];
		
		if ($element_type=="relation_type")
			$sql = "UPDATE relation_types SET name=\"$name_str\", description=\"$desc_str\" WHERE id=$element_id";
		$this->getDb()->execute($sql);
		return;
	}
	
}
