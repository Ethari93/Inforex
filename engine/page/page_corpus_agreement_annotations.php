<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
class Page_corpus_agreement_annotations extends CPageCorpus {

	public function __construct(){
		parent::__construct();
		$this->anyCorpusRole[] = CORPUS_ROLE_AGREEMENT_CHECK;
        $this->includeJs("js/c_widget_annotation_type_tree.js");
    }

	function execute(){

		global $db, $user, $corpus;
		
		/* Variable declaration */
		$corpus_id = $corpus['id'];
		$annotators = array();
		$annotation_set_a = array();
		$annotation_set_b = array();
		$agreement = array();
		$pcs = array();
		$comparision_mode = strval($_GET['comparision_mode']);
		$comparision_modes = array();
		$comparision_modes["borders"] = "borders";
		$comparision_modes["categories"] = "borders and categories";
		$comparision_modes["borders_lemmas"] = "borders and lemmas";
		$comparision_modes["lemmas"] = "borders, categories and lemmas";
        $comparision_modes["distinct_types"] = "distinct annotation types";
		$subcorpora = DbCorpus::getCorpusSubcorpora($corpus_id);
		$subcorpus_ids = $_GET['subcorpus_ids'];
		$corpus_flags = DbCorporaFlag::getCorpusFlags($corpus_id);
		$flags = DbCorporaFlag::getFlags();
		$corpus_flag_id = intval($_GET['corpus_flag_id']);
		$flag_id = intval($_GET['flag_id']);
		$flag = array();

		$this->setup_annotation_type_tree($corpus_id);

        $annotation_types = CookieManager::getAnnotationTypeTreeAnnotationTypes($corpus_id);

		if ( !is_array($subcorpus_ids) ){
			$subcorpus_ids = array();
		}
		
		if ( $corpus_flag_id !== 0 && $flag_id !== 0 ){
			$flag = array($corpus_flag_id => $flag_id);
		}
		
		if ( !isset($comparision_modes[$comparision_mode]) ){
			$comparision_mode = "borders";
		}
		
		$annotators = DbAnnotation::getUserAnnotationCount($corpus_id, $subcorpus_ids, null, null, $annotation_types, $flag, "agreement");

		// TODO: do ujednolicenia z setupUserSelectionAB
        $annotator_a_id = strval($_GET['annotator_a_id']);
        $annotator_b_id = strval($_GET['annotator_b_id']);
		$annotation_set_final_count = DbAnnotation::getAnnotationCount(null, $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "final"); 
		$annotation_set_final_doc_count = DbAnnotation::getAnnotationDocCount(null, $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "final");
		
		if ( $annotator_a_id == "final" ){
			$annotation_set_a = DbAnnotation::getUserAnnotations(null, $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "final"); 
		}
		else if ( intval($annotator_a_id) > 0 ) {
			$annotation_set_a = DbAnnotation::getUserAnnotations(intval($annotator_a_id), $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "agreement");
		}
		
		if ( $annotator_b_id == "final" ){
			$annotation_set_b = DbAnnotation::getUserAnnotations(null, $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "final");
		}
		else if ( intval($annotator_b_id) > 0 ) {
			$annotation_set_b = DbAnnotation::getUserAnnotations($annotator_b_id, $corpus_id, $subcorpus_ids, null, $annotation_types, $flag, "agreement");
		}
		
		if ( $annotator_a_id && $annotator_b_id ){

		    if($comparision_mode !== "distinct_types"){
                $annotation_types = array();
                foreach ($annotation_set_a as $an){
                    $annotation_types[$an["annotation_name"]] = 1;
                }

                foreach ($annotation_set_b as $an){
                    $annotation_types[$an["annotation_name"]] = 1;
                }

                foreach ( array_keys($annotation_types) as $annotation_name ){
                    $agreement = compare($annotation_set_a, $annotation_set_b, "key_generator_${comparision_mode}", $annotation_name);
                    $pcs_value = pcs2(count($agreement['a_and_b']), count($agreement['only_a']), count($agreement['only_b']));
                    $pcs[$annotation_name] = array("only_a"=>count($agreement['only_a']), "only_b"=>count($agreement['only_b']), "a_and_b"=>count($agreement['a_and_b']), "pcs"=>$pcs_value);
                }

                $agreement = compare($annotation_set_a, $annotation_set_b, "key_generator_${comparision_mode}");
            }
            else{
                $agreement = compareDistinctTypes($annotation_set_a, $annotation_set_b, "key_generator_${comparision_mode}");
            }
            ksort($agreement['annotations']);
            $pcs_value = pcs2(count($agreement['a_and_b']), count($agreement['only_a']), count($agreement['only_b']));
            $pcs["all"] = array("only_a"=>count($agreement['only_a']), "only_b"=>count($agreement['only_b']), "a_and_b"=>count($agreement['a_and_b']), "pcs"=>$pcs_value);
        }

		/* Assign variables to the template */
		//$this->set("annotation_sets", $annotation_sets);
		$this->set("annotation_set_final_count", intval($annotation_set_final_count));
		$this->set("annotation_set_final_doc_count", intval($annotation_set_final_doc_count));
		//$this->set("annotation_set_id", $annotation_set_id);
		$this->set("annotators", $annotators);
		$this->set("annotator_a_id", $annotator_a_id);
		$this->set("annotator_b_id", $annotator_b_id);
		$this->set("agreement", $agreement);
		$this->set("pcs", $pcs);
		$this->set("comparision_mode", $comparision_mode);
		$this->set("comparision_modes", $comparision_modes);
		$this->set("subcorpora", $subcorpora);
		$this->set("subcorpus_ids", $subcorpus_ids);
		$this->set("corpus_flags", $corpus_flags);
		$this->set("flags", $flags);
		$this->set("corpus_flag_id", $corpus_flag_id);
		$this->set("flag_id", $flag_id);
	}

