<?php

/**
 * Table class
 *
 * Hoping for the appearance of The Promised Savior
 *
 * @author   Iliya Gholami 2023 - 2024 <OctalDev313@gmail.com>
 * @copyright Iliya Gholami 2023 - 2024 <OctalDev313@gmail.com>
 */

namespace IliyaGholami;

/**
 * class Table
 */
/**
 * class Table
 */
class Table{
		
	public string $query = "";

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
	 * Primary
	 *
	 * @param string $name column name
	 * @return void
	 */
	public function primary(string $name)
	{
		$name = $this->sanitizeInput($name);
		if( !strpos($this->query, "PRIMARY KEY") ) $this->query .= "PRIMARY KEY ({$name}),";
	}

	/**
	 * Id
	 *
	 * @return void
	 */
	public function id()
	{
		$this->bigint("id", 16);
		$this->primary("id");
	}
	
	/**
	 * Int
	 *
	 * @param string $name column name
	 * @param int $max default (11)
	 *
	 * @return void
	 */
	public function int(string $name, int $max = 11)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name INT($max),";
	}
	
	/**
	 * Bigint
	 *
	 * @param string $name column name
	 * @param int $max default (11)
	 *
	 * @return void
	 */
	public function bigInt(string $name, int $max = 20)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name bigint($max),";
	}

	/**
	 * String ( varchar )
	 *
	 * @param string $name column name
	 * @param int $max default (255)
	 *
	 * @return void
	 */
	public function string(string $name, int $max = 255)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name varchar($max),";
	}

	/**
	 * Text
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function text(string $name)
	{
		$name = $this->sanitizeInput($name);
	
		$this->query .= "$name TEXT,";
	}
	
	/**
	 * Mediumtext
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	 
	public function mediumText(string $name)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name MEDIUMTEXT,";
	}

	/**
	 * Date
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function date(string $name)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name DATE,";
	}

	/**
	 * Time
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function time(string $name)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name TIME,";
	}

	/**
	 * Datetime
	 *
	 * @param string $name column name
	 *
	 * @return void
	 */
	public function dateTime(string $name)
	{
		$name = $this->sanitizeInput($name);

		$this->query .= "$name DATETIME,";
	}
	
	/**
	 * Timestamp
	 *
	 * @param string $name column name
	 * @param string value value
	 *
	 * @return void
	 */
	public function timestamp(string $name, string $value)
	{
		$name = $this->sanitizeInput($name);
		$value = $this->sanitizeInput($value);

		$this->query .= "$name TIMESTAMP($value),";
	}
}