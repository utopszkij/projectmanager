<?php
/** mysql database interface 
*
* requed DEFINE: MYSQLHOST, MYSQLUSER, MYSQLPSW, MYSQLDB;
*
* DB class
*    setQuery($sqlStr)
*    getQuery() : sqlStr
*    staement() : bool
*    loadObject() : recordObject
*    loadObjectList() : Array of recordObjects
*    getErrorNum() : numeric
*    getErrorMsg() : string
*    table($tableName, $alias='', $columns='*') : Table
*    filter($tableName, $alias='', $columns='*') : Filter
*    transaction(function);
* Table class
*    where($whereStr or [field, value] vagy [field, relStr, value]) : Table 
*    orWhere($whereStr or [field, value] vagy [field, relStr, value]) : Table
*    group([field, field, ...]) : table   
*    having($whereStr or [field, value] vagy [field, relStr, value]) : Table 
*    orHaving($whereStr or [field, value] vagy [field, relStr, value]) : Table
*    offset($num) : Table
*    limit($num) : Table
*    order([fild, filed,...]) : Table
*    get() : array of RecordsObject
*    first() : recordObject
*    count() : numeric 
*    update(record)
*    insert(record)
*    delete(record)
*    getInsertedId() : numeric
*    getErrorNum() : numeric
*    getErrorMsg() : string
* Filer class
*    join($type, $tableName, $alias, $onStr) : Filter;
*    where($whereStr or [field, value] vagy [field, relStr, value]) : Filter 
*    orWhere($whereStr or [field, value] vagy [field, relStr, value]) : Filter
*    group([field, field, ...]) : Filter   
*    having($whereStr or [field, value] vagy [field, relStr, value]) : Filter 
*    orHaving($whereStr or [field, value] vagy [field, relStr, value]) : Filter
*    offset($num) : Filter
*    limit($num): Filter
*    order([fild, filed,...]) : Filter
*    get() : array of RecordsObject
*    first() : recordObject
*    count() : numeric 
*    getErrorNum() : numeric
*    getErrorMsg() : string
*
* global $dbResult array használható UNITTEST -hez
* 
* Licensz: GNU/GPL
* Szerző: Fogler Tibor    tibor.fogler@gmail.com
*/

global $mysqli, $dbResult;
$dbResult = [];
if (MYSQLHOST != '') {
    $mysqli = new mysqli(MYSQLHOST, MYSQLUSER, MYSQLPSW, MYSQLDB);
}

class DB {
   protected $mysqli;
   protected $sql;
   protected $errorMsg;
   protected $errorNum;
   protected $inTransaction = false;
    
   function __construct() {
    	global $mysqli;
        $this->mysqli = $mysqli;
   }
   
	public function setQuery(string $sql) {
		$this->sql = $sql;
	}

	public function getQuery() : string {
		return $this->sql;
	}

	public function loadObjectList() {
		global $dbResult;
        $this->errorMsg = '';
        $this->errorNum = 0;
        $result = '_none_';
        if (count($dbResult) > 0) {
            $result = $dbResult[0];
            array_splice($dbResult,0,1);
        }
        if ($result == '_none_') {
            $result = [];
            if (MYSQLHOST == '') {
                $result = [];
            }
            try {
                $cursor = $this->mysqli->query($this->sql);
            } catch (Exception $e) {
                try {
                    $this->mysqli = new mysqli("localhost", TESTDBUSER, TESTDBPSW, TESTDB);
                    try {
                        $cursor = $this->mysqli->query($this->sql);
                    } catch(Exception $e) {
                        $cursor = false;
                        $this->errorMsg = 'error_in_query '.$e->getMessage().' sql='.$this->sql;
                        $this->errorNum = 1000;
                    }
                } catch(Exception $e) {
                        $cursor = false;
                        $this->errorMsg = 'error_in_reconnect '.$e->getMessage();
                        $this->errorNum = 1000;
                }
            }
            if ($cursor) {
                $w = $cursor->fetch_object();
                while ($w != null) {
                    $i = count($result);
                    $result[$i] = $w;
                    $w = $cursor->fetch_object();
                }
                $cursor->close();
            }
        }
        return $result;
	}
	
	public function loadObject() {
	    global $dbResult;
	    $result = '_none_';
	    if (count($dbResult) > 0) {
	        $result = $dbResult[0];
	        array_splice($dbResult,0,1);
        }
	    if ($result == '_none_') {
            $res = $this->loadObjectList();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        }
        return $result;
	}

