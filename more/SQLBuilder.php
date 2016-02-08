<?
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