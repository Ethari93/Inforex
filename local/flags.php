<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
$engine = "../engine/";
include($engine . "config.php");
include($engine . "config.local.php");
include($engine . "include.php");
include($engine . "cliopt.php");

mb_internal_encoding("UTF-8");

$opt = new Cliopt();
$opt->addExecute("php set-flags.php --document n --user u --db-name xxx --db-user xxx --db-pass xxx --db-host xxx --db-port xxx",null);
$opt->addExecute("php set-flags.php --subcorpus n --user u --db-name xxx --db-user xxx --db-pass xxx --db-host xxx --db-port xxx",null);
$opt->addExecute("php set-flags.php --corpus n --user u --db-name xxx --db-user xxx --db-pass xxx --db-host xxx --db-port xxx",null);
$opt->addParameter(new ClioptParameter("db-uri", "U", "URI", "connection URI: user:pass@host:ip/name"));
$opt->addParameter(new ClioptParameter("document", "d", "report_id", "report id"));
$opt->addParameter(new ClioptParameter("corpus", "c", "corpus_id", "corpus id"));
$opt->addParameter(new ClioptParameter("subcorpus", "s", "subcorpus_id", "subcorpus id"));
$opt->addParameter(new ClioptParameter("flag", "f", "flag name", "flag name"));
$opt->addParameter(new ClioptParameter("status", "v", "id", "flag status id"));
$opt->addParameter(new ClioptParameter("init", null, null, "init only not set flags"));
$config = null;
try {
	$opt->parseCli($argv);
	
	if ( $opt->exists("db-uri")){
		$uri = $opt->getRequired("db-uri");
		if ( preg_match("/(.+):(.+)@(.*)\/(.*)/", $uri, $m)){
			$dbUser = $m[1];
			$dbPass = $m[2];
			$dbHost = $m[3];
			$dbName = $m[4];
		}else{
			throw new Exception("DB URI is incorrect. Given '$uri', but exptected 'user:pass@host:port/name'");
		}
	}
	
	$config->dsn = array(
	    			'phptype'  => 'mysql',
	    			'username' => $dbUser,
	    			'password' => $dbPass,
	    			'hostspec' => $dbHost,
	    			'database' => $dbName);	$config->corpus = $opt->getParameters("corpus");
	$config->subcorpus = $opt->getParameters("subcorpus");
	$config->documents = $opt->getParameters("document");
	$config->flag = $opt->getOptional("flag", null);
	$config->status = $opt->getOptional("status", null);
	$config->init = $opt->exists("init");
	
	if ( count($config->corpus) == 0 && count($config->subcorpus) == 0 && count($config->documents) == 0 )
		throw new Exception("No corpus, subcorpus nor report id set");
		
	if ( !$config->init )
		throw new Exception("Use -init");
		
} 
catch(Exception $ex){
	print "!! ". $ex->getMessage() . " !!\n\n";
	$opt->printHelp();
	die("\n");
}
#include("../../engine/database.php");
	
/******************** main function       *********************************************/
// Process all files in a folder
function main ($config){

	$db = new Database($config->dsn);
	$GLOBALS['db'] = $db;

	$ids = array();
	$n = 0;
	
	foreach ($config->corpus as $c){
		$sql = sprintf("SELECT * FROM reports WHERE corpora = %d", $c);
		foreach ( $db->fetch_rows($sql) as $r ){
			$ids[$r['id']] = 1;			
		}		
	}

	foreach ($config->subcorpus as $s){
		$sql = sprintf("SELECT * FROM reports WHERE subcorpus_id = %d", $s);
		foreach ( $db->fetch_rows($sql) as $r ){
			$ids[$r['id']] = 1;			
		}		
	}
	
	foreach ($config->documents as $d){
		$ids[$d] = 1;
	}
	
	foreach ( array_keys($ids) as $report_id){
		echo "\r " . (++$n) . " z " . count($ids) . " :  id=$report_id     ";
			
		$doc = $db->fetch("SELECT * FROM reports WHERE id=?",array($report_id));
		
		if ( $config->init )
			init_flag_status($doc['corpora'], $report_id, $config->flag, $config->status, $db);
	}
	
} 


/******************** aux function        *********************************************/
/**
 * Set status if not initiated
 */
function init_flag_status($corpora_id, $report_id, $flag_name, $status, $db){
	$sql = "SELECT corpora_flag_id FROM corpora_flags WHERE corpora_id = ? AND short = ?";
	$corpora_flag_id = $db->fetch_one($sql, array($corpora_id, $flag_name));

	if ($corpora_flag_id){
		$value = intval($db->fetch_one("SELECT flag_id FROM reports_flags WHERE corpora_flag_id = ? AND report_id = ?",
							array($corpora_flag_id, $report_id) ) ); 
		if ( $value == -1 || $value == 0 ){
			$db->execute("REPLACE reports_flags (corpora_flag_id, report_id, flag_id) VALUES(?, ?, ?)",
				array($corpora_flag_id, $report_id, $status));
		}	
	}	
	
}

/******************** main invoke         *********************************************/
main($config);

echo "done ■\n";
	
?>