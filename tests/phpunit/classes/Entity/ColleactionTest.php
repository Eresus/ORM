<?php
/**
 * Модульные тесты класса ORM_Entity_Collection
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
class ORM_Entity_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Entity_Collection::contains
     */
    public function testContains()
    {
        $collection = new ORM_Entity_Collection();

        $newEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('none'))->getMock();
        $collection->attach($newEntity);

        $deletedEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getEntityState'))->getMock();
        $deletedEntity->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_DELETED));
        $collection->attach($deletedEntity);

        $this->assertTrue($collection->contains($newEntity));
        $this->assertContains($newEntity, $collection);
        $this->assertFalse($collection->contains($deletedEntity));
        $this->assertNotContains($deletedEntity, $collection);
    }

    /**
     * @covers ORM_Entity_Collection::contains
     */
    public function testContainsByKey()
    {
        $collection = new ORM_Entity_Collection();

        $entity1 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getPrimaryKey'))->getMock();
        $entity1->expects($this->any())->method('getPrimaryKey')->will($this->returnValue(1));
        $collection->attach($entity1);

        $entity2 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getPrimaryKey'))->getMock();
        $entity2->expects($this->any())->method('getPrimaryKey')->will($this->returnValue(2));
        $collection->attach($entity2);


        $this->assertTrue($collection->contains(1));
        $this->assertFalse($collection->contains(3));
        $this->assertTrue($collection->contains(2));
    }

    /**
     * @covers ORM_Entity_Collection::next
     * @covers ORM_Entity_Collection::rewind
     * @covers ORM_Entity_Collection::isCurrentEntityDeleted
     */
    public function testRewindNext()
    {
        $collection = new ORM_Entity_Collection();

        $deletedEntity1 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getEntityState'))->getMock();
        $deletedEntity1->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_DELETED));
        $collection->attach($deletedEntity1);

        $newEntity1 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('none'))->getMock();
        $collection->attach($newEntity1);

        $deletedEntity2 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getEntityState'))->getMock();
        $deletedEntity2->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_DELETED));
        $collection->attach($deletedEntity2);

        $newEntity2 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('none'))->getMock();
        $collection->attach($newEntity2);

        $collection->rewind();
        $this->assertSame($newEntity1, $collection->current());
        $collection->next();
        $this->assertSame($newEntity2, $collection->current());
    }

    /**
     * @covers ORM_Entity_Collection::count
     */
    public function testCount()
    {
        $collection = new ORM_Entity_Collection();

        $newEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('none'))->getMock();
        $collection->attach($newEntity);

        $deletedEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            setMethods(array('getEntityState'))->getMock();
        $deletedEntity->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_DELETED));
        $collection->attach($deletedEntity);

        $this->assertCount(1, $collection);
    }

    /**
     * @covers ORM_Entity_Collection::clear
     */
    public function testClear()
    {
        $collection = new ORM_Entity_Collection();

        $collection->attach($this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            getMock());
        $collection->attach($this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            getMock());
        $collection->attach($this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
            getMock());
        $collection->clear();

        $this->assertCount(0, $collection);
    }

    /**
     * @covers ORM_Entity_Collection::offsetGet
     */
    public function testNumericIndexes()
    {
        $collection = new ORM_Entity_Collection();

        $e1 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->getMock();
        $collection->attach($e1);
        $e2 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->getMock();
        $collection->attach($e2);
        $e3 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->getMock();
        $collection->attach($e3);

        $this->assertSame($e2, $collection[1]);
        $this->assertSame($e3, $collection[2]);
        $this->assertSame($e1, $collection[0]);
    }
}

