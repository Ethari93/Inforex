<?
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
class DbAnnotation{
	
	/**
	 * Return list of annotations. 
	 */
	static function getAnnotationByReportId($report_id,$fields=null){
		global $db;
		
		$sql = " SELECT " .
				($fields ? $fields : " * " ) .
				" FROM reports_annotations " .
				" WHERE report_id = ?";

		return $db->fetch_rows($sql, array($report_id));
	}
	
	static function getReportAnnotationsBySubsetId($report_id, $subset_id){
		global $db;
		$sql = "SELECT * FROM reports_annotations an" .
				" JOIN annotation_types at ON (an.type = at.name)" .
				" WHERE an.report_id = ? AND at.annotation_subset_id = ?";
		return $db->fetch_rows($sql, array($report_id, $subset_id));
	}
	
	/**
	 * Return list of annotations types. 
	 */
	static function getAnnotationTypes($fields=null){
		global $db;
		
		$sql = " SELECT " .
				($fields ? $fields : " * " ) .
				" FROM annotation_types ";

		return $db->fetch_rows($sql);
	}
	
	static function getAnnotationTypesByCorpora($corpus_id,$fields=null){
		global $db;
		
		$sql = " SELECT " .
				($fields ? $fields : " * " ) .		
				" FROM annotation_sets a_s " .
				" LEFT JOIN annotation_sets_corpora a_s_c ON (a_s.annotation_set_id=a_s_c.annotation_set_id) " .
				" WHERE a_s_c.corpus_id=? ";
		
		return $db->fetch_rows($sql,array($corpus_id));
	}

	static function getAnnotationTypesByGroupId($group_id){
		global $db;
	    $sql = "SELECT name FROM annotation_types WHERE group_id = ?";
		return $db->fetch_rows($sql, array($group_id));
	}
	
	static function getAnnotationTypesBySets($report_ids, $relation_ids){
		global $db;
	    $sql = "SELECT DISTINCT type, report_id " .
	            "FROM reports_annotations " .
	            "WHERE report_id IN('" . implode("','",$report_ids) . "') " .
	            "AND " .
	                "(id IN " .
	                    "(SELECT source_id " .
	                    "FROM relations " .
	                    "WHERE relation_type_id " .
	                    "IN " .
	                        "(".implode(",",$relation_ids).") ) " .
	                "OR id " .
	                "IN " .
	                    "(SELECT target_id " .
	                    "FROM relations " .
	                    "WHERE relation_type_id " .
	                    "IN " .
	                        "(".implode(",",$relation_ids).") ) )";
		return $db->fetch_rows($sql);
	}
	
	static function getAnnotationsBySets($report_ids=null, $annotation_layers=null, $annotation_names=null){
		global $db;
		// "if(ra.type like 'wsd%', 'sense', ra.type) as" wsd_* traktujemy osobno 
		$sql = "SELECT *, ra.type, raa.`value` AS `prop` " .
				" FROM reports_annotations ra" .
				" LEFT JOIN annotation_types at ON (ra.type=at.name) " .
				" LEFT JOIN reports_annotations_attributes raa ON (ra.id=raa.annotation_id) ";
		$andwhere = array();
		$orwhere = array();		
		$andwhere[] = " stage='final' ";
		if ($report_ids <> null && count($report_ids) > 0)
			$andwhere[] = "report_id IN (" . implode(",",$report_ids) . ")";
		if ($annotation_layers <> null && count($annotation_layers) > 0)
			$orwhere[] = "at.group_id IN (" . implode(",",$annotation_layers) . ")";
		if ($annotation_names <> null && count($annotation_names) > 0)
			$orwhere[] = "ra.type IN ('" . implode("','",$annotation_names) . "')";		
		if (count($andwhere) > 0)
			$sql .= " WHERE (" . implode(" AND ", $andwhere) . ") ";
		if (count($orwhere) > 0) 
			if (count($andwhere)==0)
				$sql .= " WHERE ";
			else 			
				$sql .= " AND ( " . implode(" OR ",$orwhere) . " ) ";			
		$sql .= "  GROUP BY ra.id ORDER BY `from`";	
		
		$rows = $db->fetch_rows($sql);
		
		return $rows;
				
	}
	