	/**
	 * Ustaw strukturę dostępnych typów anotacji.
	 * @param unknown $corpus_id
	 */
	private function setup_annotation_type_tree($corpus_id){
		$annotations = DbAnnotation::getAnnotationStructureByCorpora($corpus_id);
		$this->set('annotation_types',$annotations);
	}
	
}

/** TODO do przeniesienia do osobnego pliku */

/**
 * 
 * @param unknown $name
 * @param unknown $ans1
 * @param unknown $ans2
 * @param unknown $key_generator
 * @param string $type
 * @param string $annotation_name_filter Jeżeli ustawiony, to filtruje po nazwach anotacji.
 * @return unknown[]|number[]|string[]
 */
function compare($ans1, $ans2, $key_generator, $annotation_name_filter=null){
	$annotations = array();
	//$annotations_border = array();
	$copy_ans1 = array();
	//$copy_ans1_border = array();
	$copy_ans2 = array();
	//$copy_ans2_border = array();'
	
	foreach ($ans1 as $as){
		if ( $annotation_name_filter != null && $as['annotation_name'] != $annotation_name_filter ){
			continue;
		}
		$key = $key_generator($as);
		//$key_border = key_generator_borders($as);
		if ( isset($ans1[$key]) ){
			echo "Warning: duplicated annotation in DB1 $key with $key_generator\n";
		}
		else{
			$copy_ans1[$key] = $as;
			//$copy_ans1_border[$key_border][] = $key;
			//$annotations_border[$key_border] = 1;
		}
		$annotations[$key] = $as;
	}

	foreach ($ans2 as $as){
		if ( $annotation_name_filter != null && $as['annotation_name'] != $annotation_name_filter ){
			continue;
		}
		$key = $key_generator($as);
		//$key_border = key_generator_borders($as);
		if ( isset($ans2[$key]) ){
			echo "Warning: duplicated annotation in DB2 $key with $key_generator\n";
		}
		else{
			$copy_ans2[$key] = $as;
			//$copy_ans2_border[$key_border][] = $key;
			//$annotations_border[$key_border] = 1;
		}
		$annotations[$key] = $as;
	}
	
	$only1 = array_diff_key($copy_ans1, $copy_ans2);
	$only2 = array_diff_key($copy_ans2, $copy_ans1);
	$both = array_intersect_key($copy_ans1, $copy_ans2);

	return array("only_a"=>$only1, "only_b"=>$only2, "a_and_b"=>$both, "annotations"=>$annotations, "annotations_a"=>$copy_ans1, "annotations_b"=>$copy_ans2);
}

function compareDistinctTypes($ans1, $ans2, $key_generator, $annotation_name_filter=null){
    $annotations_1 = array();
    $annotations_2 = array();
    $annotations = array();
    foreach($ans1 as $an1){
        if ( $annotation_name_filter != null && $an1['annotation_name'] != $annotation_name_filter ){
            continue;
        }

        $key = $key_generator($an1);
        $annotations_1[$key] = $an1;
        $annotations[$key] = $an1;
    }

    foreach($ans2 as $an2){
        if ( $annotation_name_filter != null && $an2['annotation_name'] != $annotation_name_filter ){
            continue;
        }
        $key = $key_generator($an2);
        $annotations_2[$key] = $an2;
        $annotations[$key] = $an2;
    }

    $only1 = array_diff_key($annotations_1, $annotations_2);
    $only2 = array_diff_key($annotations_2, $annotations_1);
    $both = array_intersect_key($annotations_1, $annotations_2);

    return array("only_a"=>$only1, "only_b"=>$only2, "a_and_b"=>$both, "annotations"=>$annotations, "annotations_a"=>$annotations_1, "annotations_b"=>$annotations_2);

}

function key_generator_borders($row){
	return implode(array($row['report_id'], sprintf("%08d", $row['from']), sprintf("%08d", $row['to'])), "_");
}

function key_generator_borders_lemmas($row){
	return implode(array($row['report_id'], sprintf("%08d", $row['from']), sprintf("%08d", $row['to']), $row['lemma']), "_");
}

function key_generator_categories($row){
	return implode(array($row['report_id'], sprintf("%08d", $row['from']), sprintf("%08d", $row['to']), $row['type_id']), "_");
}

function key_generator_lemmas($row){
	return implode(array($row['report_id'], sprintf("%08d", $row['from']), sprintf("%08d", $row['to']), $row['type_id'], $row['lemma']), "_");
}

function key_generator_distinct_types($row){
    return implode(array($row['report_id'], $row['type_id']), "_");
}

// ToDo: duplicated
function pcs2($both, $only1, $only2){
	if ( (2*$both + $only1 + $only2) == 0 ){
		return 0;
	}
	else{
		return $both*200.0/(2.0*$both+$only1+$only2);
	}
}
