<?php
/**
 * Базовые тесты высокого уровня
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

require_once __DIR__ . '/bootstrap.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SplQueue
     */
    private $queries;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->queries = new SplQueue();
        $queries = $this->queries;

        $db = $this->getMock('stdClass', array('exec', 'createInsertQuery'));
        $db->expects($this->any())->method('exec')->will($this->returnCallback(
            function ($sql) use ($queries)
            {
                $queries->push($sql);
            }
        ));
        $db->expects($this->any())->method('createInsertQuery')
            ->will($this->returnValue(new \Mekras\TestDoubles\UniversalStub()));
        $db->options = new stdClass;
        $db->options->tableNamePrefix = 'prf_';
        Eresus_DB::setHandler($db);
    }

    /**
     *
     */
    public function testCreateDropComplexTable()
    {
        $manager = new ORM_Manager();
        /** @var Eresus_Plugin $plugin */
        $plugin = $this->getMockForAbstractClass('Eresus_Plugin');
        $driver = $this->getMock('ORM_Driver_MySQL', array('none'), array($manager));
        /** @var ORM_Driver_MySQL $driver */
        $manager->setDriver($driver);
        include_once 'BaseTable.fixtures/Table_Foo.php';
        $table = new MyPlugin_Entity_Table_Foo($plugin, $driver);
        $driver->createTable($table);
        $driver->dropTable($table);

        $this->assertEquals(
            'CREATE TABLE prf_foo (' .
                'id INT(10) UNSIGNED AUTO_INCREMENT, ' .
                'active BOOLEAN DEFAULT 0, ' .
                'entity INTEGER UNSIGNED, ' .
                'PRIMARY KEY (id), ' .
                'KEY active_idx (active)' .
            ') ENGINE InnoDB DEFAULT CHARSET=utf8',
            $this->queries->dequeue()
        );

        $this->assertEquals(
            'CREATE TABLE prf_foo_bindings (' .
            'foo INT(10) UNSIGNED, ' .
            'bindings INT(10) UNSIGNED, ' .
            'PRIMARY KEY (foo, bindings)' .
            ') ENGINE InnoDB DEFAULT CHARSET=utf8',
            $this->queries->dequeue()
        );

        $this->assertEquals('DROP TABLE prf_foo', $this->queries->dequeue());
        $this->assertEquals('DROP TABLE prf_foo_bindings', $this->queries->dequeue());
    }

    /* *
     *
     * /
    public function testSaveBindings()
    {
        $manager = new ORM_Manager();
        $plugin = new Plugin;
        $driver = $this->getMock('ORM_Driver_MySQL', array('none'), array($manager));
        /** @var ORM_Driver_MySQL $driver * /
        $manager->setDriver($driver);
        include_once 'BaseTable.fixtures/Table_Foo.php';
        include_once 'BaseTable.fixtures/Entity_Foo.php';
        $table = new MyPlugin_Entity_Table_Foo($plugin, $driver);
        $entity = new MyPlugin_Entity_Foo($table);
        $table->persist($entity);

        $this->assertEquals(
            'CREATE TABLE prf_foo (' .
            'id INT(10) UNSIGNED AUTO_INCREMENT, ' .
            'active BOOLEAN DEFAULT 0, ' .
            'entity INTEGER UNSIGNED, ' .
            'PRIMARY KEY (id), ' .
            'KEY active_idx (active)' .
            ') ENGINE InnoDB DEFAULT CHARSET=utf8',
            $this->queries->dequeue()
        );
    }*/
}

