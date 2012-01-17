<?php
class Ajax_sens_edit_add_sens extends CPage {
	function execute(){
		global $db, $mdb2;
		$name = $_POST['sensname'];
		$num = $_POST['sensnum'];
		$id = $_POST['sensid'];
		$name_num = $name . '-' . $num;
		
		$sql = " SELECT * FROM annotation_types_attributes_enum WHERE value=? ";
		
		$result = $db->fetch_one($sql, array($name_num));
		
		if(count($result)){
			$error_msg = 'Sens ' . $name_num . ' alredy exist';
			echo json_encode(array("error"=>$error_msg));
			return;
		}

		$sql = "INSERT INTO annotation_types_attributes_enum (annotation_type_attribute_id, value, description) VALUES (?, ?,' ')";
		$db->execute($sql, array($id,$name_num));
		echo json_encode(array("success" => 1));
	}	
}
?>