<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */

function hasUserSystemRole($user, $anyRole){
    /** Just in case to avoid loop for permission checked for cached data  */
    if (in_array(ROLE_SYSTEM_USER_PUBLIC, $anyRole)){
        return true;
    } else {
        $userRoles = array_keys(is_array($user['role']) ? $user['role'] : array(ROLE_SYSTEM_USER_PUBLIC=>""));
        return count(array_intersect($userRoles, $anyRole)) > 0;
    }
}

function hasUserCorpusRole($user, $corpus, $anyRole){
    if ( in_array(CORPUS_ROLE_IS_PUBLIC, $anyRole) && $corpus['public'] ){
        return true;
    } else {
        $userRoles = $corpus['role'][$user['user_id']];
        $userRoles = array_keys(is_array($userRoles) ? $userRoles : array(ROLE_SYSTEM_USER_PUBLIC => ""));
        return count(array_intersect($userRoles, $anyRole)) > 0;
    }
}

function hasUserPerspectiveAccess($user_id, $corpus_id, $anyPerspective){
    return DBReportPerspective::userHasPerspectiveAccess($user_id, $corpus_id, $anyPerspective);
}

/**
 * Sprawdza, czy aktualnie zalogowany użytkownik posiada wskazaną rolę.
 * @param $role - nazwa roli,
 * @return true - jeżeli użytkownik posiada rolę
 */
function hasRole($role){
	global $user;
	return isset($user['role'][$role]);
}

/**
 * Sprawdza, czy aktualnie zalogowany użytkownik posiada wskazany poziom dostepu do bieżącego korpusu.
 * @param $role - nazwa roli,
 * @return true - jeżeli użytkownik posiada rolę
 */
function hasCorpusRole($role){
	global $corpus, $user;
	return isset($corpus['role'][$user['user_id']][$role]) || isCorpusOwner();
}

/**
 * Sprawdza, czy aktualnie zalogowany użytkownik jest właścicielem aktywnego korpusu.
 * @return true - jeżeli użytkownik jest właścicielem
 */
function isCorpusOwner(){
	global $corpus, $user;
	return $user['user_id'] == $corpus['user_id'];
}

/**
 * Sprawdza, czy dany użytkownik ma dostęp do wskazanego dokumentu.
 * Jeżeli nie ma dostępu, to zostanie zwrócony komunikat błędu, a wpp wartość false.
 */
function hasAccessToReport($user, $report, $corpus){
	/* Jeżeli korpus nie jest publiczny, to następuje sprawdzenie dostępu */
	if ( !$corpus['public'] && !hasRole("admin") && !isCorpusOwner() ){
		
		if ( !hasCorpusRole("read") ){
			return "Brak dostępu do korpusu <small>(brak roli <code>read</code>)</small>.";			
		}
			
		/* Sprawdź, czy użytkownik ma ograniczony dostęp */
		if ( hasCorpusRole("read_limited") ){
			$c = $db->fetch_one(
					"SELECT COUNT(*) FROM reports_limited_access WHERE user_id = ? AND report_id = ?",
					array($user['user_id'], $report['id']));
			if ( $c != 1 ){
				return "Masz ograniczony dostęp do korpusu.";			
			} 
		}			
	}	
	
	return true;
}

function hasPerspectiveAccess($perspective_name){
	global $corpus, $user;
	$perspectives = DBReportPerspective::get_corpus_perspectives($corpus['id'], $user);
	$allowed_names = array();
	foreach($perspectives as $per){
		$allowed_names[] = $per->id;
	}
	return in_array($perspective_name, $allowed_names);
}

?>
