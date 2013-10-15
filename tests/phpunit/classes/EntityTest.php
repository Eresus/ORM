<?php
/**
 * Тесты класса ORM_Entity
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

use Mekras\TestDoubles\UniversalStub;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_EntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Entity::__construct
     * @covers ORM_Entity::getPdoValue
     * @covers ORM_Entity::setPdoValue
     * @covers ORM_Entity::__get
     * @covers ORM_Entity::__set
     */
    public function testBasicUsage()
    {
        $dbRecord = array('foo' => 'bar');

        $field = $this->getMock('stdClass', array('isVirtual', 'pdo2orm', 'orm2pdo'));
        $field->expects($this->any())->method('isVirtual')->will($this->returnValue(false));
        $field->expects($this->any())->method('pdo2orm')->will($this->returnArgument(0));
        $field->expects($this->any())->method('orm2pdo')->will($this->returnArgument(0));

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getColumns'))->getMock();
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array(
            'foo' => $field
        )));

        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('none'))->getMock();
        $tableProp = new ReflectionProperty('ORM_Entity', 'table');
        $tableProp->setAccessible(true);
        $tableProp->setValue($entity, $table);
        $pdoValues = new ReflectionProperty('ORM_Entity', 'pdoValues');
        $pdoValues->setAccessible(true);
        $pdoValues->setValue($entity, $dbRecord);

        /** @var ORM_Entity $entity */
        $this->assertEquals('bar', $entity->foo);
        $entity->foo = 'baz';
        $this->assertEquals('baz', $entity->foo);
    }

    /**
     * @covers ORM_Entity::__get
     * @covers ORM_Entity::__set
     */
    public function testNoExistentProps()
    {
        /*$table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getColumns'))->getMock();
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array()));*/

        $entity = $this->getMockForAbstractClass('ORM_Entity', array(array()));

        /** @var ORM_Entity $entity */
        $entity->bar = 'baz';
        $this->assertEquals('baz', $entity->bar);
    }

    /* *
     * @covers ORM_Entity::getTable
     * /
    public function testGetTable()
    {
        $table = $this->getMockBuilder('ORM_Table')->setMethods(array('setTableDefinition'))
            ->disableOriginalConstructor()->getMock();
        $entity = $this->getMockForAbstractClass('ORM_Entity', array($table),
            'ORM_Entity_Test__Entity_GetTable');
        /** @var ORM_Entity $entity * /
        $this->assertSame($table, $entity->getTable());
    }*/

    /**
     * @covers ORM_Entity::getEntityState
     * @covers ORM_Entity::setEntityState
     * @covers ORM_Entity::afterSave
     * @covers ORM_Entity::__set
     * @covers ORM_Entity::afterDelete
     */
    public function testEntityState()
    {
        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getTable'))->getMock();
        $entity->expects($this->any())->method('getTable')
            ->will($this->returnValue(new UniversalStub()));
        /** @var ORM_Entity $entity */
        $this->assertEquals(ORM_Entity::IS_NEW, $entity->getEntityState());
        $entity->afterSave();
        $this->assertEquals(ORM_Entity::IS_PERSISTENT, $entity->getEntityState());
        $entity->foo = 'bar';
        $this->assertEquals(ORM_Entity::IS_DIRTY, $entity->getEntityState());
        $entity->afterDelete();
        $this->assertEquals(ORM_Entity::IS_DELETED, $entity->getEntityState());
    }

    /* *
     * @covers ORM_Entity::getPrimaryKey
     * /
    public function testGetPrimaryKey()
    {
        $plugin = $this->getMockBuilder('Plugin')->disableOriginalConstructor()
            ->setMockClassName('testGetPrimaryKey')->getMock();
        $entity = $this->getMockBuilder('ORM_Entity')->setMethods(array('_'))
            ->setMockClassName('testGetPrimaryKey_Entity_Bar')
            ->setConstructorArgs(array($plugin))->getMock();
        $attrs = new ReflectionProperty('ORM_Entity', 'attrs');
        $attrs->setAccessible(true);
        $attrs->setValue($entity, array('id' => 123));

        $legacyKernel = new stdClass();
        $legacyKernel->plugins = $this->getMock('stdClass', array('load'));
        $legacyKernel->plugins->expects($this->any())->method('load')
            ->will($this->returnValue($plugin));
        $app = $this->getMock('stdClass', array('getLegacyKernel'));
        $app->expects($this->any())->method('getLegacyKernel')
            ->will($this->returnValue($legacyKernel));
        $kernel = $this->getMock('stdClass', array('app'));
        $kernel->expects($this->any())->method('app')->will($this->returnValue($app));
        Eresus_Kernel::setMock($kernel);

        $table = $this->getMockBuilder('stdClass')
            ->setMethods(array('getPrimaryKey', 'getColumns'))
            ->setMockClassName('testGetPrimaryKey_Entity_Table_Bar')->getMock();
        $table->expects($this->any())->method('getPrimaryKey')->will($this->returnValue('id'));
        $table->expects($this->any())->method('getColumns')->will($this->returnValue(array()));
        $tables = new ReflectionProperty('ORM', 'tables');
        $tables->setAccessible(true);
        $tables->setValue(array('testGetPrimaryKey_Entity_Table_Bar' => $table));

        /** @var ORM_Entity $entity * /
        $this->assertEquals(123, $entity->getPrimaryKey());
    }

    /**
     * @covers ORM_Entity::getTableByEntityClass
     * /
    public function testGetTableByEntityClass()
    {
        $getTableByEntityClass = new ReflectionMethod('ORM_Entity', 'getTableByEntityClass');
        $getTableByEntityClass->setAccessible(true);

        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->getMock();

        $plugin = $this->getMockBuilder('Plugin')->disableOriginalConstructor()
            ->setMockClassName('Foo')->getMock();
        $legacyKernel = new stdClass();
        $legacyKernel->plugins = $this->getMock('stdClass', array('load'));
        $legacyKernel->plugins->expects($this->any())->method('load')
            ->will($this->returnValue($plugin));
        $app = $this->getMock('stdClass', array('getLegacyKernel'));
        $app->expects($this->any())->method('getLegacyKernel')
            ->will($this->returnValue($legacyKernel));
        $kernel = $this->getMock('stdClass', array('app'));
        $kernel->expects($this->any())->method('app')->will($this->returnValue($app));
        Eresus_Kernel::setMock($kernel);

        $this->getMockBuilder('stdClass')->setMockClassName('Foo_Entity_Table_Bar')->getMock();
        $this->assertInstanceOf('Foo_Entity_Table_Bar',
            $getTableByEntityClass->invoke($entity, 'Foo_Entity_Bar'));
    }

    /**
     * @covers ORM_Entity::__get
     * @covers ORM_Entity::__set
     * /
    public function testGettersCache()
    {
        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getFoo', 'setPdoValue'))->getMock();
        $entity->expects($this->exactly(2))->method('getFoo')->will($this->returnValue('bar'));
        $this->assertEquals('bar', $entity->foo);
        $this->assertEquals('bar', $entity->foo);
        $entity->foo = 'baz';
        $this->assertEquals('bar', $entity->foo);
    }*/
}

