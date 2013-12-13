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

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Helper_OrderingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Helper_Ordering::groupBy
     */
    public function testGroupBy()
    {
        $helper = new ORM_Helper_Ordering();
        $helper->groupBy('foo', 'ASC', 'bar', 'DESC');

        $groupBy = new ReflectionProperty('ORM_Helper_Ordering', 'groupBy');
        $groupBy->setAccessible(true);
        $this->assertEquals(array('foo', 'ASC', 'bar', 'DESC'), $groupBy->getValue($helper));
    }

    /* *
     * @covers ORM_Helper_Ordering::moveUp
     * /
    public function testMoveUp()
    {
        $helper = $this->getMock('ORM_Helper_Ordering', array('swap'));
        $entity = $this->createTestEntity();
        $helper->moveUp($entity);
        $entity->position = 1;
        $groupBy = new ReflectionProperty('ORM_Helper_Ordering', 'groupBy');
        $groupBy->setAccessible(true);
        $groupBy->setValue($helper, array('foo'));
        $helper->moveUp($entity);
        $GLOBALS['ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo::loadOneFromQuery'] = null;
        $helper->moveUp($entity);
    }

    /**
     * @covers ORM_Helper_Ordering::moveDown
     * /
    public function testMoveDown()
    {
        $helper = $this->getMock('ORM_Helper_Ordering', array('swap'));
        $entity = $this->createTestEntity();
        $groupBy = new ReflectionProperty('ORM_Helper_Ordering', 'groupBy');
        $groupBy->setAccessible(true);
        $groupBy->setValue($helper, array('foo'));
        $helper->moveDown($entity);
        $GLOBALS['ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo::loadOneFromQuery'] = null;
        $helper->moveDown($entity);
    }

    private function createTestEntity()
    {
        $table = $this->getMockBuilder('ORM_Table')->setMethods(array('setTableDefinition'))
            ->disableOriginalConstructor()->getMock();

        $entityClass = 'ORM_Helper_Ordering_Test_Plugin_Entity_Foo';

        if (!class_exists($entityClass))
        {
            return $this->getMockBuilder('ORM_Entity')->setMethods(array('none'))
                ->setMockClassName($entityClass)->setConstructorArgs(array($table))->getMock();
        }
        return new $entityClass($table);
    }*/
}
