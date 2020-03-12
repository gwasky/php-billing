<?php
include_once('class.pog_base.php');

/*
	This SQL query will create the table to store your object.

	CREATE TABLE `wimax_billing` (
	`id` int(11) NOT NULL auto_increment,
	`entry_id` INT NOT NULL,
	`entry_date` DATE NOT NULL,
	`account_id` VARCHAR(255) NOT NULL,
	`bill_start` DATE NOT NULL,
	`bill_end` DATE NOT NULL,
	`entry_type` VARCHAR(255) NOT NULL,
	`entry` VARCHAR(255) NOT NULL,
	`amount` DECIMAL NOT NULL,
	`balance` DECIMAL NOT NULL,
	`matched_invoice` INT NOT NULL,
	`billing_date` DATE NOT NULL,
	`user` VARCHAR(255) NOT NULL,
	`currency` VARCHAR(255) NOT NULL,
	`parent_id` VARCHAR(255) NOT NULL,
	`rate_date` DATE NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM;
*/

/**
* <b>wimax_billing</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=wimax_billing&attributeList=array+%28%0A++0+%3D%3E+%27entry_id%27%2C%0A++1+%3D%3E+%27entry_date%27%2C%0A++2+%3D%3E+%27account_id%27%2C%0A++3+%3D%3E+%27bill_start%27%2C%0A++4+%3D%3E+%27bill_end%27%2C%0A++5+%3D%3E+%27entry_type%27%2C%0A++6+%3D%3E+%27entry%27%2C%0A++7+%3D%3E+%27amount%27%2C%0A++8+%3D%3E+%27balance%27%2C%0A++9+%3D%3E+%27matched_invoice%27%2C%0A++10+%3D%3E+%27billing_date%27%2C%0A++11+%3D%3E+%27user%27%2C%0A++12+%3D%3E+%27currency%27%2C%0A++13+%3D%3E+%27parent_id%27%2C%0A++14+%3D%3E+%27rate_date%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27INT%27%2C%0A++1+%3D%3E+%27DATE%27%2C%0A++2+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++3+%3D%3E+%27DATE%27%2C%0A++4+%3D%3E+%27DATE%27%2C%0A++5+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++6+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++7+%3D%3E+%27DECIMAL%27%2C%0A++8+%3D%3E+%27DECIMAL%27%2C%0A++9+%3D%3E+%27INT%27%2C%0A++10+%3D%3E+%27DATE%27%2C%0A++11+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++12+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++13+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++14+%3D%3E+%27DATE%27%2C%0A%29
*/

class wimax_billing extends POG_Base
{
	public $id = '';

	/**
	 * @var INT
	 */
	public $entry_id;
	
	/**
	 * @var DATE
	 */
	public $entry_date;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $account_id;
	
	/**
	 * @var DATE
	 */
	public $bill_start;
	
	/**
	 * @var DATE
	 */
	public $bill_end;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $entry_type;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $entry;
	
	/**
	 * @var DECIMAL
	 */
	public $amount;
	
	/**
	 * @var DECIMAL
	 */
	public $balance;
	
	/**
	 * @var INT
	 */
	public $matched_invoice;
	
	/**
	 * @var DATE
	 */
	public $billing_date;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $user;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $currency;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $parent_id;
	
	/**
	 * @var DATE
	 */
	public $rate_date;
	
	public $pog_attribute_type = array(
		"id" => array('db_attributes' => array("NUMERIC", "INT")),
		"entry_id" => array('db_attributes' => array("NUMERIC", "INT")),
		"entry_date" => array('db_attributes' => array("NUMERIC", "DATE")),
		"account_id" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"bill_start" => array('db_attributes' => array("NUMERIC", "DATE")),
		"bill_end" => array('db_attributes' => array("NUMERIC", "DATE")),
		"entry_type" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"entry" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"amount" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"balance" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"matched_invoice" => array('db_attributes' => array("NUMERIC", "INT")),
		"billing_date" => array('db_attributes' => array("NUMERIC", "DATE")),
		"user" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"currency" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"parent_id" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"rate_date" => array('db_attributes' => array("NUMERIC", "DATE")),
		);
	public $pog_query;
	
	
	/**
	* Getter for some private attributes
	* @return mixed $attribute
	*/
	public function __get($attribute)
	{
		if (isset($this->{"_".$attribute}))
		{
			return $this->{"_".$attribute};
		}
		else
		{
			return false;
		}
	}
	
