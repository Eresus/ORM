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
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        DB::setMock(null);
    }

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
        $table->__construct(new ORM_Driver_MySQL(), $plugin);
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
     * @covers ORM_Table::createSelectQuery
     */
    public function testCreateSelectQuery()
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
        $db = $this->getMock('stdClass', array('getHandler'));
        $db->expects($this->once())->method('getHandler')->will($this->returnValue($handler));
        DB::setMock($db);

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
}


