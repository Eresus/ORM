<?php
/**
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
 */


require_once __DIR__ . '/../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_TableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Table::__construct
     */
    public function testConstruct()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition'))->getMock();
        $table->expects($this->once())->method('setTableDefinition');
        /** @var Plugin $plugin */
        $plugin = $this->getMockBuilder('Eresus_Plugin')->disableOriginalConstructor()->getMock();
        /** @var ORM_Table $table */
        $table->__construct($plugin, new ORM_Driver_MySQL(new ORM_Manager()));
    }

    /**
     * @covers ORM_Table::hasColumns
     * @expectedException InvalidArgumentException
     */
    public function testHasColumnsBadName()
    {
        $driver = new ORM_Driver_SQL(new ORM_Manager());
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        $hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
        $hasColumns->setAccessible(true);
        $hasColumns->invoke($table, array(null));
    }

    /**
     * @covers ORM_Table::hasColumns
     * @expectedException InvalidArgumentException
     */
    public function testHasColumnsBadDefinition()
    {
        $driver = new ORM_Driver_SQL(new ORM_Manager());
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        $hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
        $hasColumns->setAccessible(true);
        $hasColumns->invoke($table, array('foo' => null));
    }

    /**
     * @covers ORM_Table::hasColumns
     * @expectedException InvalidArgumentException
     */
    public function testHasColumnsNoType()
    {
        $driver = new ORM_Driver_SQL(new ORM_Manager());
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        $hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
        $hasColumns->setAccessible(true);
        $hasColumns->invoke($table, array('foo' => array()));
    }

    /**
     * @covers ORM_Table::hasColumns
     * @expectedException InvalidArgumentException
     */
    public function testHasColumnsInvalidType()
    {
        $driver = new ORM_Driver_SQL(new ORM_Manager());
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        $hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
        $hasColumns->setAccessible(true);
        $hasColumns->invoke($table, array('foo' => array('type' => 'bar')));
    }

    /**
     * @covers ORM_Table::hasColumns
     * @covers ORM_Table::getPrimaryKey
     */
    public function testHasColumns()
    {
        $driver = new ORM_Driver_SQL(new ORM_Manager());
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        $hasColumns = new ReflectionMethod('ORM_Table', 'hasColumns');
        $hasColumns->setAccessible(true);
        $hasColumns->invoke($table, array(
            'foo' => array('type' => 'integer'),
            'bar' => array('type' => 'integer')
        ));
        /** @var ORM_Table $table */
        $this->assertEquals('foo', $table->getPrimaryKey());
    }

    /**
     * @covers ORM_Table::count
     */
    public function testCount()
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

    /**
     * @covers ORM_Table::findAll
     */
    public function testFindAll()
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

    /**
     * @covers ORM_Table::find
     */
    public function testFind()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getColumns', 'getPrimaryKey',
                'createSelectQuery', 'loadOneFromQuery'))->getMock();

        $table->expects($this->once())->method('getColumns')->will($this->returnValue(array(
            'id' => new ORM_Field_Integer($table, 'id', array())
        )));

        $table->expects($this->once())->method('getPrimaryKey')->will($this->returnValue('id'));

        $q = new ezcQuerySelect(null);
        $table->expects($this->once())->method('createSelectQuery')->with(true)
            ->will($this->returnValue($q));

        $entity = new stdClass();
        $table->expects($this->once())->method('loadOneFromQuery')->with($q)
            ->will($this->returnValue($entity));

        /** @var ORM_Table $table */
        $this->assertSame($entity, $table->find(1));
        // Проверяем реестр
        $this->assertSame($entity, $table->find(1));
    }

    /**
     * @covers ORM_Table::createSelectQuery
     */
    public function testCreateSelectQuery()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition'))->getMock();

        $q = new ezcQuerySelect(null);
        $handler = $this->getMock('stdClass', array('createSelectQuery'));
        $handler->expects($this->exactly(2))->method('createSelectQuery')
            ->will($this->returnValue($q));
        DB::setHandler($handler);

        $ordering = new ReflectionProperty('ORM_Table', 'ordering');
        $ordering->setAccessible(true);
        $ordering->setValue($table, array(array('foo', 'DESC')));
        $table->createSelectQuery();

        $ordering->setValue($table, array());
        $columns = new ReflectionProperty('ORM_Table', 'columns');
        $columns->setAccessible(true);
        $columns->setValue($table,
            array('position' => new ORM_Field_Integer($table, 'position', array())));
        $table->createSelectQuery();
    }

    /**
     * @covers ORM_Table::createCountQuery
     */
    public function testCreateCountQuery()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getName'))->getMock();
        $table->expects($this->once())->method('getName')->will($this->returnValue('foo'));

        $q = $this->getMock('ezcQuerySelect', array('select', 'alias', 'from', 'limit'));
        $q->expects($this->once())->method('select')->will($this->returnValue($q));
        $q->expects($this->once())->method('from')->with('foo')->will($this->returnValue($q));
        $q->expects($this->once())->method('limit')->with(1);
        $handler = $this->getMock('stdClass', array('createSelectQuery'));
        $handler->expects($this->once())->method('createSelectQuery')->will($this->returnValue($q));
        DB::setHandler($handler);

        $table->createCountQuery();
    }

    /**
     * @covers ORM_Table::loadFromQuery
     */
    public function testLoadFromQuery()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'entityFactory'))->getMock();

        $q = $this->getMock('ezcQuerySelect', array('limit'));
        $q->expects($this->never())->method('limit');
        /** @var ORM_Table $table */
        $table->loadFromQuery($q);

        $q = $this->getMock('ezcQuerySelect', array('limit'));
        $q->expects($this->once())->method('limit')->with(10, 5);
        $table->loadFromQuery($q, 10, 5);

        $db = $this->getMock('stdClass', array('fetchAll'));
        $db->expects($this->once())->method('fetchAll')->will($this->
            returnValue(array(array(1), array(1), array(1))));
        DB::setMock($db);
        $self = $this;
        /** @var PHPUnit_Framework_MockObject_MockObject $table */
        $table->expects($this->exactly(3))->method('entityFactory')->with(array(1))->
            will($this->returnCallback(
                function () use ($self)
                {
                    $o = $self->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
                        ->setMethods(array('none'))->getMock();
                    return $o;
                }
            ));
        $q = $this->getMock('ezcQuerySelect');
        /** @var ORM_Table $table */
        $result = $table->loadFromQuery($q);
        $this->assertCount(3, $result);
    }

    /**
     * @covers ORM_Table::loadOneFromQuery
     */
    public function testLoadOneFromQuery()
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

    /**
     * @covers ORM_Table::setTableName
     * @covers ORM_Table::getName
     */
    public function testSetGetTableName()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition'))->getMock();
        $setTableName = new ReflectionMethod('ORM_Table', 'setTableName');
        $setTableName->setAccessible(true);

        $setTableName->invoke($table, 'foo');
        /** @var ORM_Table $table */
        $this->assertEquals('foo', $table->getName());
    }

    /**
     * @covers ORM_Table::index
     */
    public function testIndex()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition'))->getMock();
        $m_index = new ReflectionMethod('ORM_Table', 'index');
        $m_index->setAccessible(true);
        $m_index->invoke($table, 'foo', array());
    }

    /**
     * @covers ORM_Table::getEntityClass
     */
    public function testGetEntityClass()
    {
        $uid = 'A' . uniqid();
        $table = $this->getMockBuilder('ORM_Table')->setMockClassName($uid . '_Table_Foo')->
            disableOriginalConstructor()->setMethods(array('setTableDefinition'))->getMock();
        /** @var ORM_Table $table */
        $this->assertEquals($uid . '_Foo', $table->getEntityClass());
    }

    /**
     * @covers ORM_Table::setOrdering
     */
    public function testSetOrdering()
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

    /**
     * @covers ORM_Table::findAllBy
     * @expectedException LogicException
     */
    public function testFindAllByUnknownField()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getColumns', 'createSelectQuery'))->getMock();
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array()));
        /** @var ORM_Table $table */
        $table->findAllBy(array('foo' => 'bar'));
    }

    /**
     * @covers ORM_Table::findAllBy
     * @expectedException LogicException
     */
    public function testFindAllByUnusableField()
    {
        $field = $this->getMockBuilder('ORM_Field_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getTypeName', 'canBeUsedInWhere'))->getMock();
        $field->expects($this->any())->method('canBeUsedInWhere')->will($this->returnValue(false));

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getColumns', 'createSelectQuery'))->getMock();
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array(
            'foo' => $field
        )));
        /** @var ORM_Table $table */
        $table->findAllBy(array('foo' => 'bar'));
    }

    /**
     * @covers ORM_Table::findAllBy
     */
    public function testFindAllBy()
    {
        $field = $this->getMock('stdClass', array('canBeUsedInWhere', 'orm2pdo', 'getPdoType'));
        $field->expects($this->any())->method('canBeUsedInWhere')->will($this->returnValue(true));
        $field->expects($this->once())->method('orm2pdo')->with('bar')
            ->will($this->returnValue('BAR'));
        $field->expects($this->any())->method('getPdoType')
            ->will($this->returnValue(PDO::PARAM_STR));

        $q = $this->getMock('ezcQuerySelect', array('bindValue', 'where'));
        $q->expects($this->once())->method('bindValue')->with('BAR', ':foo', PDO::PARAM_STR)
            ->will($this->returnValue("'BAR'"));
        $q->expects($this->once())->method('where')->with("foo = 'BAR'");
        $q->expr = $this->getMock('stdClass', array('eq', 'lAnd'));
        $q->expr->expects($this->once())->method('eq')->with('foo', "'BAR'")
            ->will($this->returnValue("foo = 'BAR'"));
        $q->expr->expects($this->once())->method('lAnd')->will($this->returnCallback(
            function ($values)
            {
                return implode(' AND ', $values);
            }
        ));

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()->
            setMethods(array('setTableDefinition', 'getColumns', 'createSelectQuery',
                'loadFromQuery'))->getMock();
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array(
            'foo' => $field
        )));
        $table->expects($this->once())->method('createSelectQuery')->will($this->returnValue($q));
        $table->expects($this->once())->method('loadFromQuery')->with($q);
        /** @var ORM_Table $table */
        $table->findAllBy(array('foo' => 'bar'));
    }
}

