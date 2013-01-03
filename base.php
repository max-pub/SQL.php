<?php

// Extending the basic-PDO-class with some Object-Relational-Mapping capabilities.
// work with associative arrays instead of strings...
class DataBase extends PDO {

	// shortcut function to quickly setup a database with an external sql-file
	function setup($file){
		$this->exec(file_get_contents($file));
	}
    
    // analyzes the given parameters and decides how to interpret them... SQL-string / table-name / where-clause / ...
    // is used by all selecting-methods of this class
    function selectType($s,$p){
        if(strpos($s,'SELECT')===false) { $s = "SELECT * FROM $s"; }
        if($p==''){return $s;}
        if(is_numeric($p)){ return "$s WHERE id=$p"; }
        if(is_array($p)){ return "$s WHERE ".SQLBuilder::where($p); }
        return "$s WHERE $p";
    }
      
    // returns only the first row of the result-set
    function firstRow($sql, $where=''){ return $this->first($sql,$where); }
	function first($sql,$where=''){
		$qs = $this->selectType($sql,$where);
		$q = $this->query($qs);
		if(!$q){echo 'ERROR:: '.$sql.'<br/>'; return array();}
		return $q->fetch(PDO::FETCH_ASSOC);
	}
    
    
    // returns first column of first row. Handy if you just want a count or other aggregate value
	function firstCell($sql,$where=''){
		$qs = $this->selectType($sql,$where);
		$q = $this->query($qs);
		if(!$q){echo 'ERROR:: '.$sql.'<br/>'; return '';}
		$tmp = $q->fetch();
		return $tmp[0];
	}
	
    // returns all rows that match the given query
	function all($sql,$where=''){
		$qs = $this->selectType($sql,$where);
        echo $qs;
        if($this->debug){echo $qs;}
		$q = $this->query($qs);
		if(!$q){echo 'ERROR:: '.$sql.'<br/>'; return array();}
		return $q->fetchAll(PDO::FETCH_ASSOC);
	}
	
	
    // insert new data via an assoc-array (ORM = (primitive) object-relational-mapping)
	function insert($table,$data){
		try{ return $this->exec(SQLBuilder::insert($table,$data)); }
		catch(Exception $e){ echo "ERROR:: ".insert($table,$data); }
	}
    
    // update the DB with both SET and WHERE parameters given as an assoc-array
	function update($table,$data,$pos){
		$this->exec(SQLBuilder::update($table,$data,$pos));
	}
	
    // delete some rows from the set... where-clause as an assoc-array
	function delete($table,$where){ 
		$this->exec(SQLBuilder::delete($table,$where));
	}
	
    // combination of insert & update... tests a given id for you
    function save($table,$data,$id=''){
        if($id){
            $this->update($table,$data,$id);
            return $id;
        } else {
            $this->insert($table,$data);
            return $this->lastInsertId();
        }
    }
	
    // returns distinct values of a given column
	function distinct($table,$col='id',$where=''){
		$tmp = array();
		foreach($this->query("SELECT distinct($col) FROM $table $where") as $row){$tmp[]=$row[0];}
		return $tmp;
	}
    
    // returns a full column of the given name
	function column($sql,$col='id'){
		$tmp = array();
		foreach($this->query($sql) as $row){$tmp[]=$row[$col];}
		return $tmp;
	}
	
	
	
	// returns the max-value of a given column
	function max($table,$column='id'){
		$tmp = dbq1("SELECT max($id) AS 'id' FROM $table");
		$id =  $tmp['id']*1;
		//if($id<10000){$id=10000;}
		return $id;
	}
    
    // returns the number of rows for a given table
	function sizeOf($table){
		$tmp = dbq1("SELECT COUNT(*) AS cnt FROM $table");
		return $tmp['cnt']*1;
	}
}




// bulds the queries that are needed by the DataBase-Class above
class SQLBuilder {
      function escape($str){
          return str_replace("|||","''",str_replace("'","|||",$str));
      }
      
      function escapeArray($arr){
        foreach($arr as $key=>$val){
            $arr[$key] = SQLBuilder::escape($val);
        }
        return $arr;
      }
        
      
      function insert($table,$array){
        $sql = "INSERT INTO $table (";
        $sql .= implode(',',(array_keys($array)));
        $sql .= ") VALUES ('";
        $sql .= implode("','",SQLBuilder::escapeArray(array_values($array)));
        $sql .= "')";
        return $sql;
      }
      
      function update($table,$set,$where){
        if(is_numeric($where)){$where = "rowid='$where'";}
        if(is_array($where)){$where = SQLBuilder::where($where);}
        $sql = "UPDATE $table SET ".SQLBuilder::where($set,', ').' WHERE '.$where;
        return $sql;
      }
      
      function delete($table,$where){
        if(is_numeric($where)){$where = "rowid='$where'";}
        if(is_array($where)){$where = SQLBuilder::where($where);}
        $sql = "DELETE FROM $table WHERE ".$where;
        return $sql;
      }
      
      function where($array,$delimiter=' AND '){
        $tmp = array();
        foreach($array as $k=>$v){
            $k=($k); 
            $v=SQLBuilder::escape($v);
            $tmp[] = "$k='$v'"; // no ' because of "escape"-function
        }
        $sql = implode($delimiter,$tmp);
        return $sql;
      }


}




  
  
  ?>