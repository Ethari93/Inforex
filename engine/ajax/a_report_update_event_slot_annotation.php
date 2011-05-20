<?php
/**
metoda dodajaca anotacje do slotu zdarzenia 
 * 
 */
class Ajax_report_update_event_slot_annotation extends CPage {
	
	function checkPermission(){
		if (hasRole('admin') || hasCorpusRole('annotate') || isCorpusOwner())
			return true;
		else
			return "Brak prawa do dodawania anotacji <small>[checkPermission]</small>.";
	}
	
	function execute(){
		global $mdb2, $user;

		if (!intval($user['user_id'])){
			echo json_encode(array("error"=>"Brak identyfikatora użytkownika"));
			return;
		}

		$slot_id = intval($_POST['slot_id']);
		$annotation_id = intval($_POST['annotation_id']);
		
		$sql = "UPDATE reports_events_slots " .
				"SET report_annotation_id={$annotation_id}, user_update_id={$user['user_id']}, update_time=now()" .
				"WHERE report_event_slot_id={$slot_id}";

		db_execute($sql);
 		echo json_encode(array("success"=>1));
	}
	
}
?>