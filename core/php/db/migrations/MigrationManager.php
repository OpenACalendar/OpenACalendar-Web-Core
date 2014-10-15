<?php


namespace db\migrations;



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MigrationManager {
	public static function upgrade($verbose = false) {
		global $DB, $CONFIG, $LASTMIGRATIONCLASS;

		// some vars
		$timeSource = new \TimeSource();

		// First, the migrations table.
		$stat = $DB->query("select * from information_schema.tables where table_name='migration'");
		$tableExists = ($stat->rowCount() == 1);
		
		if ($tableExists) {
			if ($verbose) print "Migrations table exists.\n";
		} else {
			if ($verbose) print "Creating migration table.\n";
			$DB->query("CREATE TABLE migration ( id VARCHAR(255) NOT NULL, installed_at timestamp without time zone NOT NULL, PRIMARY KEY(id)  )");
		}

		// Now load all possible migrations from disk & sort them
		$migrations = array();
		$dirs = array(dirname(__FILE__).'/../../../sql/migrations/');
		foreach($CONFIG->extensions as $extensionName) {
			$dirs[] = dirname(__FILE__).'/../../../../extension/'.$extensionName.'/sql/migrations/';
		}
		foreach($dirs as $dir) {
			if (is_dir($dir)) {
				$handle = opendir($dir);		
				$fileEndingPHP = '.migration.set.php';
				$fileEndingSQL = '.'.$CONFIG->databaseType.'.sql';
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..') {
						if ($verbose) echo "Loading ".$file."\n";
						if (substr($file, -strlen($fileEndingSQL)) == $fileEndingSQL) {
							$migrations[] = new Migration(substr($file, 0, -strlen($fileEndingSQL)), file_get_contents($dir.$file));
						} else if (substr($file, -strlen($fileEndingPHP)) == $fileEndingPHP) {
							require_once $dir. substr($file,0, - strlen($fileEndingPHP)).".migration.class.php";
							require $dir.$file;
							$migrations[] = $LASTMIGRATIONCLASS;
						}
					}
				}
				closedir($handle);
			}
		}		
		usort($migrations, "db\migrations\MigrationManager::compareMigrations");
		
		// Now see what is already applied 
		// ... in an O(N^2) loop inside a loop, performance could be better but doesn't matter here for now.
		$stat = $DB->query("SELECT id FROM migration");
		while($result = $stat->fetch()) {
			foreach($migrations as $migration) { 
				if ($migration->getId() == $result['id']) {
					$migration->setIsApplied();
				}
			}
		}
		
		// Finally apply the new ones!
		if ($verbose) {
			foreach($migrations as $migration) {
				if (!$migration->getApplied()) {
					print "Will apply ".$migration->getId()."\n";				
				} else {
					print "Already Applied ".$migration->getId()."\n";
				}
			}
		}

		$stat = $DB->prepare("INSERT INTO migration (id, installed_at) VALUES (:id, :at)");
		foreach($migrations as $migration) {
			if (!$migration->getApplied()) {
				if ($verbose) print "Applying ".$migration->getId()."\n";
				$DB->beginTransaction();
				$migration->performMigration($DB, $timeSource, $CONFIG);
				$stat->execute(array('id'=>$migration->getId(),'at'=> date("Y-m-d H:i:s")));
				$DB->commit();
				if ($verbose) print "Applied ".$migration->getId()."\n";
			}
		}
		
		if ($verbose) print "Done\n";
		
		
	}
	
	private static function compareMigrations(Migration $a, Migration $b) {
		if ($a->getIdAsUnixTimeStamp() == $b->getIdAsUnixTimeStamp()) return 0;
		return ($a->getIdAsUnixTimeStamp() < $b->getIdAsUnixTimeStamp()) ? -1 : 1;
	}	
	
}


