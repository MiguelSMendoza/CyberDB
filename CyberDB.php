<?php
/**
 * Helper to access a Mysqli database
 * @copyright 2014 SMendoza.net
 * @license    http://www.gnu.org/licenses/gpl-2.0.html  GNU GPL v2
 * @link       https://github.com/MiguelSMendoza/CyberDB
 * @version 1.0
 * @author Miguel S. Mendoza <miguel@smendoza.net>
 **/
if (!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
if (!defined('CYBERDB_PATH')) define('CYBERDB_PATH', dirname(preg_replace('/\\\\/','/',__FILE__)) . '/');

class CyberDB {

	private $logErrors = true;
	private $throwException = false;

	private $dbhost = 'HOST';
	private $dbuser = 'USER';
	private $dbpassword = 'PASSWORD';
	private $dbname = 'DATABASE';

	private $mysqli;
	private $Query;
	private $Result;

	public function getQuery() {
		if(!isset($this->Query))
			$this->Query = new Query();
		return $this->Query;
	}

	public function setQuery($query) {
		if($query instanceof Query)
			$this->Query = $query;
		else if(is_string($query))
				$this->Query = new Query($query);
			else
				return false;
			return true;
	}

	public function getArray() {
		$result = array();

		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			while ($row = $this->Result->fetch_array(MYSQLI_ASSOC)) {
				array_push($result, $row);
			}
			$this->Result->free();
		}
		else {
			$this->raiseError("Conection Failed - ".$this->mysqli->error);
		}
		$this->closeDB();
		return $result;
	}
	
	public function getObjectsArray() {
		$result = array();

		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			while ($row = $this->Result->fetch_object()) {
				array_push($result, $row);
			}
			$this->Result->free();
		}
		else {
			$this->raiseError("Conection Failed - ".$this->mysqli->error);
		}
		$this->closeDB();
		return $result;
	}

	public function getResult() {
		$resultado = false;
		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			$aux = $this->Result->fetch_array(MYSQLI_NUM);
			$resultado = $aux[0];
			$this->Result->free();
		}
		else {
			$this->raiseError("Conection Failed - ".$this->mysqli->error);
		}
		$this->closeDB();
		return $resultado;
	}

	public function getRow() {
		$resultado = false;
		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			$resultado = $this->Result->fetch_assoc();
			$this->Result->free();
		}
		else {
			$this->raiseError("Conection Failed: ".$this->mysqli->error);
		}
		$this->closeDB();
		return $resultado;
	}
	
	public function getObject() {
		$resultado = false;
		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			$resultado = $this->Result->fetch_object();
			$this->Result->free();
		}
		else {
			$this->raiseError("Conection Failed: ".$this->mysqli->error);
		}
		$this->closeDB();
		return $resultado;
	}

	public function executeQuery() {
		$result = false;
		$this->connectDB();

		if ($this->Result = $this->mysqli->query((string) $this->Query)) {
			$result = ($this->mysqli->affected_rows > 0);
		}
		else {
			$this->raiseError("Conection Failed - ".$this->mysqli->error);
		}

		$this->closeDB();
		return $result;
	}
	
	public function setDB($host, $user, $pass, $name) {
		$this->dbhost = $host;
		$this->dbuser = $user;
		$this->dbpassword = $pass;
		$this->dbname = $name;
	}
	
	private function connectDB() {
		$this->mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
		if ($this->mysqli->connect_errno) {
			$this->raiseError("Error de Conexion con la BBDD-".$this->mysqli->connect_error);
			return false;
		}
		return true;
	}

	private function closeDB() {
		$this->mysqli->close();
		$this->Query = new Query();
	}

	private function raiseError($msg) {
		if($this->logErrors) $this->toLog($msg);
		if($this->throwException) throw new Exception($msg);
	}
	
	private function toLog($mensaje) {
		$log = CYBERDB_PATH.DS.'errors.log';
		$msj = date('m/d/Y h:i:s a', time())." - ".$mensaje.PHP_EOL;
		echo $msj."<p></p>";
		file_put_contents($log, $msj, FILE_APPEND | LOCK_EX);
	}

}

class Query {

	private $Statement;

	private $Type;

	private $Select;
	private $From;

	private $Update;
	private $Set;

	private $Delete;

	private $Insert;
	private $Columns;
	private $Values;

	private $Where;

	private $OrderBy;

	public function __construct($query = '') {
		$this->Type = QueryType::STATEMENT;
		$this->Statement = $query;
	}