	function wimax_billing($entry_id='', $entry_date='', $account_id='', $bill_start='', $bill_end='', $entry_type='', $entry='', $amount='', $balance='', $matched_invoice='', $billing_date='', $user='', $currency='', $parent_id='', $rate_date='')
	{
		$this->entry_id = $entry_id;
		$this->entry_date = $entry_date;
		$this->account_id = $account_id;
		$this->bill_start = $bill_start;
		$this->bill_end = $bill_end;
		$this->entry_type = $entry_type;
		$this->entry = $entry;
		$this->amount = $amount;
		$this->balance = $balance;
		$this->matched_invoice = $matched_invoice;
		$this->billing_date = $billing_date;
		$this->user = $user;
		$this->currency = $currency;
		$this->parent_id = $parent_id;
		$this->rate_date = $rate_date;
	}
	
	
	/**
	* Gets object from database
	* @param integer $id 
	* @return object $wimax_billing
	*/
	function Get($id)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `wimax_billing` where `id`='".intval($id)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->id = $row['id'];
			$this->entry_id = $this->Unescape($row['entry_id']);
			$this->entry_date = $row['entry_date'];
			$this->account_id = $this->Unescape($row['account_id']);
			$this->bill_start = $row['bill_start'];
			$this->bill_end = $row['bill_end'];
			$this->entry_type = $this->Unescape($row['entry_type']);
			$this->entry = $this->Unescape($row['entry']);
			$this->amount = $this->Unescape($row['amount']);
			$this->balance = $this->Unescape($row['balance']);
			$this->matched_invoice = $this->Unescape($row['matched_invoice']);
			$this->billing_date = $row['billing_date'];
			$this->user = $this->Unescape($row['user']);
			$this->currency = $this->Unescape($row['currency']);
			$this->parent_id = $this->Unescape($row['parent_id']);
			$this->rate_date = $row['rate_date'];
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $wimax_billingList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `wimax_billing` ";
		$wimax_billingList = Array();
		if (sizeof($fcv_array) > 0)
		{
			$this->pog_query .= " where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$this->pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) != 1)
					{
						$this->pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						if ($GLOBALS['configuration']['db_encoding'] == 1)
						{
							$value = POG_Base::IsColumn($fcv_array[$i][2]) ? "BASE64_DECODE(".$fcv_array[$i][2].")" : "'".$fcv_array[$i][2]."'";
							$this->pog_query .= "BASE64_DECODE(`".$fcv_array[$i][0]."`) ".$fcv_array[$i][1]." ".$value;
						}
						else
						{
							$value =  POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$this->Escape($fcv_array[$i][2])."'";
							$this->pog_query .= "".$fcv_array[$i][0]." ".$fcv_array[$i][1]." ".$value;
						}
					}
					else
					{
						$value = POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$fcv_array[$i][2]."'";
						$this->pog_query .= "".$fcv_array[$i][0]." ".$fcv_array[$i][1]." ".$value;
					}
				}
			}
		}
		if ($sortBy != '')
		{
			if (isset($this->pog_attribute_type[$sortBy]['db_attributes']) && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'SET')
			{
				if ($GLOBALS['configuration']['db_encoding'] == 1)
				{
					$sortBy = "BASE64_DECODE($sortBy) ";
				}
				else
				{
					$sortBy = "$sortBy ";
				}
			}
			else
			{
				$sortBy = "$sortBy ";
			}
		}
		else
		{
			$sortBy = "id";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		//echo $this->pog_query."<br>";
		while ($row = Database::Read($cursor))
		{
			$wimax_billing = new $thisObjectName();
			$wimax_billing->id = $row['id'];
			$wimax_billing->entry_id = $this->Unescape($row['entry_id']);
			$wimax_billing->entry_date = $row['entry_date'];
			$wimax_billing->account_id = $this->Unescape($row['account_id']);
			$wimax_billing->bill_start = $row['bill_start'];
			$wimax_billing->bill_end = $row['bill_end'];
			$wimax_billing->entry_type = $this->Unescape($row['entry_type']);
			$wimax_billing->entry = $this->Unescape($row['entry']);
			$wimax_billing->amount = $this->Unescape($row['amount']);
			$wimax_billing->balance = $this->Unescape($row['balance']);
			$wimax_billing->matched_invoice = $this->Unescape($row['matched_invoice']);
			$wimax_billing->billing_date = $row['billing_date'];
			$wimax_billing->user = $this->Unescape($row['user']);
			$wimax_billing->currency = $this->Unescape($row['currency']);
			$wimax_billing->parent_id = $this->Unescape($row['parent_id']);
			$wimax_billing->rate_date = $row['rate_date'];
			$wimax_billingList[] = $wimax_billing;
		}
		return $wimax_billingList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $id
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `id` from `wimax_billing` where `id`='".$this->id."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `wimax_billing` set 
			`entry_id`='".$this->Escape($this->entry_id)."', 
			`entry_date`='".$this->entry_date."', 
			`account_id`='".$this->Escape($this->account_id)."', 
			`bill_start`='".$this->bill_start."', 
			`bill_end`='".$this->bill_end."', 
			`entry_type`='".$this->Escape($this->entry_type)."', 
			`entry`='".$this->Escape($this->entry)."', 
			`amount`='".$this->Escape($this->amount)."', 
			`balance`='".$this->Escape($this->balance)."', 
			`matched_invoice`='".$this->Escape($this->matched_invoice)."', 
			`billing_date`='".$this->billing_date."', 
			`user`='".$this->Escape($this->user)."', 
			`currency`='".$this->Escape($this->currency)."', 
			`parent_id`='".$this->Escape($this->parent_id)."', 
			`rate_date`='".$this->rate_date."' where `id`='".$this->id."'";
		}
		else
		{
			$this->pog_query = "insert into `wimax_billing` (`entry_id`, `entry_date`, `account_id`, `bill_start`, `bill_end`, `entry_type`, `entry`, `amount`, `balance`, `matched_invoice`, `billing_date`, `user`, `currency`, `parent_id`, `rate_date` ) values (
			'".$this->Escape($this->entry_id)."', 
			'".$this->entry_date."', 
			'".$this->Escape($this->account_id)."', 
			'".$this->bill_start."', 
			'".$this->bill_end."', 
			'".$this->Escape($this->entry_type)."', 
			'".$this->Escape($this->entry)."', 
			'".$this->Escape($this->amount)."', 
			'".$this->Escape($this->balance)."', 
			'".$this->Escape($this->matched_invoice)."', 
			'".$this->billing_date."', 
			'".$this->Escape($this->user)."', 
			'".$this->Escape($this->currency)."', 
			'".$this->Escape($this->parent_id)."', 
			'".$this->rate_date."' )";
		}
		
		//echo $this->pog_query."<br>";
		
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->id == "")
		{
			$this->id = $insertId;
		}
		return $this->id;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $id
	*/
	function SaveNew()
	{
		$this->id = '';
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `wimax_billing` where `id`='".$this->id."'";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array)
	{
		if (sizeof($fcv_array) > 0)
		{
			$connection = Database::Connect();
			$pog_query = "delete from `wimax_billing` where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) !== 1)
					{
						$pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$this->Escape($fcv_array[$i][2])."'";
					}
					else
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$fcv_array[$i][2]."'";
					}
				}
			}
			return Database::NonQuery($pog_query, $connection);
		}
	}
}
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `wimax_invoicing` (
	`id` int(11) NOT NULL auto_increment,
	`generation_date` DATE NOT NULL,
	`parent_id` VARCHAR(255) NOT NULL,
	`billing_date` DATE NOT NULL,
	`previous_balance` DECIMAL NOT NULL,
	`payments_sum` DECIMAL NOT NULL,
	`adjustments_sum` DECIMAL NOT NULL,
	`charges_sum` DECIMAL NOT NULL,
	`amount_payable` DECIMAL NOT NULL,
	`period` VARCHAR(255) NOT NULL,
	`details` VARCHAR(255) NOT NULL,
	`invoice_number` INT NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM;
*/

/**
* <b>wimax_invoicing</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=wimax_invoicing&attributeList=array+%28%0A++0+%3D%3E+%27generation_date%27%2C%0A++1+%3D%3E+%27parent_id%27%2C%0A++2+%3D%3E+%27billing_date%27%2C%0A++3+%3D%3E+%27previous_balance%27%2C%0A++4+%3D%3E+%27payments_sum%27%2C%0A++5+%3D%3E+%27adjustments_sum%27%2C%0A++6+%3D%3E+%27charges_sum%27%2C%0A++7+%3D%3E+%27amount_payable%27%2C%0A++8+%3D%3E+%27period%27%2C%0A++9+%3D%3E+%27details%27%2C%0A++10+%3D%3E+%27invoice_number%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27DATE%27%2C%0A++1+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++2+%3D%3E+%27DATE%27%2C%0A++3+%3D%3E+%27DECIMAL%27%2C%0A++4+%3D%3E+%27DECIMAL%27%2C%0A++5+%3D%3E+%27DECIMAL%27%2C%0A++6+%3D%3E+%27DECIMAL%27%2C%0A++7+%3D%3E+%27DECIMAL%27%2C%0A++8+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++9+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++10+%3D%3E+%27INT%27%2C%0A%29
*/

class wimax_invoicing extends POG_Base
{
	public $id = '';

	/**
	 * @var DATE
	 */
	public $generation_date;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $parent_id;
	
	/**
	 * @var DATE
	 */
	public $billing_date;
	
	/**
	 * @var DECIMAL
	 */
	public $previous_balance;
	
	/**
	 * @var DECIMAL
	 */
	public $payments_sum;
	
	/**
	 * @var DECIMAL
	 */
	public $adjustments_sum;
	
	/**
	 * @var DECIMAL
	 */
	public $charges_sum;
	
	/**
	 * @var DECIMAL
	 */
	public $amount_payable;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $period;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $details;
	
	/**
	 * @var INT
	 */
	public $invoice_number;
	
	public $pog_attribute_type = array(
		"id" => array('db_attributes' => array("NUMERIC", "INT")),
		"generation_date" => array('db_attributes' => array("NUMERIC", "DATE")),
		"parent_id" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"billing_date" => array('db_attributes' => array("NUMERIC", "DATE")),
		"previous_balance" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"payments_sum" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"adjustments_sum" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"charges_sum" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"amount_payable" => array('db_attributes' => array("NUMERIC", "DECIMAL")),
		"period" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"details" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"invoice_number" => array('db_attributes' => array("NUMERIC", "INT")),
		);
	public $pog_query;
	
	
	/**
	* Getter for some private attributes
	* @return mixed $attribute
	*/
	public function __get($attribute)
	{
		if (isset($this->{"_".$attribute}))
		{
			return $this->{"_".$attribute};
		}
		else
		{
			return false;
		}
	}
	
	function wimax_invoicing($generation_date='', $parent_id='', $billing_date='', $previous_balance='', $payments_sum='', $adjustments_sum='', $charges_sum='', $amount_payable='', $period='', $details='', $invoice_number='')
	{
		$this->generation_date = $generation_date;
		$this->parent_id = $parent_id;
		$this->billing_date = $billing_date;
		$this->previous_balance = $previous_balance;
		$this->payments_sum = $payments_sum;
		$this->adjustments_sum = $adjustments_sum;
		$this->charges_sum = $charges_sum;
		$this->amount_payable = $amount_payable;
		$this->period = $period;
		$this->details = $details;
		$this->invoice_number = $invoice_number;
	}
	
	
	/**
	* Gets object from database
	* @param integer $id 
	* @return object $wimax_invoicing
	*/
	function Get($id)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `wimax_invoicing` where `id`='".intval($id)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->id = $row['id'];
			$this->generation_date = $row['generation_date'];
			$this->parent_id = $this->Unescape($row['parent_id']);
			$this->billing_date = $row['billing_date'];
			$this->previous_balance = $this->Unescape($row['previous_balance']);
			$this->payments_sum = $this->Unescape($row['payments_sum']);
			$this->adjustments_sum = $this->Unescape($row['adjustments_sum']);
			$this->charges_sum = $this->Unescape($row['charges_sum']);
			$this->amount_payable = $this->Unescape($row['amount_payable']);
			$this->period = $this->Unescape($row['period']);
			$this->details = $this->Unescape($row['details']);
			$this->invoice_number = $this->Unescape($row['invoice_number']);
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $wimax_invoicingList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `wimax_invoicing` ";
		$wimax_invoicingList = Array();
		if (sizeof($fcv_array) > 0)
		{
			$this->pog_query .= " where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$this->pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) != 1)
					{
						$this->pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						if ($GLOBALS['configuration']['db_encoding'] == 1)
						{
							$value = POG_Base::IsColumn($fcv_array[$i][2]) ? "BASE64_DECODE(".$fcv_array[$i][2].")" : "'".$fcv_array[$i][2]."'";
							$this->pog_query .= "BASE64_DECODE(`".$fcv_array[$i][0]."`) ".$fcv_array[$i][1]." ".$value;
						}
						else
						{
							$value =  POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$this->Escape($fcv_array[$i][2])."'";
							$this->pog_query .= "".$fcv_array[$i][0]." ".$fcv_array[$i][1]." ".$value;
						}
					}
					else
					{
						$value = POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$fcv_array[$i][2]."'";
						$this->pog_query .= "".$fcv_array[$i][0]." ".$fcv_array[$i][1]." ".$value;
					}
				}
			}
		}
		if ($sortBy != '')
		{
			if (isset($this->pog_attribute_type[$sortBy]['db_attributes']) && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'SET')
			{
				if ($GLOBALS['configuration']['db_encoding'] == 1)
				{
					$sortBy = "BASE64_DECODE($sortBy) ";
				}
				else
				{
					$sortBy = "$sortBy ";
				}
			}
			else
			{
				$sortBy = "$sortBy ";
			}
		}
		else
		{
			$sortBy = "id";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		//echo "---->>> ".$this->pog_query."<br>";
		while ($row = Database::Read($cursor))
		{
			$wimax_invoicing = new $thisObjectName();
			$wimax_invoicing->id = $row['id'];
			$wimax_invoicing->generation_date = $row['generation_date'];
			$wimax_invoicing->parent_id = $this->Unescape($row['parent_id']);
			$wimax_invoicing->billing_date = $row['billing_date'];
			$wimax_invoicing->previous_balance = $this->Unescape($row['previous_balance']);
			$wimax_invoicing->payments_sum = $this->Unescape($row['payments_sum']);
			$wimax_invoicing->adjustments_sum = $this->Unescape($row['adjustments_sum']);
			$wimax_invoicing->charges_sum = $this->Unescape($row['charges_sum']);
			$wimax_invoicing->amount_payable = $this->Unescape($row['amount_payable']);
			$wimax_invoicing->period = $this->Unescape($row['period']);
			$wimax_invoicing->details = $this->Unescape($row['details']);
			$wimax_invoicing->invoice_number = $this->Unescape($row['invoice_number']);
			$wimax_invoicingList[] = $wimax_invoicing;
		}
		return $wimax_invoicingList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $id
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `id` from `wimax_invoicing` where `id`='".$this->id."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `wimax_invoicing` set 
			`generation_date`='".$this->generation_date."', 
			`parent_id`='".$this->Escape($this->parent_id)."', 
			`billing_date`='".$this->billing_date."', 
			`previous_balance`='".$this->Escape($this->previous_balance)."', 
			`payments_sum`='".$this->Escape($this->payments_sum)."', 
			`adjustments_sum`='".$this->Escape($this->adjustments_sum)."', 
			`charges_sum`='".$this->Escape($this->charges_sum)."', 
			`amount_payable`='".$this->Escape($this->amount_payable)."', 
			`period`='".$this->Escape($this->period)."', 
			`details`='".$this->Escape($this->details)."', 
			`invoice_number`='".$this->Escape($this->invoice_number)."' where `id`='".$this->id."'";
		}
		else
		{
			$this->pog_query = "insert into `wimax_invoicing` (`generation_date`, `parent_id`, `billing_date`, `previous_balance`, `payments_sum`, `adjustments_sum`, `charges_sum`, `amount_payable`, `period`, `details`, `invoice_number` ) values (
			'".$this->generation_date."', 
			'".$this->Escape($this->parent_id)."', 
			'".$this->billing_date."', 
			'".$this->Escape($this->previous_balance)."', 
			'".$this->Escape($this->payments_sum)."', 
			'".$this->Escape($this->adjustments_sum)."', 
			'".$this->Escape($this->charges_sum)."', 
			'".$this->Escape($this->amount_payable)."', 
			'".$this->Escape($this->period)."', 
			'".$this->Escape($this->details)."', 
			'".$this->Escape($this->invoice_number)."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		//echo $this->pog_query."<br>";
		if ($this->id == "")
		{
			$this->id = $insertId;
		}
		return $this->id;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $id
	*/
	function SaveNew()
	{
		$this->id = '';
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `wimax_invoicing` where `id`='".$this->id."'";
		//echo ">> ".$this->pog_query."<br>";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array)
	{
		if (sizeof($fcv_array) > 0)
		{
			$connection = Database::Connect();
			$pog_query = "delete from `wimax_invoicing` where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{

				if (sizeof($fcv_array[$i]) == 1)
				{
					$pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) !== 1)
					{
						$pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$this->Escape($fcv_array[$i][2])."'";
					}
					else
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$fcv_array[$i][2]."'";
					}
				}
			}
			return Database::NonQuery($pog_query, $connection);
		}
	}
}

