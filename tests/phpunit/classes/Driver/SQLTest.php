<?php
/**
 * Модульные тесты ORM_Driver_SQL
 *
 * @version ${product.version}
 *
 * @copyright 2013, Михаил Красильников <m.krasilnikov@yandex.ru>
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

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Driver_SQLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Driver_SQL::getCreateTableDefinition
     */
    public function testGetCreateTableDefinition()
    {
        $getCreateTableDefinition
            = new ReflectionMethod('ORM_Driver_SQL', 'getCreateTableDefinition');
        $getCreateTableDefinition->setAccessible(true);
        $driver = new ORM_Driver_SQL(new ORM_Manager);
        $this->assertEquals('CREATE TABLE foo (bar, baz, pkey, idx1, idx2)',
            $getCreateTableDefinition->invoke($driver,
                'foo', array('bar', 'baz'), 'pkey', array('idx1', 'idx2')));
    }

    /**
     * @covers ORM_Driver_SQL::getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $getFieldDefinition = new ReflectionMethod('ORM_Driver_SQL', 'getFieldDefinition');
        $getFieldDefinition->setAccessible(true);
        $driver = new ORM_Driver_SQL(new ORM_Manager);
        $field = $this->getMockBuilder('ORM_Field_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getTypeName', 'getSqlFieldDefinition', 'hasParam', 'getParam',
                'getPdoType'))->getMock();
        $field->expects($this->any())->method('getSqlFieldDefinition')
            ->will($this->returnArgument(0));
        $field->expects($this->any())->method('hasParam')
            ->with('default')->will($this->returnValue(true));
        $type = new stdClass();
        $field->expects($this->any())->method('getPdoType')->will(
            $this->returnCallback(function () use ($type) { return $type->value; }));
        $value = new stdClass();
        $field->expects($this->any())->method('getParam')->with('default')
            ->will($this->returnCallback(function () use ($value) { return $value->value; }));

        $type->value = null;
        $value->value = null;
        $this->assertEquals(' DEFAULT NULL', $getFieldDefinition->invoke($driver, $field));

        $type->value = PDO::PARAM_STR;
        $value->value = 'bar';
        $this->assertEquals(" DEFAULT 'bar'", $getFieldDefinition->invoke($driver, $field));

        $type->value = PDO::PARAM_BOOL;
        $value->value = false;
        $this->assertEquals(' DEFAULT 0', $getFieldDefinition->invoke($driver, $field));

        $type->value = PDO::PARAM_INT;
        $value->value = 123;
        $this->assertEquals(' DEFAULT 123', $getFieldDefinition->invoke($driver, $field));
    }

    /**
     * @covers ORM_Driver_SQL::getPrimaryKeyDefinition
     */
    public function testGetPrimaryKeyDefinition()
    {
        $getPrimaryKeyDefinition
            = new ReflectionMethod('ORM_Driver_SQL', 'getPrimaryKeyDefinition');
        $getPrimaryKeyDefinition->setAccessible(true);
        $driver = new ORM_Driver_SQL(new ORM_Manager);
        $this->assertEquals('PRIMARY KEY (foo)', $getPrimaryKeyDefinition->invoke($driver, 'foo'));
    }

    /**
     * @covers ORM_Driver_SQL::getIndexDefinition
     */
    public function testGetIndexDefinition()
    {
        $getIndexDefinition
            = new ReflectionMethod('ORM_Driver_SQL', 'getIndexDefinition');
        $getIndexDefinition->setAccessible(true);
        $driver = new ORM_Driver_SQL(new ORM_Manager);
        $this->assertEquals('KEY foo (bar, baz)',
            $getIndexDefinition->invoke($driver, 'foo', array('fields' => array('bar', 'baz'))));
    }

    /**
     * @covers ORM_Driver_SQL::getDriverFieldType
     */
    public function testGetDriverFieldType()
    {
        $getDriverFieldType
            = new ReflectionMethod('ORM_Driver_SQL', 'getDriverFieldType');
        $getDriverFieldType->setAccessible(true);

        $driver = new ORM_Driver_SQL(new ORM_Manager);

        $field = $this->getMockBuilder('ORM_Field_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getTypeName'))->setMockClassName('ORM_Field_Foo')->getMock();
        $this->assertInstanceOf('ORM_Field_Foo',
            $getDriverFieldType->invoke($driver, $field));

        $field = $this->getMockBuilder('ORM_Field_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getTypeName'))->setMockClassName('Foo_Field_Bar')->getMock();
        $this->getMockBuilder('ORM_Driver_SQL_Field')->disableOriginalConstructor()
            ->setMethods(array('getSqlFieldDefinition'))
            ->setMockClassName('Foo_Driver_SQL_Bar')->getMock();
        $this->assertInstanceOf('Foo_Driver_SQL_Bar',
            $getDriverFieldType->invoke($driver, $field));
    }
}