	public function Where($whereClauses) {
		if(isset($this->Where))
			$this->Where->appendElements($whereClauses);
		else 
			$this->Where = new QueryElement('WHERE', $whereClauses, ' AND ');
		return $this;
	}
	/**
	 *
	 * @param mixed $columns SELECT Columns for Query Element
	 *
	 **/
	public function Select($columns) {
		$this->Type = QueryType::SELECT;
		if(isset($this->Select))
			$this->Select->appendElements($columns);
		else 
			$this->Select = new QueryElement('SELECT', $columns);
		return $this;
	}
	/**
	 *
	 * @param mixed $tables FROM Tables for Query Element
	 *
	 **/
	public function From($tables) {
		if(isset($this->From))
			$this->From->appendElements($tables);
		else 
			$this->From = new QueryElement('FROM', $tables);
		return $this;
	}
	/**
	 *
	 * @param mixed $orderByClauses ORDER By Clauses for Query Element
	 *
	 **/
	public function OrderBy($orderByClauses) {
		if(isset($this->Order))
			$this->Order->appendElements($orderByClauses);
		else 
			$this->Order = new QueryElement('ORDER BY', $orderByClauses);
		return $this;
	}
	/**
	 *
	 * @param mixed $updateTable UPDATE Table for Query Element
	 *
	 * @since 1.0
	 **/
	public function Update($updateTable) {
		$this->Type = QueryType::UPDATE;
		if(isset($this->Update))
			$this->Update->appendElements($updateTable);
		else 
			$this->Update = new QueryElement('UPDATE',$updateTable);
		return $this;
	}
	/**
	 *
	 * @param mixed $setClauses SET Clauses for UPDATE Statement
	 *
	 * @since 1.0
	 **/
	public function Set($setClauses){
		if(isset($this->Set))
			$this->Set->appendElements($setClauses);
		else 
			$this->Set = new QueryElement('SET',$setClauses);
		return $this;
	}
	/**
	 *
	 * @param mixed $deleteTable DELETE Table for DELETE Statement
	 *
	 * @since 1.0
	 **/
	public function Delete($deleteTable) {
		$this->Type = QueryType::DELETE;
		$this->Delete = new QueryElement('DELETE FROM',$deleteTable);
		return $this;
	}
	/**
	 *
	 * @param mixed $insertTable INSERT Table for INSERT Statement
	 *
	 * @since 1.0
	 **/
	public function Insert($insertTable) {
		$this->Type = QueryType::INSERT;
		$this->Insert = new QueryElement('INSERT INTO',$insertTable);
		return $this;
	}
	/**
	 *
	 * @param mixed $columns INSERT Columns for INSERT Statement
	 *
	 * @since 1.0
	 **/
	public function Columns($columns) {
		if(isset($this->Insert))
			$this->Insert->appendElements($columns);
		else 
			$this->Insert = new QueryElement('()',$columns);
		return $this;
	}
	/**
	 *
	 * @param mixed $values INSERT Values for INSERT Statement
	 *
	 * @since 1.0
	 **/
	public function Values($values) {
		if(isset($this->Insert))
			$this->Insert->appendElements($values);
		else 
			$this->Insert = new QueryElement('()',$values);
		return $this;
	}

	public function __toString() {
		$query = "";
		switch($this->Type) {
		case QueryType::STATEMENT:
			$query = $this->Statement;
			break;
		case QueryType::SELECT:
			$query = $this->Select.$this->From.$this->Where.$this->OrderBy;
			break;
		case QueryType::UPDATE:
			$query = $this->Update.$this->Set.$this->Where;
			break;
		case QueryType::INSERT:
			$query = $this->Insert.$this->Columns.$this->Values;
			break;
		case QueryType::DELETE:
			$query = $this->Delete.$this->Where;
			break;
		}
		return $query;
	}

	public function gettype() {
		return $this->Type;
	}

}

class QueryElement {

	private $Name;

	private $Elements;

	private $Glue;

	public function __construct($name, $elements, $glue = ', ') {
		$this->Elements = array();
		$this->Name = $name;
		$this->Glue = $glue;

		$this->appendElements($elements);
	}

	public function __toString(){
		if(strcmp($this->Name, '()')==0)
			return PHP_EOL .'('.implode($this->Glue, $this->Elements).') ';
		else
			return PHP_EOL .$this->Name.' '.implode($this->Glue, $this->Elements);
	}

	public function appendElements($elements) {
		if (is_array($elements)) {
			$this->Elements = array_merge($this->Elements, $elements);
		}
		else {
			$this->Elements = array_merge($this->Elements, array($elements));
		}
	}
}

class QueryType {
	const STATEMENT = 0;
	const SELECT = 1;
	const UPDATE = 2;
	const DELETE = 3;
	const INSERT = 4;
}

?>
