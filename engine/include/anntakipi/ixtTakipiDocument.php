<?php
/* 
 * Created on 2010-01-13
 * Michał Marcińczuk <marcinczuk@gmail.com> [czuk.eu]
 */
 
require_once("ixtTakipiReader.php"); 
 
class TakipiDocument{
		
	var $sentences = array();
		
	function __construct(){
	}	

	/**
	 * Retuns an array with references to document tokes.
	 */
	function getTokens(){
		$tokens = array();
		foreach ($this->sentences as $sentence)
			foreach ($sentence->tokens as &$token)
				$tokens[] = $token;
		return $tokens;
	}
	
	function addAnnotation($type, $from, $to){
		$i = 0;
		foreach ($this->sentences as &$sentence){
			$a = $i;
			$b = $a + count($sentence->tokens) - 1;
			if ( $from >= $a && $to >= $a && $from <= $b && $to <= $b){
				$sentence->addAnnotation($type, $from-$a, $to-$a);
				return true;
			}
			$i = $b + 1; 
		}
		throw new Exception("Annotation `$type` was not added, ann=($from, $to), sent=($a, $b)");
	}	
}
?>
