<?php
/**
 * ORM
 *
 * Модульные тесты
 *
 * @version ${product.version}
 *
 * @copyright 2011, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <m.krasilnikov@yandex.ru>
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо (по вашему выбору) с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package ORM
 * @subpackage Tests
 *
 * $Id: bootstrap.php 1849 2011-10-03 17:34:22Z mk $
 */


require_once __DIR__ . '/../bootstrap.php';
require_once TESTS_SRC_DIR . '/orm.php';
require_once TESTS_SRC_DIR . '/orm/classes/Entity.php';
require_once TESTS_SRC_DIR . '/orm/classes/Driver/MySQL.php';
require_once TESTS_SRC_DIR . '/orm/classes/Table.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Table_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown()
	{
		DB::setMock(null);
	}
	//-----------------------------------------------------------------------------
	/**
	 * @covers ORM_Table::__construct
	 */
	public function test_construct()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$table->expects($this->once())->method('setTableDefinition');
		$table->__construct(new Plugin());
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::create
	 *
	 * @see http://bugs.eresus.ru/view.php?id=876
	 */
	public function test_create()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$p_tableName = new ReflectionProperty('ORM_Table', 'tableName');
		$p_tableName->setAccessible(true);
		$p_tableName->setValue($table, 'foo');
		$m_hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
		$m_hasColumns->setAccessible(true);
		$m_hasColumns->invoke($table, array(
			'f1' => array(
				'type' => 'integer',
				'autoincrement' => true,
				'unsigned' => true,
			),
			'f2' => array(
				'type' => 'string',
				'length' => 100,
				'default' => '',
			),
			'f3' => array(
				'type' => 'string',
				'default' => null,
			),
			'f4' => array(
				'type' => 'string',
				'length' => 70000,
			),
			'f5' => array(
				'type' => 'boolean',
			),
			'f6' => array(
				'type' => 'float',
			),
			'f7' => array(
				'type' => 'timestamp',
			),
			'f8' => array(
				'type' => 'date',
			),
			'f9' => array(
				'type' => 'time',
			),
		));

		$m_index = new ReflectionMethod('ORM_Table', 'index');
		$m_index->setAccessible(true);
		$m_index->invoke($table, 'idx1', array('fields' => array('f2', 'f4')));

		$db = $this->getMock('stdClass', array('exec'));
		$db->expects($this->once())->method('exec')->with("CREATE TABLE p_foo (f1 INT(10) UNSIGNED " .
			"AUTO_INCREMENT, f2 VARCHAR(100) DEFAULT '', f3 TEXT DEFAULT NULL, f4 LONGTEXT, f5 BOOL, " .
			"f6 FLOAT, f7 TIMESTAMP, f8 DATE, f9 TIME, PRIMARY KEY (f1), KEY idx1 (f2, f4)) " .
			"ENGINE InnoDB DEFAULT CHARSET=utf8");
		$db->options = new stdClass();
		$db->options->tableNamePrefix = 'p_';

		$DB = $this->getMock('stdClass', array('getHandler'));
		$DB->expects($this->any())->method('getHandler')->will($this->returnValue($db));

		DB::setMock($DB);

		$table->create();
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::drop
	 */
	public function test_drop()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$p_tableName = new ReflectionProperty('ORM_Table', 'tableName');
		$p_tableName->setAccessible(true);
		$p_tableName->setValue($table, 'foo');

		$db = $this->getMock('stdClass', array('exec'));
		$db->expects($this->once())->method('exec')->with("DROP TABLE p_foo");
		$db->options = new stdClass();
		$db->options->tableNamePrefix = 'p_';

		$DB = $this->getMock('stdClass', array('getHandler'));
		$DB->expects($this->any())->method('getHandler')->will($this->returnValue($db));

		DB::setMock($DB);

		$table->drop();
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::persist
	 */
	public function test_persist()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
		$m_hasColumns->setAccessible(true);
		$m_hasColumns->invoke($table, array(
			'id' => array(
				'type' => 'integer',
				'autoincrement' => true,
			),
			'foo' => array(
				'type' => 'string'
			)
		));

        $handler = $this->getMock('Mekras\TestDoubles\UniversalStub', array('createInsertQuery'));
        $handler->expects($this->any())->method('createInsertQuery')
            ->will($this->returnValue($this->getMockForAbstractClass('ezcQuery')));
        $DB = $this->getMock('stdClass', array('getHandler'));
        $DB->expects($this->any())->method('getHandler')
            ->will($this->returnValue($handler));
        DB::setMock($DB);

		$table->persist($this->getMockForAbstractClass('ORM_Entity', array(new Plugin)));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::update
	 */
	public function test_update()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
		$m_hasColumns->setAccessible(true);
		$m_hasColumns->invoke($table, array(
			'id' => array(
				'type' => 'integer',
				'autoincrement' => true,
			),
			'foo' => array(
				'type' => 'string'
			)
		));

        $handler = $this->getMock('Mekras\TestDoubles\UniversalStub', array('createUpdateQuery'));
        $handler->expects($this->any())->method('createUpdateQuery')
            ->will($this->returnValue($this->getMockForAbstractClass('ezcQuery')));
        $DB = $this->getMock('stdClass', array('getHandler'));
        $DB->expects($this->any())->method('getHandler')
            ->will($this->returnValue($handler));
        DB::setMock($DB);

		$table->update($this->getMockForAbstractClass('ORM_Entity', array(new Plugin)));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::delete
	 */
	public function test_delete()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
		$m_hasColumns->setAccessible(true);
		$m_hasColumns->invoke($table, array(
			'id' => array(
				'type' => 'integer',
				'autoincrement' => true,
			)
		));

        $handler = $this->getMock('Mekras\TestDoubles\UniversalStub', array('createDeleteQuery'));
        $handler->expects($this->any())->method('createDeleteQuery')
            ->will($this->returnValue($this->getMockForAbstractClass('ezcQuery')));
        $DB = $this->getMock('stdClass', array('getHandler'));
        $DB->expects($this->any())->method('getHandler')
            ->will($this->returnValue($handler));
        DB::setMock($DB);

		$table->delete($this->getMockForAbstractClass('ORM_Entity', array(new Plugin)));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::count
	 */
	public function test_count()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'createCountQuery'))->getMock();

		$db = $this->getMock('stdClass', array('fetch'));
		$db->expects($this->once())->method('fetch')->
			will($this->returnValue(array('record_count' => 123)));
		DB::setMock($db);
		$this->assertEquals(123, $table->count());

		$db = $this->getMock('stdClass', array('fetch'));
		$db->expects($this->once())->method('fetch')->will($this->returnValue(null));
		DB::setMock($db);
		$this->assertEquals(0, $table->count());
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::findAll
	 */
	public function test_findAll()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'createSelectQuery', 'loadFromQuery'))->getMock();

		$q = new ezcQuerySelect(null);
		$table->expects($this->once())->method('createSelectQuery')->with(true)->
			will($this->returnValue($q));
		$table->expects($this->once())->method('loadFromQuery')->with($q, null, 0)->
			will($this->returnValue(array(1, 2, 3)));

		$this->assertEquals(array(1, 2, 3), $table->findAll());
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::find
	 */
	public function test_find()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'getPrimaryKey', 'createSelectQuery',
			'pdoFieldType', 'loadOneFromQuery'))->getMock();

		$table->expects($this->once())->method('getPrimaryKey')->will($this->returnValue('id'));
		$q = $this->getMock('ezcQuerySelect', array('where'));
		$q->expects($this->once())->method('where');
		$table->expects($this->once())->method('createSelectQuery')->with(true)->
			will($this->returnValue($q));
		$table->expects($this->once())->method('loadOneFromQuery')->with($q)->
			will($this->returnValue('foo'));

		$this->assertEquals('foo', $table->find(123));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::createSelectQuery
	 */
	public function test_createSelectQuery()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();

		$q = new ezcQuerySelect(null);
		$handler = $this->getMock('stdClass', array('createSelectQuery'));
		$handler->expects($this->exactly(2))->method('createSelectQuery')->will($this->returnValue($q));
		$db = $this->getMock('stdClass', array('getHandler'));
		$db->expects($this->exactly(2))->method('getHandler')->will($this->returnValue($handler));
		DB::setMock($db);

		$p_ordering = new ReflectionProperty('ORM_Table', 'ordering');
		$p_ordering->setAccessible(true);
		$p_ordering->setValue($table, array(array('foo', 'DESC')));
		$table->createSelectQuery();

		$p_ordering->setValue($table, array());
		$p_columns = new ReflectionProperty('ORM_Table', 'columns');
		$p_columns->setAccessible(true);
		$p_columns->setValue($table, array('position' => array()));
		$table->createSelectQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::createCountQuery
	 */
	public function test_createCountQuery()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'getTableName'))->getMock();
		$table->expects($this->once())->method('getTableName')->will($this->returnValue('foo'));

		$q = $this->getMock('ezcQuerySelect', array('select', 'alias', 'from', 'limit'));
		$q->expects($this->once())->method('select')->will($this->returnValue($q));
		$q->expects($this->once())->method('from')->with('foo')->will($this->returnValue($q));
		$q->expects($this->once())->method('limit')->with(1);
		$handler = $this->getMock('stdClass', array('createSelectQuery'));
		$handler->expects($this->once())->method('createSelectQuery')->will($this->returnValue($q));
		$db = $this->getMock('stdClass', array('getHandler'));
		$db->expects($this->once())->method('getHandler')->will($this->returnValue($handler));
		DB::setMock($db);

		$table->createCountQuery();
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::loadFromQuery
	 */
	public function test_loadFromQuery()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'entityFactory'))->getMock();

		$q = $this->getMock('ezcQuerySelect', array('limit'));
		$q->expects($this->never())->method('limit');
		$table->loadFromQuery($q);

		$q = $this->getMock('ezcQuerySelect', array('limit'));
		$q->expects($this->once())->method('limit')->with(10, 5);
		$table->loadFromQuery($q, 10, 5);

		$db = $this->getMock('stdClass', array('fetchAll'));
		$db->expects($this->once())->method('fetchAll')->will($this->
			returnValue(array(array(1), array(1), array(1))));
		DB::setMock($db);
		$table->expects($this->exactly(3))->method('entityFactory')->with(array(1))->
			will($this->returnValue('foo'));
		$q = $this->getMock('ezcQuerySelect');
		$this->assertEquals(array('foo', 'foo', 'foo'), $table->loadFromQuery($q));

	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::loadOneFromQuery
	 */
	public function test_loadOneFromQuery()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'entityFactory'))->getMock();

		$q = $this->getMock('ezcQuerySelect', array('limit'));
		$q->expects($this->once())->method('limit')->with(1);

		$db = $this->getMock('stdClass', array('fetch'));
		$db->expects($this->once())->method('fetch')->will($this->returnValue(array(1)));
		DB::setMock($db);
		$table->expects($this->once())->method('entityFactory')->with(array(1))->
			will($this->returnValue('foo'));
		$this->assertEquals('foo', $table->loadOneFromQuery($q));

		$db = $this->getMock('stdClass', array('fetch'));
		$db->expects($this->once())->method('fetch')->will($this->returnValue(null));
		DB::setMock($db);
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'entityFactory'))->getMock();
		$table->expects($this->never())->method('entityFactory');
		$q = $this->getMock('ezcQuerySelect', array('limit'));
		$q->expects($this->once())->method('limit')->with(1);
		$this->assertNull($table->loadOneFromQuery($q));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::setTableName
	 * @covers ORM_Table::getTableName
	 */
	public function test_setgetTableName()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_setTableName = new ReflectionMethod('ORM_Table', 'setTableName');
		$m_setTableName->setAccessible(true);
		$m_getTableName = new ReflectionMethod('ORM_Table', 'getTableName');
		$m_getTableName->setAccessible(true);

		$m_setTableName->invoke($table, 'foo');
		$this->assertEquals('foo', $m_getTableName->invoke($table));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::hasColumns
	 * @covers ORM_Table::getPrimaryKey
	 */
	public function test_hasColumns()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
		$m_hasColumns->setAccessible(true);
		$m_hasColumns->invoke($table, array(
			'id' => array(
				'type' => 'integer',
				'autoincrement' => true,
			),
			'foo' => array(
				'type' => 'string'
			)
		));

		$p_columns = new ReflectionProperty('ORM_Table', 'columns');
		$p_columns->setAccessible(true);
		$this->assertEquals(2, count($p_columns->getValue($table)));

		$m_getPrimaryKey = new ReflectionMethod('ORM_Table', 'getPrimaryKey');
		$m_getPrimaryKey->setAccessible(true);
		$this->assertEquals('id', $m_getPrimaryKey->invoke($table));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::index
	 */
	public function test_index()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_index = new ReflectionMethod('ORM_Table', 'index');
		$m_index->setAccessible(true);
		$m_index->invoke($table, 'foo', array());
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::getEntityClass
	 */
	public function test_getEntityClass()
	{
		$uid = 'A' . uniqid();
		$table = $this->getMockBuilder('ORM_Table')->setMockClassName($uid . '_Table_Foo')->
			disableOriginalConstructor()->setMethods(array('setTableDefinition'))->getMock();
		$m_getEntityClass = new ReflectionMethod('ORM_Table', 'getEntityClass');
		$m_getEntityClass->setAccessible(true);

		$this->assertEquals($uid . '_Foo', $m_getEntityClass->invoke($table));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::setOrdering
	 */
	public function test_setOrdering()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_setOrdering = new ReflectionMethod('ORM_Table', 'setOrdering');
		$m_setOrdering->setAccessible(true);
		$m_setOrdering->invoke($table, 'foo', 'DESC', 'bar');

		$p_ordering = new ReflectionProperty('ORM_Table', 'ordering');
		$p_ordering->setAccessible(true);
		$this->assertEquals(array(array('foo', 'DESC'), array('bar', 'ASC')),
			$p_ordering->getValue($table));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldType
	 * @expectedException InvalidArgumentException
	 */
	public function test_pdoFieldType_not_string()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldType = new ReflectionMethod('ORM_Table', 'pdoFieldType');
		$m_pdoFieldType->setAccessible(true);

		$m_pdoFieldType->invoke($table, null);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldType
	 * @expectedException InvalidArgumentException
	 */
	public function test_pdoFieldType_invalid()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldType = new ReflectionMethod('ORM_Table', 'pdoFieldType');
		$m_pdoFieldType->setAccessible(true);

		$m_pdoFieldType->invoke($table, 'invalid');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldType
	 */
	public function test_pdoFieldType()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldType = new ReflectionMethod('ORM_Table', 'pdoFieldType');
		$m_pdoFieldType->setAccessible(true);

		$this->assertEquals(PDO::PARAM_BOOL, $m_pdoFieldType->invoke($table, 'boolean'));
		$this->assertEquals(PDO::PARAM_INT, $m_pdoFieldType->invoke($table, 'integer'));
		$this->assertNull($m_pdoFieldType->invoke($table, 'float'));
		$this->assertEquals(PDO::PARAM_STR, $m_pdoFieldType->invoke($table, 'string'));
		$this->assertEquals(PDO::PARAM_STR, $m_pdoFieldType->invoke($table, 'timestamp'));
		$this->assertEquals(PDO::PARAM_STR, $m_pdoFieldType->invoke($table, 'time'));
		$this->assertEquals(PDO::PARAM_STR, $m_pdoFieldType->invoke($table, 'date'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldValue
	 * @expectedException InvalidArgumentException
	 */
	public function test_pdoFieldValue_not_string()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldValue = new ReflectionMethod('ORM_Table', 'pdoFieldValue');
		$m_pdoFieldValue->setAccessible(true);

		$m_pdoFieldValue->invoke($table, null, null);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldValue
	 * @expectedException InvalidArgumentException
	 * @dataProvider test_pdoFieldValue_invalid_dataProvider
	 */
 	public function test_pdoFieldValue_invalid($type)
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldValue = new ReflectionMethod('ORM_Table', 'pdoFieldValue');
		$m_pdoFieldValue->setAccessible(true);

		$m_pdoFieldValue->invoke($table, true, $type);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Поставщик данных для {@link test_pdoFieldValue_invalid()}
	 */
	public function test_pdoFieldValue_invalid_dataProvider()
	{
		return array(array('timestamp'), array('date'), array('time'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::pdoFieldValue
	 */
	public function test_pdoFieldValue()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition'))->getMock();
		$m_pdoFieldValue = new ReflectionMethod('ORM_Table', 'pdoFieldValue');
		$m_pdoFieldValue->setAccessible(true);

		$datetime = new DateTime('01-02-03 12:34:56');
		$this->assertEquals('2001-02-03 12:34:56', $m_pdoFieldValue->invoke($table, $datetime,
			'timestamp'));
		$timestamp = time();
		$s = date('Y-m-d H:i:s', $timestamp);
		$this->assertEquals($s, $m_pdoFieldValue->invoke($table, $timestamp, 'timestamp'));
		$this->assertEquals('2001-02-03', $m_pdoFieldValue->invoke($table, $datetime, 'date'));
		$this->assertEquals('12:34:56', $m_pdoFieldValue->invoke($table, $datetime, 'time'));
		$this->assertNull($m_pdoFieldValue->invoke($table, null, 'time'));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Table::entityFactory
	 */
	public function test_entityFactory()
	{
		$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'getEntityClass'))->getMock();

		$table->expects($this->once())->method('getEntityClass')->
			will($this->returnValue('ORM_Table_Test_Plugin_Entity_Foo'));

		$p_plugin = new ReflectionProperty('ORM_Table', 'plugin');
		$p_plugin->setAccessible(true);
		$p_plugin->setValue($table, new Plugin);
		$p_columns = new ReflectionProperty('ORM_Table', 'columns');
		$p_columns->setAccessible(true);
		$p_columns->setValue($table, array(
			'id' => array('type' => 'integer'),
			'time' => array('type' => 'time'),
			'timestamp' => array('type' => 'timestamp'),
		));
		$m_entityFactory = new ReflectionMethod('ORM_Table', 'entityFactory');
		$m_entityFactory->setAccessible(true);

		$entity = $m_entityFactory->invoke($table,array(
			'id' => 123,
			'time' => '12:34',
			'timestamp' => '2012-02-03 13:45'
		));
		$this->assertInstanceOf('ORM_Entity', $entity);
		$this->assertInstanceOf('DateTime', $entity->time);
		$this->assertInstanceOf('DateTime', $entity->timestamp);
		$this->assertEquals('03.02.12 13:45', $entity->timestamp->format('d.m.y H:i'));
	}
	//-----------------------------------------------------------------------------
}

class ORM_Table_Test_Plugin_Entity_Foo extends ORM_Entity {}