	public function query() : bool {
	    global $dbResult;
	    $this->errorMsg = '';
	    $this->errorNum = 0;
	    $result = '_none_';
	    if (count($dbResult) > 0) {
	        $result = $dbResult[0];
	        array_splice($dbResult,0,1);
	    }
		if ($result == '_none_') {
		    if (MYSQLHOST == '') {
		        $result = true;
		        return $result;
		    }
            if (!isset($this->sql)) $this->sql = '';
            try {
                $result = $this->mysqli->query($this->sql);
                if (!$result && $this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
                }
                $this->errorMsg = 'error_in_query '.$this->mysqli->error.' sql='.$this->sql;
                $this->errorNum = $this->mysqli->errno;
            } catch (Exception $e) {
                $return = false;
                $this->errorMsg = 'error_in_reconnect '.$e->getMessage().' sql='.$this->sql;
                $this->errorNum = 1000;
                if ($this->inTransaction) {
                    $this->mysqli->rollback();
                    $this->inTransaction = false;
                }
            }
        }
        return $result;
	}
	
	public function getErrorNum() : numeric {
		return $this->errorNum;
	}

	public function getErrorMsg() : string {
		return $this->errorMsg;
	}
	
	public function quote(string $str) : string {
        // global $mysqli;	    
	    // $result = $mysqli->real_escape_string($str);
        $str = str_replace('"','\"',$str);
        $str = str_replace("\n",'\n',$str);
        if (is_string($str)) {
	        $str = '"'.$str.'"';
	    }
	    return $str;
	}
	
   public function exec(string $sqlStr) : bool {
        $this->setQuery($sqlStr);
        return $this->query();
   }

	public function statement(string $sqlStr) : bool {
		$this->setQuery($sqlStr);
		return $this->query();	
	} 
 
	public static function table(string $fromStr, string $alias = '', string $columns = '*') {
		$result = new Table();
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	}  

	public static function filter(string $formStr, string $alias = '', string $columns = '*') {
		$result = new Filter();
		$result->setFromStr($fromStr, $alias, $columns);
		return $result;	
	} 
	
	public static function transaction($fun) {
	    $this->inTransaction = true;
	    $this->mysqli->begin_transaction();
	    $fun();
	    if ($this->inTransaction) {
	       $this->mysqli->commit();
	    }
	    $this->inTransaction = false;
	}
	 
} // DB

class Table extends DB {
   protected $fromStr = '';
   protected $columns = '*';
   protected $alias = '';
   protected $whereStr = '';
   protected $groupStr = '';
   protected $havingStr = '';
   protected $orderStr = '';
   protected $offset = 0;
   protected $limit = 0;
   
	public function setFromStr(string $fromStr, string $alias, string $columns) {
		$this->fromStr = $fromStr;
		return $this;	
	}

	public function get() {
		if ($this->whereStr == '') $this->whereStr = '1';
		if ($this->orderStr == '') $this->orderStr = '1';
		if ($this->offset == '') $this->offset = '0';
		$sqlStr = 'SELECT '.$this->columns.' FROM '.$this->fromStr.' '.$this->alias;
		$sqlStr .= ' WHERE '.$this->whereStr.' ORDER BY '.$this->orderStr;
		if ($this->limit != 0) {
			$sqlStr .= ' LIMIT '.$this->offset.','.$this->limit;		
		}	
		if ($this->groupStr != '') {
			$sqlStr .= ' GROUP BY '.$this->groupStr;		
		}
		if ($this->havingStr != '') {
			$sqlStr .= ' HAVING '.$this->groupStr;		
		}
		$this->setQuery($sqlStr);
		return $this->loadObjectList();
	}	
	
	public function first() {
		$this->limit = 1;
		$res = $this->get();
		if (count($res) > 0) {
		  return $res[0];
		} else {
		  return false;  
		}
	}
	
