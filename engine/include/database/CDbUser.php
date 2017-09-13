<?php

class DbUser{
	
	static function addCorpusRoles($user_id, $corpus_roles = array()){
		
		foreach($corpus_roles as $corpus_id => $roles){
			foreach($roles as $role){
				DbUser::addSingleCorpusRole($user_id, $corpus_id, $role);
			}
		}
		
	}
	
	static function addSingleCorpusRole($user_id, $corpus_id, $role){
		global $db;
		$sql = "INSERT INTO users_corpus_roles(user_id, corpus_id, role) values (?,?,?);";
		$db->execute($sql, array($user_id, $corpus_id, $role));
		$error = $db->mdb2->errorInfo();
		if(isset($error[0]))
			throw new Exception("Error: (". $error[1] . ") -> ".$error[2]);
	}
	
	/**
	 * Zwraca tablicę z danymi użytkownika z tabeli users.
	 */
	static function get($user_id){
		global $db;
		return $db->fetch("SELECT * FROM users WHERE user_id = ?", $user_id);
	}

	static function getAnonymousActivitiesByYear($reverseOrder = false){
        global $db;

        if($reverseOrder){
            $order = "DESC";
        } else{
            $order = "ASC";
        }


        $sql = "SELECT YEAR(a.datetime) AS year, COUNT(a.activity_page_id) AS number_of_activities , COUNT(DISTINCT(a.ip_id)) AS number_of_users FROM activities a 
                WHERE a.user_id IS NULL 
                GROUP BY YEAR(a.datetime) ";
        $sql .= " ORDER BY YEAR(a.datetime) " . $order;

        $activities_years = $db->fetch_rows($sql);

        return $activities_years;
    }

    static function getAnonymousActivitiesByYearMonth(){
	    global $db;

        $sql = "SELECT YEAR(a.datetime) AS year, MONTH(a.datetime) as month, COUNT(a.activity_page_id) AS number_of_activities , COUNT(DISTINCT(a.ip_id)) AS number_of_users FROM activities a 
                WHERE a.user_id IS NULL 
                GROUP BY YEAR(a.datetime), MONTH(a.datetime)
                ORDER BY YEAR(a.datetime) DESC, MONTH(a.datetime) DESC";

        $activities_years_months = $db->fetch_rows($sql);

        return $activities_years_months;
    }
	
}

?>