	static function deleteReportAnnotationsByType($report_id, $types){
		global $db;
		if (!is_array($types)) $types = array($types);
		
		$sql = "DELETE FROM reports_annotations_optimized WHERE report_id = ? ".
				" AND type IN (". implode(",", array_fill(0, count($types), "?")) .")";
				
		$params = array_merge(array($report_id), array_values($types));
		$db->execute($sql, $params);	
	}
	
	static function getSubsetsBySetAndCorpus($set_id, $corpus_id){
		global $db;
		$sql = "SELECT ansub.annotation_subset_id as id, ans.description setname, ansub.description subsetname"
		." FROM annotation_subsets ansub"
		." JOIN annotation_sets ans ON ( ansub.annotation_set_id = ans.annotation_set_id )"
		." LEFT JOIN annotation_sets_corpora ac ON ( ac.annotation_set_id = ans.annotation_set_id )"
		." WHERE ac.corpus_id = ? "
		." AND ans.annotation_set_id = ?";
		
		$rows = $db->fetch_rows($sql, array($corpus_id, $set_id));
		
		return $rows;
	}
	
	static function getAnnotationSetsWithCount($corpus_id, $subcorpus, $status){
		global $db;
		$params = array($corpus_id);
		
		$setsById = array();
		
		$sql = "SELECT DISTINCT ans.annotation_set_id AS id, ans.description AS name FROM annotation_types at ".
				"LEFT JOIN annotation_subsets ansub ON(at.annotation_subset_id = ansub.annotation_subset_id) ".
				"JOIN annotation_sets ans ON(at.group_id = ans.annotation_set_id) ".
				"LEFT JOIN annotation_sets_corpora ac ON (ac.annotation_set_id = ans.annotation_set_id) ".
				"WHERE ac.corpus_id = ?";
		
		$sets = $db->fetch_rows($sql, $params);
		
		foreach($sets as $set){
			$setsById[$set['id']] = array('name' => $set['name'], 'unique' => 0, 'count' => 0);
		}
	
		if ($subcorpus)
			$params[] = $subcorpus;
			
		if ( $status > 0 )
			$params[] = $status;
		
		$sql = "SELECT b.setname AS name, b.id, b.group, SUM( b.count ) AS count, SUM( b.unique ) AS `unique` ".
				"FROM ( ".
				
				"		SELECT a.type AS type , ans.description AS setname, at.group_id AS `group` , ".
				"		COUNT( * ) AS count, ".
				"		COUNT( DISTINCT (a.text) ) AS `unique` , ".
				"		COUNT( DISTINCT (r.id) ) AS docs, at.group_id AS id ".
				
				"		FROM annotation_sets ans ".
				"			JOIN annotation_types at ON (at.group_id = ans.annotation_set_id) ".
				"			JOIN reports_annotations a ON (a.type = at.name) ".
				"			JOIN reports r ON (r.id = a.report_id) ".
				"		WHERE r.corpora = ?".
							( $subcorpus ? " AND r.subcorpus_id = ? " : "") .
							( $status ? " AND r.status = ? " : "") .
				"		GROUP BY a.type ".
				"		ORDER BY a.type ".
				
				") AS b ".
				"GROUP BY b.group";
		
		
		$annotation_sets = $db->fetch_rows($sql, $params);
		
		foreach($annotation_sets as $set){
			$setsById[$set['id']]['unique'] = $set['unique'];
			$setsById[$set['id']]['count'] = $set['count'];
			if($setsById[$set['id']]['name'] == ''){
				$setsById[$set['id']]['inc_name'] = $set['name'];
			}
		}
		
		return $setsById;
	}
	
