<?php

/**
 * Database class
 *
 * Hoping for the appearance of The Promised Savior
 *
 * @author   Iliya Gholami 2023 - 2024 <OctalDev313@gmail.com>
 * @copyright Iliya Gholami 2023 - 2024 <OctalDev313@gmail.com>
 */

namespace IliyaGholami;

use \Amp\Mysql\MysqlConfig;
use \Amp\Mysql\MysqlConnectionPool;

class Database
{

	/**
	 * @var object $query_builder query_builder
	 */
	private object $query_builder;

	/**
	 * @var MysqlConnectionPool $db database
	 */
	public MysqlConnectionPool $db;


	/**
	 * Constructor
	 *
	 * @param string $host host
	 * @param string $user user
	 * @param string $pass pass
	 * @param string $name dbname
	 */
	public function __construct( string $host, string $user, string $pass, string $name )
	{

		$config = MysqlConfig::fromString("host=$host user=$user password=$pass db=$name");

		$this->db = new MysqlConnectionPool($config);

		$this->query_builder = (object) [];

		$this->query_builder->mode = "";
		$this->query_builder->where = "";
	}

	/**
	 * Sanitize input
	 *
	 * @param string $data data
	 * @return string
	 */
	public function sanitizeInput( string $data )
	{

		return htmlentities(   addslashes(  trim( $data )  )   );

	}

	/**
	 * Prepare
	 *
	 * @param string $query query
	 * @return MysqlConnectionPool
	 */
	public function prepare( string $query )
	{

		return $this->db->prepare($query);

	}

	/**
	 * Query
	 *
	 * @param string $query query
	 * @return MysqlConnectionPool
	 */
	public function query( string $query )
	{

		return $this->db->query($query);

	}

	/**
	 * Table
	 *
	 * @param string $name table name
	 * @return Mysql
	 */
	public function table( string $name )
	{

		$this->query_builder->table = $this->sanitizeInput($name);
		return $this;
	}

	/**
	 * Create table
	 *
	 * @param string $name table name
	 * @param object $closure function
	 *
	 * @return MysqlConnectionPool
	 */
	public function createTable(string $name, object $closure)
	{
		$name = $this->sanitizeInput($name);

		$table = new Table();

		$closure($table);

		$query = $table->query;
		$query = substr($query, 0, strlen($query) - 1);

		$query = "CREATE TABLE IF NOT EXISTS $name(" . $query . ") ";
		$query .= "default charset = utf8mb4;";

		return $this->query($query);
	}

	/**
	 * Insert
	 *
	 * @param all ...$datas datas
	 * @return Mysql
	 */
	public function insert(...$datas)
	{
		$this->query_builder->mode = "insert";
		$this->query_builder->insert = $datas;
		
		return $this;
	}

	/**
	 * Select
	 *
	 * @param all ...$fields fields to select
	 * @return Mysql
	 */
	public function select(...$fields)
	{
		if( empty($fields) ) $fields = ["*"];

		$this->query_builder->mode = "select";
		$this->query_builder->select = $fields;
		
		return $this;
	}

	/**
	 * Update
	 *
	 * @param all ...$datas datas
	 * @return Mysql
	 */
	public function update(...$datas)
	{
		$this->query_builder->mode = "update";
		$this->query_builder->update = $datas;

		return $this;
	}

	/**
	 * Delete
	 *
	 * @return Mysql
	 */
	public function delete()
	{
		$this->query_builder->mode = "delete";

		return $this;
	}

	/**
	 * where
	 *
	 * @param string $field field
	 * @param string $value value
	 * @oaram string $operator operator ( default = "=" )
	 *
	 * @return Datbase
	 */
	public function where(string $field, string $value, string $operator = '=')
	{
		$field = $this->sanitizeInput($field);
		$value = $this->sanitizeInput($value);
		$operator = $this->sanitizeInput($operator);
		
		$this->query_builder->where = "WHERE `{$field}` {$operator} '{$value}'";
		
		return $this;
	}

	/**
	 * Find by id
	 *
	 * @param string $id id
	 * @return Mysql
	 */
	public function find(string $id)
	{
		return $this->where("id", $id, "=");
	}

