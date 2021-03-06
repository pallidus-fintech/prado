<?php
Prado::using('System.Data.*');
Prado::using('System.Data.Common.Pgsql.TPgsqlMetaData');

/**
 * @package System.Data.DbCommon
 */
class CommandBuilderPgsqlTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped(
              'The pdo_pgsql extension is not available.'
            );
        }
    }

	function pgsql_meta_data()
	{
		$conn = new TDbConnection('pgsql:host=localhost;dbname=prado_unitest', 'prado_unitest','prado_unitest');
		return new TPgsqlMetaData($conn);
	}

	function test_insert_command_using_named_array()
	{
		$builder = $this->pgsql_meta_data()->createCommandBuilder('address');
		$address=array(
			'username' => 'Username',
			'phone' => 121987,
			'field1_boolean' => true,
			'field2_date' => '1213',
			'field3_double' => 121.1,
			'field4_integer' => 345,
			'field6_time' => time(),
			'field7_timestamp' => time(),
			'field8_money' => '121.12',
			'field9_numeric' => 984.22,
			'int_fk1'=>1,
			'int_fk2'=>1,
		);
		$insert = $builder->createInsertCommand($address);
		$sql = 'INSERT INTO public.address("username", "phone", "field1_boolean", "field2_date", "field3_double", "field4_integer", "field6_time", "field7_timestamp", "field8_money", "field9_numeric", "int_fk1", "int_fk2") VALUES (:username, :phone, :field1_boolean, :field2_date, :field3_double, :field4_integer, :field6_time, :field7_timestamp, :field8_money, :field9_numeric, :int_fk1, :int_fk2)';
		$this->assertEquals($sql, $insert->Text);
	}

	function test_update_command()
	{
		$builder = $this->pgsql_meta_data()->createCommandBuilder('address');
		$data = array(
			'phone' => 9809,
			'int_fk1' => 1212,
		);
		$update = $builder->createUpdateCommand($data, '1');
		$sql = 'UPDATE public.address SET "phone" = :phone, "int_fk1" = :int_fk1 WHERE 1';
		$this->assertEquals($sql, $update->Text);
	}

	function test_delete_command()
	{
		$builder = $this->pgsql_meta_data()->createCommandBuilder('address');
		$where = 'phone is NULL';
		$delete = $builder->createDeleteCommand($where);
		$sql = 'DELETE FROM public.address WHERE phone is NULL';
		$this->assertEquals($sql, $delete->Text);
	}

	function test_select_limit()
	{
		$meta = $this->pgsql_meta_data();
		$builder = $meta->createCommandBuilder('address');
		$query = 'SELECT * FROM '.$meta->getTableInfo('address')->getTableFullName();

		$limit = $builder->applyLimitOffset($query, 1);
		$expect = $query.' LIMIT 1';
		$this->assertEquals($expect, $limit);

		$limit = $builder->applyLimitOffset($query, -1, 10);
		$expect = $query.' OFFSET 10';
		$this->assertEquals($expect, $limit);

		$limit = $builder->applyLimitOffset($query, 2, 3);
		$expect = $query.' LIMIT 2 OFFSET 3';
		$this->assertEquals($expect, $limit);
	}
}
