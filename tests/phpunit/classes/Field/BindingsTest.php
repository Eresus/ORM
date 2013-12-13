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

    /**
     * @covers ORM_Field_Bindings::afterEntitySave
     */
    public function testAfterEntitySave()
    {
        $table = $this->getMockBuilder('ORM_Table')->disableOriginalConstructor()
            ->setMethods(array('setTableDefinition', 'getName', 'getDriver'))->getMock();
        $table->expects($this->any())->method('getName')->will($this->returnValue('main'));
        $table->expects($this->any())->method('getDriver')
            ->will($this->returnValue(new ORM_Driver_SQL(new ORM_Manager())));
        /** @var ORM_Table $table */
        $field = $this->getMockBuilder('ORM_Field_Bindings')
            ->setConstructorArgs(array($table, 'slave', array('class' => 'My_Entity_Main')))
            ->setMethods(array('getBindingsTableName', 'getTable'))->getMock();

        $e1 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getPrimaryKey'))->getMock();
        $e1->expects($this->any())->method('getPrimaryKey')->will($this->returnValue(1));
        $e2 = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getPrimaryKey'))->getMock();
        $e1->expects($this->any())->method('getPrimaryKey')->will($this->returnValue(2));

        $entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()
            ->setMethods(array('getTable', '__get', 'getPrimaryKey'))->getMock();
        $entity->expects($this->any())->method('getTable')->will($this->returnValue($table));
        $entity->expects($this->any())->method('__get')->will($this->returnValueMap(array(
            array('slave', array($e1, $e2)),
        )));
        $entity->expects($this->any())->method('getPrimaryKey')->will($this->returnValue(123));

        $db = $this->getMock('stdClass', array('createSelectQuery', 'createDeleteQuery',
            'createInsertQuery'));
        $qs = $this->getMock('\Mekras\TestDoubles\UniversalStub', array('fetchAll'));
        $qs->expects($this->once())->method('fetchAll')->will($this->returnValue(array(
            array('main' => 1, 'slave' => 10),
            array('main' => 3, 'slave' => 30)
        )));
        $db->expects($this->once())->method('createSelectQuery')->will($this->returnValue($qs));

        $dq = $this->getMock('\Mekras\TestDoubles\UniversalStub', array('bindValue'));
        $dq->expects($this->any())->method('bindValue')->will($this->returnArgument(0));
        $dq->expr = $this->getMock('\Mekras\TestDoubles\UniversalStub', array('eq', 'lAnd', 'lOr'));
        $dq->expr->expects($this->any())->method('eq')->will(
            $this->returnCallback(function ($a, $b) { return "$a = $b"; }));
        $dq->expr->expects($this->any())->method('lAnd')->will(
            $this->returnCallback(function () { return implode(' AND ', func_get_args()); }));
        $dq->expr->expects($this->once())->method('lOr')->with(array('main = 3 AND slave = 30'))
            ->will($this->returnSelf());
        $db->expects($this->once())->method('createDeleteQuery')->will($this->returnValue($dq));

        $iq = $this->getMock('\Mekras\TestDoubles\UniversalStub', array('bindValue', 'set'));
        $iq->expects($this->any())->method('bindValue')->will($this->returnArgument(0));
        $iqData = new SplQueue();
        $iq->expects($this->exactly(2))->method('set')->will($this->returnCallback(
            function ($a, $b) use ($iqData)
            {
                $iqData->enqueue("$a = $b");
            }
        ));
        $db->expects($this->once())->method('createInsertQuery')->will($this->returnValue($iq));

        Eresus_DB::setHandler($db);

        /** @var ORM_Field_Bindings $field */
        $field->afterEntitySave($entity);

        $this->assertEquals('main = 123', $iqData->dequeue());
        $this->assertEquals('slave = ', $iqData->dequeue());
    }
}

