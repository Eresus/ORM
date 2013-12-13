<?php
/**
 * Тесты класса ORM_Field_Entities
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
class ORM_Field_EntitiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ORM_Field_Entities::getTypeName
     */
    public function testGetTypes()
    {
        $field = $this->getMockBuilder('ORM_Field_Entities')->disableOriginalConstructor()
            ->setMethods(array('none'))->getMock();
        /** @var ORM_Field_Entities $field */
        $this->assertEquals('entities', $field->getTypeName());
    }

    /**
     * @covers ORM_Field_Entities::afterEntitySave
     */
    public function testPersist()
    {
        $childEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getEntityState'))->getMock();
        $childEntity->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_NEW));

        $targetTable = $this->getMock('stdClass', array('persist'));
        $targetTable->expects($this->once())->method('persist')->with($childEntity);

        $manager = $this->getMock('ORM_Manager', array('getTableByEntityClass'));
        $manager->expects($this->once())->method('getTableByEntityClass')->with('My_Entity_Bar')
            ->will($this->returnValue($targetTable));

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->once())->method('getDriver')
            ->will($this->returnValue(new ORM_Driver_SQL($manager)));

        $field = $this->getMockBuilder('ORM_Field_Entities')->setMethods(array('none'))
            ->setConstructorArgs(array(
                $table,
                'foo',
                array('class' => 'My_Entity_Bar', 'reference' => 'foo',
                    'cascade' => array('persist'))))
            ->getMock();

        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('__get'))->getMock();
        $entity->expects($this->once())->method('__get')->with('foo')
            ->will($this->returnValue(array($childEntity)));

        /** @var ORM_Field_Entities $field */
        /** @var ORM_Entity $entity */
        $field->afterEntitySave($entity);
    }

    /**
     * @covers ORM_Field_Entities::afterEntitySave
     */
    public function testUpdate()
    {
        $childEntity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getEntityState'))->getMock();
        $childEntity->expects($this->any())->method('getEntityState')
            ->will($this->returnValue(ORM_Entity::IS_DIRTY));

        $targetTable = $this->getMock('stdClass', array('update'));
        $targetTable->expects($this->once())->method('update')->with($childEntity);

        $manager = $this->getMock('ORM_Manager', array('getTableByEntityClass'));
        $manager->expects($this->once())->method('getTableByEntityClass')->with('My_Entity_Bar')
            ->will($this->returnValue($targetTable));

        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getDriver'))->getMock();
        $table->expects($this->once())->method('getDriver')
            ->will($this->returnValue(new ORM_Driver_SQL($manager)));

        $field = $this->getMockBuilder('ORM_Field_Entities')->setMethods(array('none'))
            ->setConstructorArgs(array(
                $table,
                'foo',
                array('class' => 'My_Entity_Bar', 'reference' => 'foo',
                    'cascade' => array('update'))))
            ->getMock();

        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('__get'))->getMock();
        $entity->expects($this->once())->method('__get')->with('foo')
            ->will($this->returnValue(array($childEntity)));

        /** @var ORM_Field_Entities $field */
        /** @var ORM_Entity $entity */
        $field->afterEntitySave($entity);
    }
}

