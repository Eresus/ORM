<?php
/**
 * Модульные тесты класса ORM_Driver_MySQL
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

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Driver_MySQLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Driver_MySQL::getCreateTableDefinition
     */
    public function testGetCreateTableDefinition()
    {
        $getCreateTableDefinition
            = new ReflectionMethod('ORM_Driver_MySQL', 'getCreateTableDefinition');
        $getCreateTableDefinition->setAccessible(true);
        $driver = new ORM_Driver_MySQL();
        $this->assertEquals(
            'CREATE TABLE foo (bar, baz, pkey, idx1, idx2) ENGINE InnoDB DEFAULT CHARSET=utf8',
            $getCreateTableDefinition->invoke($driver,
                'foo', array('bar', 'baz'), 'pkey', array('idx1', 'idx2')));
    }

    /**
     * @covers ORM_Driver_MySQL_Datetime::getSqlFieldDefinition
     * @covers ORM_Driver_MySQL_Integer::getSqlFieldDefinition
     * @covers ORM_Driver_MySQL_String::getSqlFieldDefinition
     */
    public function testMySqlSpecificTypes()
    {
        $method = new ReflectionMethod('ORM_Driver_MySQL', 'getFieldDefinition');
        $method->setAccessible(true);
        $driver = new ORM_Driver_MySQL();

        $field = new ORM_Field_Datetime(array(), $this->getMock('ORM'));
        $this->assertEquals("foo DATETIME", $method->invoke($driver, 'foo', $field));

        $field = new ORM_Field_Integer(
            array('length' => 5, 'unsigned' => true, 'autoincrement' => true),
            $this->getMock('ORM'));
        $this->assertEquals("foo INT(5) UNSIGNED AUTO_INCREMENT",
            $method->invoke($driver, 'foo', $field));

        $field = new ORM_Field_String(
            array('length' => 300),
            $this->getMock('ORM'));
        $this->assertEquals("foo TEXT",
            $method->invoke($driver, 'foo', $field));
    }
}

