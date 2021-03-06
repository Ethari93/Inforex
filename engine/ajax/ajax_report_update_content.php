<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */

class Ajax_report_update_content extends CPageCorpus {

    function __construct(){
        parent::__construct();
        $this->anyCorpusRole[] = CORPUS_ROLE_EDIT_DOCUMENTS;
    }

    function customPermissionRule($user = null, $corpus = null){
        global $user, $corpus;
        $report = array(intval($_POST['report_id']));
        return hasAccessToReport($user, $report, $corpus);
    }

	/**
	 * Generate AJAX output.
	 */
	function execute(){
		global $user, $corpus;
	
		$report_id = intval($_POST['report_id']);
		$content = stripslashes(strval($_POST['content']));
		
		if (!intval($corpus['id'])){
			throw new Exception("Brakuje identyfikatora korpusu!");
		}

		if (!intval($user['user_id'])){
			throw new Exception("Brakuje identyfikatora użytkownika!");
		}
				
		$report = new TableReport($report_id);
		$content_before  = $report->content;
		$report->content = $content;
		$report->save();
		
		$df = new DiffFormatter();
		$diff = $df->diff($content_before, $report->content, true);
		if ( trim($diff) != "" ){
			$deflated = gzdeflate($diff);
			$data = array("datetime"=>date("Y-m-d H:i:s"), "user_id"=>$user['user_id'] , "report_id"=>$report->id, "diff"=>$deflated);		
			$this->getDb()->insert("reports_diffs", $data);
		}
				
		return;
	}
	
}
