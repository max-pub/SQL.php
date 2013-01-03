<?php

class SQLite extends DataBase {
    function __construct($path) {
        parent::__construct("sqlite:$path");
        $this->path = $path;
//        print "In SubClass constructor\n";
    }
    
    function status(){
    	return array('path'=>$this->path, 'size'=>filesize($this->path));
    }

	function listTables(){
	    $ret = $this->column("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name",'name');
    	return $ret;
    }
    
    function dropTable($name){
    	return $this->exec("DROP TABLE $name");
    }
    
    function renameTable($oldName, $newName){
    	return $this->exec("ALTER TABLE $oldName RENAME TO $newName");
    }
    
    
    
    function listColumns($table){
    	if(!$table){return 0;}
        $cols = $this->query("pragma table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $col){
            $rcol[$col['name']] = $col['type'];
        }
    	return $rcol;
    }
    
    function addColumn($table, $column, $type){
    	return $this->exec("ALTER TABLE $table ADD COLUMN $column $type");
    }
    
} 
 
 





?>