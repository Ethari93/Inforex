<?php
class Ajax_sens_edit_add_word extends CPage {
	function execute(){
		global $db, $mdb2;
		$name = $_POST['wordname'];
		$wsd_name = "wsd_" . $name;
		
		$sql = " SELECT * FROM annotation_types WHERE name=? ";
		
		$result = $db->fetch_one($sql, array($wsd_name));
		
		if(count($result)){
			$error_msg = 'Word ' . $name . ' alredy exist';
			echo json_encode(array("error"=>$error_msg));
			return;
		}
		$sql = "INSERT INTO annotation_types (name, group_id, annotation_subset_id) VALUES (?, 2, 21)";
		$db->execute($sql, array($wsd_name));
		
		$sql = "INSERT INTO annotation_types_attributes (annotation_type, name, type) VALUES (?, 'sense', 'radio')";
		$db->execute($sql, array($wsd_name));		
		$rows_id = $mdb2->lastInsertID();
		echo json_encode(array("success" => 1, "rows_id" => $rows_id));
	}	
}
?>