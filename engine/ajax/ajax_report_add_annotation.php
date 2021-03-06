<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
class Ajax_report_add_annotation extends CPageCorpus {
	
	/**
	 * ToDo: trzeba sprawdzić atrybuty anotacji w zależności od dostępnego trybu pracy.
	 */

    function __construct(){
        parent::__construct();
        $this->anyCorpusRole[] = CORPUS_ROLE_ANNOTATE_AGREEMENT;
        $this->anyCorpusRole[] = CORPUS_ROLE_ANNOTATE;
    }
	
	function execute(){
		global $user, $db;

		if (!intval($user['user_id'])){
			throw new Exception("Brak identyfikatora użytkownika");
		}

		$type = strval($_POST['type']);
		$from = intval($_POST['from']);
		$to = intval($_POST['to']);
		$text = stripslashes(strval($_POST['text']));
		$report_id = intval($_POST['report_id']);
		$context = strval($_POST['context']);
		$stage = strval($_POST['stage']);
		$error = null;
		$annotation_type_id = strval($_POST['annotation_type_id']);

		$row = $db->fetch("SELECT r.content, f.format" .
				" FROM reports r" .
				" JOIN reports_formats f ON (r.format_id=f.id)" .
				" WHERE r.id=?", array($report_id));

		$content = $row['content'];
		$content = normalize_content($content);
		if ( $row['format'] == 'plain' ){
			$content = htmlspecialchars($content);
		}
		
		$html = new HtmlStr2($content, true);
		$text_revalidate = $html->getText($from, $to);
		$html_revalidate = custom_html_entity_decode($text_revalidate);
		
		if ( preg_replace("/\n+|\r+|\s+/","",$text) != preg_replace("/\n+|\r+|\s+/","", $html_revalidate) ){
			$error = "Synchronizacja z bazą się nie powiodła &mdash; wystąpiła rozbieżność anotacji. <br/><br/>" .
					"Typ: <b>$type</b><br/>" .
					"Pozycja: [<b>$from,$to</b>]<br/>" .
					"Przesłana jednostka: <b>'$text'</b><br/>" .
					"Jednostka z bazy: <b>'$html_revalidate'</b>";
				
			throw new Exception($error);
		}

		/**
		 * ToDo Przed dodaniem trzeba sprawdzić, czy użytkownik może dodawać anotacje określonego typu.
		 * Np. anotacje stage=final może dodać użytkownik z rolą annotator
		 *     anotacje stage=new może dodać użytkownik z rolą annotator_agreement
		 */
		
		$attributes = array(
			'report_id'=>$report_id, 
			'type_id'=>$annotation_type_id,
			'from'=>$from, 
			'to'=>$to, 
			'text'=>$text, 
			'user_id'=>$user['user_id'],
			'source'=>'user',
			'stage'=>'final'
		);

		if ( in_array($stage, array("new","final","discarded","agreement")) ){
			$attributes['stage'] = $stage;
		}

		$db->insert('reports_annotations_optimized', $attributes);
        $annotation_id = $db->last_id();

		return array("success"=>1, "from"=>$from, "to"=>$to, "text"=>$text, "annotation_id"=>$annotation_id);
	}
}