	static function getAnnotationSubsetsWithCount($corpus_id, $set_id, $subcorpus, $status){
		global $db;
		$params = array($corpus_id, $set_id);
		
		$subsetsById = array();
		
		$sql = "SELECT ansub.annotation_subset_id AS id, ansub.description AS name FROM annotation_types at ".
				"LEFT JOIN annotation_subsets ansub ON(at.annotation_subset_id = ansub.annotation_subset_id) ".
				"JOIN annotation_sets ans ON(at.group_id = ans.annotation_set_id) ".
				"JOIN reports_annotations a ON ( at.name = a.type ) ".
				"JOIN reports r ON ( r.id = a.report_id ) ".
				//"LEFT JOIN annotation_sets_corpora anc ON 1(anc.annotation_set_id = ans.annotation_set_id) ".
				"WHERE r.corpora = ? AND ans.annotation_set_id = ? ".
				"GROUP BY id";
				
		$subsets = $db->fetch_rows($sql, $params);
			
		foreach($subsets as $subset){
			$subsetsById[$subset['id']] = array('name' => $subset['name'], 'unique' => 0, 'count' => 0);
		}
	
		if ($subcorpus)
			$params[] = $subcorpus;
			
		if ( $status > 0 )
			$params[] = $status;
		
		$sql = "SELECT b.subname AS name, b.id, b.group, SUM( b.count ) AS count, SUM( b.unique ) AS `unique` ".
				"FROM ( ".
				"SELECT a.type AS type , ansub.description AS subname, at.group_id AS `group` , ". 
				"COUNT( * ) AS count, ". 
				"COUNT( DISTINCT (a.text) ) AS `unique` , ". 
				"COUNT( DISTINCT (r.id) ) AS docs, at.annotation_subset_id AS id ".
				"FROM reports_annotations a ".
				"JOIN reports r ON ( r.id = a.report_id ) ".
				"JOIN annotation_types at ON ( at.name = a.type ) ".
				"JOIN annotation_subsets ansub ON ( at.annotation_subset_id = ansub.annotation_subset_id ) ".
				"WHERE r.corpora = ? ".
				"AND at.group_id = ? ".
				( $subcorpus ? " AND r.subcorpus_id = ? " : "") .
				( $status ? " AND r.status = ? " : "") .
				"GROUP BY a.type ".
				"ORDER BY a.type ".
				") AS b ".
				"GROUP BY b.id";
		
		$annotation_subsets = $db->fetch_rows($sql, $params);
		
		foreach($annotation_subsets as $subset){
			$subsetsById[$subset['id']]['unique'] = $subset['unique'];
			$subsetsById[$subset['id']]['count'] = $subset['count'];
		}
		
		return $subsetsById;
	}
	
	static function getAnnotationTypesWithCount($corpus_id, $subset_id, $subcorpus, $status){
		global $db;
		$params = array($corpus_id, $subset_id);
	
		$typesById = array();
	
		$sql = "SELECT at.name AS name, at.name AS id FROM annotation_types at ".
				"LEFT JOIN annotation_subsets ansub ON(at.annotation_subset_id = ansub.annotation_subset_id) ".
				"JOIN annotation_sets ans ON(at.group_id = ans.annotation_set_id) ".
				"JOIN reports_annotations a ON ( at.name = a.type ) ".
				"JOIN reports r ON ( r.id = a.report_id ) ".
				"WHERE r.corpora = ? AND ansub.annotation_subset_id = ? ".//AND ans.annotation_set_id = ?
				"ORDER BY name";
				
		$types = $db->fetch_rows($sql, $params);

		foreach($types as $type){
			$typesById[$type['id']] = array('name' => $type['name'], 'unique' => 0, 'count' => 0, 'docs' => 0);
		}
	
		if ($subcorpus)
			$params[] = $subcorpus;
			
		if ( $status > 0 )
			$params[] = $status;
	
		$sql = "SELECT at.name AS name, at.name AS id, ".
				"COUNT( * ) AS count, ".
				"COUNT( DISTINCT (a.text) ) AS `unique` , ".
				"COUNT( DISTINCT (r.id) ) AS docs ".
				"FROM reports_annotations a ".
				"JOIN reports r ON ( r.id = a.report_id ) ".
				"JOIN annotation_types at ON ( at.name = a.type ) ".
				"JOIN annotation_subsets ansub ON ( at.annotation_subset_id = ansub.annotation_subset_id ) ".
				"WHERE r.corpora = ? ".
				//"AND at.group_id = ? ".
				"AND at.annotation_subset_id = ? ".
				( $subcorpus ? " AND r.subcorpus_id = ? " : "") .
				( $status ? " AND r.status = ? " : "") .
				"GROUP BY a.type ".
				"ORDER BY a.type ";
	
		$annotation_subsets = $db->fetch_rows($sql, $params);
	
		foreach($annotation_subsets as $type){
			$typesById[$type['id']]['unique'] = $type['unique'];
			$typesById[$type['id']]['count'] = $type['count'];
			$typesById[$type['id']]['docs'] = $type['docs'];
		}
	
		return $typesById;
	}
	
