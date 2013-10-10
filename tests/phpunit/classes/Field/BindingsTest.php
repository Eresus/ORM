<?php
/**
 * Тесты класса ORM_Field_Bindings
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
class ORM_Field_BindingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Field_Bindings::orm2pdo
     * @expectedException InvalidArgumentException
     */
    public function testOrm2pdoBadArg()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition'))->getMock();
        /** @var ORM_Table $table */
        $field = new ORM_Field_Bindings($table, 'foo', array('class' => 'Foo'));

        $field->orm2pdo(null);
    }

    /**
     * @covers ORM_Field_Bindings::orm2pdo
     */
    public function testOrm2pdo()
    {
        $e1 = new stdClass();
        $e2 = new stdClass();

        $targetTable = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'find'))->getMock();
        $targetTable->expects($this->any())->method('find')->will($this->returnValueMap(array(
            array(123, $e1),
            array(456, $e2),
        )));

        $manager = $this->getMock('ORM_Manager', array('getTableByEntityClass'));
        $manager->expects($this->any())->method('getTableByEntityClass')->with('Foo_Entity_Bar')
            ->will($this->returnValue($targetTable));
        /** @var ORM_Manager $manager */
        $driver = new ORM_Driver_SQL($manager);

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getDriver')->will($this->returnValue($driver));
        /** @var ORM_Table $table */
        $field = new ORM_Field_Bindings($table, 'foo', array('class' => 'Foo_Entity_Bar'));

        $result = $field->orm2pdo(array(123, 456));
        $this->assertEquals(array($e1, $e2), $result);
    }
}

