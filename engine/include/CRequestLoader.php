<?
/**
 * Loads data according to the state of $_REQUEST variables
 */
class RequestLoader{

	/********************************************************************
	 * Determine and load corpus context according to following attributes:
	 * - annotation_id,
	 * - id or report_id,
	 * - corpus,
	 * - relation_id
	 */
	static function loadCorpus(){
		$annotation_id = isset($_REQUEST['annotation_id']) ? intval($_REQUEST['annotation_id']) : 0; 
		$report_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : (isset($_REQUEST['report_id']) ? intval($_REQUEST['report_id']) : 0); 
		$corpus_id = isset($_GET['corpus']) ? intval($_GET['corpus']) : 0; 
		$relation_id = isset($_REQUEST['relation_id']) ? intval($_REQUEST['relation_id']) : 0; 
		
		// Obejście na potrzeby żądań, gdzie nie jest przesyłany id korpusu tylko raportu lub anotacji
		if ($corpus_id==0 && $report_id==0 && $annotation_id)
			$report_id = db_fetch_one("SELECT report_id FROM reports_annotations WHERE id = ?", $annotation_id);
		if ($corpus_id==0 && $report_id>0)
			$corpus_id = db_fetch_one("SELECT corpora FROM reports WHERE id = ?", $report_id);
		if ($relation_id>0)	
			$corpus_id = db_fetch_one("SELECT corpora FROM relations r JOIN reports_annotations a ON (r.source_id = a.id) JOIN reports re ON (a.report_id = re.id) WHERE r.id = ?", $relation_id);
		
		$corpus = db_fetch("SELECT * FROM corpora WHERE id=".intval($corpus_id));
		// Pobierz prawa dostępu do korpusu dla użytkowników
		if ($corpus){
			$roles = db_fetch_rows("SELECT *" .
					" FROM users_corpus_roles ur" .
					" WHERE ur.corpus_id = ?", array($corpus['id']));
			$corpus['role'] = array();
			foreach ($roles as $role)
				$corpus['role'][$role['user_id']][$role['role']] = 1;
		}
		
		return $corpus;		
	}	

}
?>