/*
	This SQL query will create the table to store your object.

	CREATE TABLE `wimax_rates` (
	`id` int(11) NOT NULL auto_increment,
	`rate_date` INT NOT NULL,
	`rate` DATE NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM;
*/

/**
* <b>wimax_rates</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=wimax_rates&attributeList=array+%28%0A++0+%3D%3E+%27rate_date%27%2C%0A++1+%3D%3E+%27rate%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27INT%27%2C%0A++1+%3D%3E+%27DATE%27%2C%0A%29
*/
class wimax_rates extends POG_Base
{
	public $id = '';

	/**
	 * @var INT
	 */
	public $rate_date;
	
	/**
	 * @var DATE
	 */
	public $rate;
	
	public $pog_attribute_type = array(
		"id" => array('db_attributes' => array("NUMERIC", "INT")),
		"rate_date" => array('db_attributes' => array("NUMERIC", "INT")),
		"rate" => array('db_attributes' => array("NUMERIC", "DATE")),
		);
	public $pog_query;
	
	
	/**
	* Getter for some private attributes
	* @return mixed $attribute
	*/
	public function __get($attribute)
	{
		if (isset($this->{"_".$attribute}))
		{
			return $this->{"_".$attribute};
		}
		else
		{
			return false;
		}
	}
	