	public function where($par, string $con = ' AND ', string $dest = 'whereStr') {
		if ($this->$dest != '') $this->$dest .= $con;
		if (is_string($par)) {
			$this->$dest .= $par;		
		} else if (is_array($par)) {
			if ((count($par) == 2) && (is_string($par[0]))) 
					$this->$dest .= '`'.$par[0].'` = '.$this->quote($par[1]);			
		} else if ((count($par) == 3) && (is_string($par[0]))) {
					$this->$dest .= '`'.$par[0].'` '.$par[1].' '.$this->quote($par[1]);			
		} else {
				foreach ($par as $p) {
				    if ((count($p) == 2) && (is_string($p[0]))) {
							$this->$dest .= '`'.$p[0].'` = '.$this->quote($p[1]);			
				    } else if ((count($p) == 3) && (is_string($p[0]))) {
							$this->$dest .= '`'.$p[0].'` '.$p[1].' '.$this->quote($p[1]);
				    }
					$this->$dest .= ' AND ';					
				}
				$this->$destr .= ' 1';					
		}
		return $this;	
	}	
	
	
	public function orWhere($par) {
		$this->where($par, ' OR (','where');
		$this->whereStr .= ')';
		return $this;	
	}

	public function group($par) {
		$this->groupStr = '';
		foreach ($par as $fn) {
			if ($this->groupStr != '') $this->groupStr .= ',';
				$this->groupStr .= '`'.$fn.'`';
		}
		return $this;	
	}
	
	public function having($par) {
		$this->where($par,' AND ','having');
		return $this;
	}

	public function orHaving($par) {
		$this->where($par,' OR (','having');
		$this->having .= ')';		
		return $this;
	}
		
	public function order(string $s) {
		$this->orderStr = $s;
		return $this;	
	}	

	public function limit(numeric $v) {
		$this->limit = $v;
		return $this;	
	}	

	public function offset(numeric $v) {
		$this->offset = $v;
		return $this;	
	}	
	
	public function delete() {
		if ($this->whereStr == '') $this->whereStr = '1';
		$sqlStr = 'DELETE FROM '.$this->fromStr.
		' WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}

	public function update($record) {
		if ($this->whereStr == '') $this->whereStr = '1';
		$s = '';
		foreach ($record as $fn => $fv) {
			if ($s != '') $s .= ',';
			$s .= '`'.$fn.'`='.$this->quote($fv);		
		}
		$sqlstr = 'UPDATE '.$this->fromstr.' SET '.$s.' WHERE '.$this->whereStr;
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}

	public function insert($record) {
		if ($this->whereStr == '') $this->whereStr = '1';
		$fnames = '';
		$values = '';
		foreach ($record as $fn => $fv) {
			if ($fnames != '') $fnames .= ',';
			$fnames .= '`'.$fn.'`';
			if ($values != '') $values .= ',';
			$values .= $this->quote($fv);
		}
		$sqlStr = 'INSERT INTO '.$this->fromStr.' ('.$fnames.') VALUES ('.$values.')';
		$this->setQuery($sqlStr);
		$this->query();
		return $this;
	}
	
	public function getInsertedId() {
	    global $mysqli;
	    return $mysqli->insert_id;
	}
	
	public function count() : numeric {
		if ($this->whereStr == '') $this->whereStr = '1';
		$sqlStr = 'SELECT count(*) AS cc FROM '.$this->formStr.' WHERE '.$this->whereStr;
		$this->setSql($sqlstr);
		$res = $this->loadObject(); 
		return $res->cc;
	}	
	
	public function getFieldList() {
		$sqlStr = 'SHOW FIELDS FROM '.$this->fromStr;
		$this->setSql($sqlstr);
		return $this->loadObjectList(); 
	}
	
} // Table

class Filter extends Table {
	protected $joins = array();
	
	public function join(string $joinType, string $tableName, string $alias, string $onStr) {
		// joinType: 'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'INNER JOIN'
		$this->joins.push(array($joinType, $tableName, $alias, $onStr));
		return $this;
	}

	public function get() {
		if ($this->whereStr == '') $this->whereStr = '1';
		if ($this->orderStr == '') $this->orderStr = '1';
		if ($this->offset == '') $this->offset = '0';
		$sqlStr = 'SELECT '.$this->columns.' FROM '.$this->formStr.' '.$this->alias;
		foreach ($this->joins as $join) {
			$sqlstr .= ' '.$join[0].' '.$joinn[1].' '.$join[2].
			' ON '.$join[3];
		}
		$sqlStr .= ' WHERE '.$this->whereStr.' ORDER BY '.$this->orderStr;
		if ($this->limit != 0) {
			$sqlStr .= 'LIMIT '.$this->offset.','.$this->limit;		
		}	
		if ($this->groupStr != '') {
			$sqlStr .= ' GROUP BY '.$this->groupStr;		
		}
		if ($this->havingStr != '') {
			$sqlStr .= ' HAVING '.$this->groupStr;		
		}
		$this->setQuery($sqlStr);
		return $this->loadObjectList();
	}
} // Filter

?>
