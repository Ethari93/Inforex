<?php
/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 * See LICENCE 
 */
 
$engine = realpath(dirname(__FILE__) . "/../engine/");
include($engine . "/config.php");
include($engine . "/config.local.php");
include($engine . "/include.php");
include($engine . "/cliopt.php");

mb_internal_encoding("utf-8");
ob_end_clean();
 
/******************** set configuration   *********************************************/

$opt = new Cliopt();
$opt->addParameter(new ClioptParameter("db-uri", "U", "URI", "connection URI: user:pass@host:ip/name"));
$opt->addParameter(new ClioptParameter("verbose", "v", null, "verbose mode"));

$config = null;

/******************** parse cli *********************************************/

$formats = array();
$formats['xml'] = 1;
$formats['plain'] = 2;
$formats['premorph'] = 3;

try{
	$opt->parseCli($argv);
	
	$dbHost = "localhost";
	$dbUser = "root";
	$dbPass = null;
	$dbName = "gpw";
	$dbPort = "3306";

	if ( $opt->exists("db-uri")){
		$uri = $opt->getRequired("db-uri");
		if ( preg_match("/(.+):(.+)@(.*):(.*)\/(.*)/", $uri, $m)){
			$dbUser = $m[1];
			$dbPass = $m[2];
			$dbHost = $m[3];
			$dbPort = $m[4];
			$dbName = $m[5];
		}else{
			throw new Exception("DB URI is incorrect. Given '$uri', but exptected 'user:pass@host:port/name'");
		}
	}
	
	$config->dsn['phptype'] = 'mysql';
	$config->dsn['username'] = $dbUser;
	$config->dsn['password'] = $dbPass;
	$config->dsn['hostspec'] = $dbHost . ":" . $dbPort;
	$config->dsn['database'] = $dbName;
	$config->verbose = $opt->exists("verbose");
			
}catch(Exception $ex){
	print "!! ". $ex->getMessage() . " !!\n\n";
	$opt->printHelp();
	die("\n");
}

// Główna pętla sprawdzająca żądania w kolejce.
while (true){
	try{
		$daemon = new TaskDaemon($config->dsn, $config->verbose); 	
		while ($daemon->tick()){};	
	}
	catch(Exception $ex){
		print "Error: " . $ex->getMessage() . "\n";
		print_r($ex);
	}
	sleep(2);
}
	

/******************** main function       *********************************************/
// Process all files in a folder
function tick ($config){

} 

/**
 * Handle single request from tasks_documents.
 */
class TaskDaemon{
	
	function __construct($dsn, $verbose){
		$this->db = new Database($dsn, false);
		$this->verbose = $verbose;
		$this->info("Verbose mode: On");		
	}
	
	/**
	 * Print message if verbose mode is on.
	 */
	function info($message){
		if ( $this->verbose ){
			echo $message . "\n";
		}		
	}
	
	/**
	 * Check the queue for new request.
	 */
	function tick(){
		$this->db->execute("BEGIN");
	
		$sql = "SELECT t.*, tr.report_id" .
				" FROM tasks t" .
				" LEFT JOIN tasks_reports tr ON (tr.task_id=t.task_id AND tr.status = ?)" .
				" WHERE t.type = ? AND t.status <> 'done' AND t.status <> 'error' ORDER BY datetime ASC LIMIT 1";
		$task = $this->db->fetch($sql, array("new", "liner2"));
		$this->info($task);
			
		if ( $task === null ){
			return false;
		}
	
		if ( $task['status'] == "new" ){
			$this->db->update("tasks", array("status"=>"process"), array("task_id"=>$task['task_id']));
		}
		
		if ( $task['status'] == "process" && !$task['report_id'] ){
			$this->db->update("tasks", array("status"=>"done"), array("task_id"=>$task['task_id']));		
		}
		
		if ( $task['report_id'] ){	
			$this->db->update("tasks_reports", 
					array("status"=>"process"), 
					array("task_id"=>$task['task_id'], "report_id"=>$task['report_id']));
		}
				
		$this->db->execute("COMMIT");
		
		if ( $task['report_id'] ){		
			print_r($task);
			try{
				$anns_count = $this->process($task['report_id'], $task['user_id']);
	
				$this->db->update("tasks_reports", 
						array("status"=>"done", "message"=>"Number of recognized annotations: $anns_count"), 
						array("task_id"=>$task['task_id'], "report_id"=>$task['report_id']));
				
				$this->db->execute("UPDATE tasks SET current_step=current_step+1 WHERE task_id = ?",
						array($task['task_id']));
				return true;
			}
			catch(Exception $ex){
				$this->info("Exception: " . $ex->getMessage());
				
				if ( $ex->getMessage() == "TIMEOUT" ){
					$this->db->update("tasks_reports", 
							array("status"=>"new"), 
							array("task_id"=>$task['task_id'], "report_id"=>$task['report_id']));
				}
				else{
					$this->db->update("tasks_reports", 
							array("status"=>"error"), 
							array("task_id"=>$task['task_id'], "report_id"=>$task['report_id']));				
				}
			}			
		}	
			
		return false;		
	}

	/**
	 * 
	 */
	function process($report_id, $user_id){
		$content = $this->db->fetch_one("SELECT content FROM reports WHERE id = ?", array($report_id));
		$content = strip_tags($content);
			
		$wsdl = "http://kotu88.ddns.net/nerws/ws/nerws.wsdl";
		//$wsdl = "http://188.124.184.105/nerws/ws/nerws.wsdl";
			
		$liner2 = new WSLiner2($wsdl);	
		$tuples = $liner2->chunk($content, "PLAIN:WCRFT", "TUPLES", "ner-names");
		
		if (preg_match_all("/\((.*),(.*),\"(.*)\"\)/", $tuples, $matches, PREG_SET_ORDER)){
			print "Number of annotations: " . count($matches) . "\n";
			foreach ($matches as $m){
				$annotation_type = strtolower($m[2]);
				list($from, $to) = split(',', $m[1]);
				$ann_text = trim($m[3], '"');
					
				// Todo: kwerendy do przepisania przy użyciu mdb2.
				$sql = "SELECT `id` FROM `reports_annotations` " .
						"WHERE `report_id`=? AND `type`=? AND `from`=? AND `to`=?";
				if (count($this->db->fetch_rows($sql, array($report_id, $annotation_type, $from, $to)))==0){					
					$sql = "INSERT INTO `reports_annotations_optimized` " .
							"(`report_id`, `type_id`, `from`, `to`, `text`, `user_id`, `creation_time`, `stage`,`source`) VALUES " .
							'(?, (SELECT annotation_type_id FROM annotation_types WHERE name=?), ?, ?, ?, ?, now(), "new", "bootstrapping")';
					$params = array($report_id, $annotation_type, $from, $to, $ann_text, $user_id);
					$this->db->execute($sql, $params);
				}
			}
			return count($matches);
		}
		return 0;
	}	
}	
	
/******************** main invoke         *********************************************/
main($config);
?>