	function wimax_rates($rate_date='', $rate='')
	{
		$this->rate_date = $rate_date;
		$this->rate = $rate;
	}
	
	
	/**
	* Gets object from database
	* @param integer $id 
	* @return object $wimax_rates
	*/
	function Get($id)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `wimax_rates` where `id`='".intval($id)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->id = $row['id'];
			$this->rate_date = $this->Unescape($row['rate_date']);
			$this->rate = $row['rate'];
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $wimax_ratesList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `wimax_rates` ";
		$wimax_ratesList = Array();
		if (sizeof($fcv_array) > 0)
		{
			$this->pog_query .= " where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$this->pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) != 1)
					{
						$this->pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						if ($GLOBALS['configuration']['db_encoding'] == 1)
						{
							$value = POG_Base::IsColumn($fcv_array[$i][2]) ? "BASE64_DECODE(".$fcv_array[$i][2].")" : "'".$fcv_array[$i][2]."'";
							$this->pog_query .= "BASE64_DECODE(`".$fcv_array[$i][0]."`) ".$fcv_array[$i][1]." ".$value;
						}
						else
						{
							$value =  POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$this->Escape($fcv_array[$i][2])."'";
							$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
						}
					}
					else
					{
						$value = POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$fcv_array[$i][2]."'";
						$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
					}
				}
			}
		}
		if ($sortBy != '')
		{
			if (isset($this->pog_attribute_type[$sortBy]['db_attributes']) && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'SET')
			{
				if ($GLOBALS['configuration']['db_encoding'] == 1)
				{
					$sortBy = "BASE64_DECODE($sortBy) ";
				}
				else
				{
					$sortBy = "$sortBy ";
				}
			}
			else
			{
				$sortBy = "$sortBy ";
			}
		}
		else
		{
			$sortBy = "id";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		
		//echo $this->pog_query."<br>";
		
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$wimax_rates = new $thisObjectName();
			$wimax_rates->id = $row['id'];
			$wimax_rates->rate_date = $this->Unescape($row['rate_date']);
			$wimax_rates->rate = $row['rate'];
			$wimax_ratesList[] = $wimax_rates;
		}
		return $wimax_ratesList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $id
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `id` from `wimax_rates` where `id`='".$this->id."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `wimax_rates` set 
			`rate_date`='".$this->Escape($this->rate_date)."', 
			`rate`='".$this->rate."' where `id`='".$this->id."'";
		}
		else
		{
			$this->pog_query = "insert into `wimax_rates` (`rate_date`, `rate` ) values (
			'".$this->Escape($this->rate_date)."', 
			'".$this->rate."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->id == "")
		{
			$this->id = $insertId;
		}
		return $this->id;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $id
	*/
	function SaveNew()
	{
		$this->id = '';
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `wimax_rates` where `id`='".$this->id."'";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array)
	{
		if (sizeof($fcv_array) > 0)
		{
			$connection = Database::Connect();
			$pog_query = "delete from `wimax_rates` where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) !== 1)
					{
						$pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$this->Escape($fcv_array[$i][2])."'";
					}
					else
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$fcv_array[$i][2]."'";
					}
				}
			}
			return Database::NonQuery($pog_query, $connection);
		}
	}
}

?>