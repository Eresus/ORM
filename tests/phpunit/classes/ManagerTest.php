<?php
/**
 * Тесты класса ORM_Manager
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

require_once __DIR__ . '/../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_ManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Manager::getTable
     * @expectedException InvalidArgumentException
     */
    public function testGetTableInvalidPlugin()
    {
        $orm = new ORM_Manager;
        $orm->getTable(null, 'Foo');
    }

    /**
     * @covers ORM_Manager::getTable
     */
    public function testGetTablePlugin()
    {
        $uid = uniqid();
        $this->getMockBuilder('ORM_Table')->setMockClassName("Plugin_{$uid}_Entity_Table_{$uid}")
            ->setMethods(array('setTableDefinition'))->disableOriginalConstructor()->getMock();
        $plugin = $this->getMockForAbstractClass('Eresus_Plugin', array(), 'Plugin_' . $uid);
        $orm = new ORM_Manager();
        $this->assertInstanceOf("Plugin_{$uid}_Entity_Table_{$uid}", $orm->getTable($plugin, $uid));
    }
}