	static function getAnnotationTags($corpus_id, $annotation_type, $subcorpus, $status){
		global $db;
		$params = array($corpus_id, $annotation_type);
		
		if ($subcorpus)
			$params[] = $subcorpus;
			
		if ( $status > 0 )
			$params[] = $status;
		
		$sql = "SELECT a.text, COUNT(*) AS count ". //SELECT a.type, a.text, COUNT(*) AS count, r.title, COUNT( * ) AS count ".
				"FROM reports_annotations a ".
				"JOIN reports r ON ( r.id = a.report_id ) ".
				"JOIN annotation_types at ON ( at.name = a.type ) ".
				"JOIN annotation_subsets ansub ON ( at.annotation_subset_id = ansub.annotation_subset_id ) ".
				"WHERE r.corpora = ? ".
				"AND at.name = ? ".
				( $subcorpus ? " AND r.subcorpus_id = ? " : "") .
				( $status ? " AND r.status = ? " : "") .
				"GROUP BY a.type, a.text ".
				"ORDER BY a.type, count desc";
		
		$annotation_tags = $db->fetch_rows($sql, $params);
		
		return $annotation_tags;
	}
	
	static function getAnnotationStructureByCorpora($corpus_id){
		global $db;
	
		$sql = "SELECT ans.annotation_set_id AS set_id, ans.description AS set_name, ansub.annotation_subset_id AS subset_id, ". 
				"ansub.description AS subset_name, at.name AS type_name, at.annotation_type_id AS type_id FROM annotation_types at ".
				"JOIN annotation_subsets ansub USING(annotation_subset_id) ".
				"JOIN annotation_sets ans USING(annotation_set_id) ".
				"LEFT JOIN annotation_sets_corpora ac USING(annotation_set_id) ".
				"WHERE ac.corpus_id = ?";
	
		$annotation_types = $db->fetch_rows($sql,array($corpus_id));
		
		$annotation_sets = array();
		foreach($annotation_types as $at){
			$set_id = $at['set_id'];
			$subset_id = $at['subset_id'];
			if (!isset($annotation_sets[$set_id])){
				$annotation_sets[$set_id] = array('name' => $at['set_name']);
			}
			if (!isset($annotation_sets[$set_id][$subset_id])){
				$annotation_sets[$set_id][$subset_id] = array('name' => $at['subset_name']);
			}
			
			$annotation_sets[$set_id][$subset_id][$at['type_id']] = $at['type_name'];
		}
		
		return $annotation_sets;
	}
	
	static function getReportAnnotationsByTypes($report_id, $types){
		global $db;
		
		$sql = "SELECT rao.*, at.annotation_type_id AS atid, at.css, ral.lemma AS lemma FROM `reports_annotations_optimized` rao ".
				"JOIN `annotation_types` at ON(rao.type = at.name) ".
				"LEFT JOIN  `reports_annotations_lemma` ral ON ( rao.id = ral.report_annotation_id ) ".
				"WHERE rao.report_id = ".$report_id." AND at.annotation_type_id IN(".implode(",",$types).") ".
				" ORDER BY `from` ASC, `to` ASC";
		
		//$typesList = implode(",",$types);
		
		$annotations = $db->fetch_rows($sql);//, array($report_id, $typesList));
		//echo $sql;die;
		return $annotations;
	}
	
	static function getIdByName($name){
		global $db;
		$sql = "SELECT annotation_type_id FROM annotation_types WHERE name='?'";
		return $db->fetch_one($sql,$name);
	}
}

?>
