<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */

class CclFactory{
	
	/**
	 * $report --- tablica asocjacyjna z atrybutami dokumentu (jak z tabeli reports)
	 * $tokens --- tablica asocjacyjna z wartościami 'from', 'to' i 'eos'
	 * $tags --- 
	 * function creates ccl document using 'eos' token attributes to match end of sentence
	 * see: createFromReportAndTokensSentence 
	 */	 
	function createFromReportAndTokens(&$report, &$tokens, &$tags){
		$fileName = str_pad($report['id'],8,'0',STR_PAD_LEFT);
		
		$ccl = new CclDocument();
		$ccl->setFileName($fileName);
		$ccl->setSubcorpus(preg_replace("/[^\p{L}|\p{N}]+/u","_",$report['name']));
		$ccl->setReport($report);
		
		$chunkList = explode('</chunk>', $report['content']);
		//might be problem with documents not splitted by chunks
		if (count($chunkList)>1)
			array_pop($chunkList);
		$from = 0;
		$to = 0;
		$pattern = '/<chunk type="([\w|\d]+)">/';
		
		foreach ($chunkList as $parts){	
			$chunk = str_replace("<"," <",$parts);
			$chunk = str_replace(">","> ",$chunk);
			preg_match_all($pattern, $chunk, $matches);
			$type = "p";
			if (is_array($matches) && array_key_exists(1, $matches))
				$type = $matches[1][0];					
			$tmpStr = trim(preg_replace("/\s\s+/"," ",custom_html_entity_decode(strip_tags($chunk))));
			$tmpStr2 = preg_replace("/\n+|\r+|\s+/","",$tmpStr);
			$to = $from + mb_strlen($tmpStr2)-1;
			$chunks[]=array(
				"notags" => $tmpStr,
				"nospace" => $tmpStr2,
				"from" => $from,
				"to" => $to,
				"type" => $type
			);
			$from = $to+1;		
		}	
		
		$htmlStr = new HtmlStr2(strip_tags($report['content']), false);
			
		// Podziel tokeny miedzy chunkami
		$tokenIndex = 0;
		$sentenceIndex = 1;
		$chunkIndex = 1;
		foreach ($chunks as $chunk){	
			$c = new CclChunk();
			$c->setId("ch" . $chunkIndex);
			$c->setType($chunk["type"]);
			$chunkIndex++;
			$s = new CclSentence();		
			$s->setId("sent" . $sentenceIndex);
			$sentenceIndex++;
			
			while ( $tokenIndex < count($tokens) && (int)$tokens[$tokenIndex]["to"] <= (int)$chunk["to"] ) {
				$token = $tokens[$tokenIndex];
				$orth = $htmlStr->getText($token['from'], $token['to']);
				$orth = custom_html_entity_decode($orth);
				if ( preg_match('/\s/',$orth) ){
					break; // TEMPORARY- change after
//					throw new Exception("Biały znak w formie tekstowej tokenu '$orth'");
				}
				
				$ns = !$htmlStr->isSpaceAfter($token['to']);
				
				$t = new CclToken();
				$t->setOrth($orth);
				$t->setNs($ns);
				$t->setId($tokenIndex);
				$t->setFrom($token['from']);
				$t->setTo($token['to']);
				
				if ( isset($tags[$token['token_id']]) and is_array($tags[$token['token_id']])){
					foreach ($tags[$token['token_id']] as $tag){
						$l = new CclLexeme();
						$l->setBase( isset($tag['base']) ? $tag['base'] : $tag['base_text'] );
						$l->setCtag($tag['ctag']);
						$l->setDisamb($tag['disamb']);
						$t->addLexeme($l);
					}
				}
				
				$s->addToken($t);
				$ccl->addToken($t);
				if ( $token['eos'] ){
					$c->addSentence($s);
					$s = new CclSentence();
					$s->setId("sent" . $sentenceIndex);
					$sentenceIndex++;
				}
				$tokenIndex++;
			}
			if (count($tokens)){
				if (!$token['eos'] )
					$c->addSentence($s);
				else 
					$sentenceIndex--;
			}
			$ccl->addChunk($c);
		}			
		
		return $ccl;
	}
	

	function setAnnotationLemmas(&$ccl, &$annotation_lemmas){
		if (empty($annotation_lemmas)){
			return false;
		}
		
		foreach($annotation_lemmas as $lemma){
			$ccl->setAnnotationLemma($lemma);
		}
	}

    function setAnnotationProperties(&$ccl, &$annotation_properties){
        if (empty($annotation_properties)){
        	return false;
		}

        foreach($annotation_properties as $property){
            $ccl->setAnnotationProperty($property);
        }
    }

	/**
	 * 
	 */	
	function setAnnotationsAndRelations(&$ccl, &$annotations, &$relations){
		if (empty($annotations)) return false;
		$annotationsById = array();
		$continuousAnnotationIds = array();
		$continuousAnnotations = array();
		$continuousRelations = array();
		$normalRelations = array();
		foreach ($relations as &$relation){
			if ($relation['relation_type_id']==1){
				$continuousAnnotationIds[] = $relation['source_id'];
				$continuousAnnotationIds[] = $relation['target_id'];
				$continuousRelations[] = &$relation;
			} else {
				$normalRelations[] = &$relation;
			}
		}
		
		foreach ($annotations as &$annotation){
			if ( !in_array($annotation['id'], $continuousAnnotationIds)){
				$ccl->setAnnotation($annotation);
			} else {
				$continuousAnnotations[$annotation['id']] =& $annotation;
			}
			$annotationsById[$annotation['id']]=&$annotation;	
		}
		
		foreach ($continuousRelations as &$cRelation){
			$source_id = $cRelation['source_id'];
			$target_id = $cRelation['target_id'];
			if (array_key_exists($source_id, $annotationsById) && 
				array_key_exists($target_id, $annotationsById)){
				$ccl->setContinuousAnnotation2(
					$continuousAnnotations[$source_id],
					$continuousAnnotations[$target_id]);
			} else if (array_key_exists($source_id, $annotationsById) ||
				array_key_exists($target_id, $annotationsById)) {
				$e = new CclError();
				$e->setClassName("CclFactory");
				$e->setFunctionName("setAnnotationsAndRelations");
				$e->addObject("relation", $cRelation);
				$e->addComment("008 no source or target annotation in a continuous relation");
				$ccl->addError($e);					
			}
		}
		
		foreach ($normalRelations as &$nRelation){
			$source_id = $nRelation['source_id'];
			$target_id = $nRelation['target_id'];
			if (array_key_exists($source_id, $annotationsById) && 
				array_key_exists($target_id, $annotationsById)){			
				$ccl->setRelation(
					$annotationsById[$nRelation['source_id']],
					$annotationsById[$nRelation['target_id']],
					$nRelation);
			} else {
				$e = new CclError();
				$e->setClassName("CclFactory");
				$e->setFunctionName("setAnnotationsAndRelations");
				$e->addObject("relation", $nRelation);
				$e->addComment("009 no source or target annotation in a normal relation");
				$ccl->addError($e);						
			}
		}
		return true;
	}
	
}