	/**
	 * Execute
	 *
	 * @return array | object MysqlConnectionPool response
	 */
	public function execute()
	{
		$query = $this->getQuery();
		
		return $this->query($query);
	}

	/**
	 * Get query
	 *
	 * @return string
	 */
	public function getQuery()
	{
		$query_builder = $this->query_builder;

		$table = $query_builder->table;
		$mode = $query_builder->mode;
		$where = $query_builder->where;

		if( $mode == "select" ) {
			$select = $query_builder->select;
			$query = "SELECT ";

			foreach($select as $key) {
				$key = $this->sanitizeInput($key);
				$query .= "{$key}, ";
			}

			$query = substr($query, 0, strlen($query) - 2);
			$query .= " FROM `{$table}` $where";
		}

		if( $mode == "delete" ) {
			$query = "DELETE FROM `{$table}`";
			$query .= " $where";
		}

		if( $mode == "update" ) {
			$query = "UPDATE `{$table}` SET ";
			foreach($query_builder->update as $key => $value) {
				$value = $this->sanitizeInput($value);
				$query .= "`$key` = '$value', ";
			}
			$query = substr($query, 0, strlen($query) - 2);
			$query .= " $where";
		}

		if( $mode == "insert" ) {
			$query = "INSERT INTO `{$table}` SET ";
			foreach($query_builder->insert as $key => $value) {
				$value = $this->sanitizeInput($value);
				$query .= "`$key` = '$value', ";
			}
			$query = substr($query, 0, strlen($query) - 2);
		}

		$query .= ";";

		return $query;
	}

	/**
	 * Add a column
	 *
	 * @param string $columnName column name
	 * @param string $dataType data type
	 *
	 * @return MysqlConnectionPool
	 */ 
	public function addColumn(string $columnName, string $dataType)
	{
		$table = $this->query_builder->table;
		$column = $this->sanitizeInput($columnName);
		$data = $this->sanitizeInput($dataType);

		$query = "alter table {$table}\n";
		$query .= "add column {$column} {$data}";

		return $this->query($query);
	}

	/**
	 * Show tables
	 *
	 * @return array
	 */
	public function showTables()
	{

		$stmt = $this->query("SHOW TABLES;");

		$tables = [];

		foreach( $stmt as $row ) {

			$row = array_values($row);
			$tables[] = $row[0];

		}

		return $tables;
	}

	/**
	 * Drop table
	 *
	 * @param string $name table name
	 * @return MysqlConnectionPool
	 */
	public function dropTable(string $name)
	{

		$name = $this->sanitizeInput($name);
		return $this->query("DROP TABLE `{$name}`;");

	}

	/**
	 * drop multi table
	 *
	 * @param ...$tables tables name
	 * @return MysqlConnectionPool
	 */
	public function dropTables(...$tables)
	{

		if( empty($tables) ) {

			throw new \Exception("Tables must not be empty");

		}

		$tables = $this->sanitizeInput(implode(", ", $tables));
		return $this->query("DROP TABLE $tables;");
	}

	/**
	 * Add a column
	 *
	 * @param string $tableName table name
	 * @param string $columnName column name
	 * @param string $dataType data type
	 *
	 * @return MysqlConnectionPool
	 */ 
	public function addColumn(string $tableName, string $columnName, string $dataType)
	{
		$table = $this->sanitizeInput($tableName);
		$column = $this->sanitizeInput($columnName);
		$data = $this->sanitizeInput($dataType);
		
		$query = "alter table {$table}\n";
		$query .= "add column {$column} {$data}";
		
		return $this->query($query);
	}

	/**
	 * Add few column
	 *
	 * @param string $tableName table name
	 * @param array $datas columnName and dataType
	 *
	 * @return MysqlConnectionPool
	 */
	public function addColumns($tableName, $datas)
	{
		$table = $this->sanitizeInput($tableName);

		$query = "alter table {$table}\n";

		foreach($datas as $columnName => $dataType) {
			$column = $this->sanitizeInput($columnName);
			$data = $this->sanitizeInput($dataType);
			$query .= "add column {$column} {$data},";
		}

		$query = substr($query, 0, strlen($query) - 1);

		return $this->query($query);
	}
}