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
require_once TESTS_SRC_DIR . '/orm/classes/Driver/SQL.php';

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
        $driver = new ORM_Driver_SQL();
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
        $driver = new ORM_Driver_SQL();
        $field = $this->getMockBuilder('ORM_Field_Abstract')->disableOriginalConstructor()
            ->setMethods(array('getSqlFieldDefinition'))->getMock();
        $field->expects($this->once())->method('getSqlFieldDefinition')
            ->will($this->returnArgument(0));
        $this->assertEquals('foo', $getFieldDefinition->invoke($driver, 'foo', $field));
    }

    /**
     * @covers ORM_Driver_SQL::getPrimaryKeyDefinition
     */
    public function testGetPrimaryKeyDefinition()
    {
        $getPrimaryKeyDefinition
            = new ReflectionMethod('ORM_Driver_SQL', 'getPrimaryKeyDefinition');
        $getPrimaryKeyDefinition->setAccessible(true);
        $driver = new ORM_Driver_SQL();
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
        $driver = new ORM_Driver_SQL();
        $this->assertEquals('KEY foo (bar, baz)',
            $getIndexDefinition->invoke($driver, 'foo', array('fields' => array('bar', 'baz'))));
    }
}

