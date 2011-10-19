<?php
/**
 * ORM
 *
 * Модульные тесты
 *
 * @version ${product.version}
 *
 * @copyright 2011, Михаил Красильников <mihalych@vsepofigu.ru>
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
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
require_once TESTS_SRC_DIR . '/orm/classes/Driver/MySQL.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Driver_MySQL_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers ORM_Driver_MySQL::createTable
	 */
	public function test_createTable()
	{
		$driver = $this->getMock('ORM_Driver_MySQL', array('getFieldDefinition'));
		$driver->expects($this->any())->method('getFieldDefinition')->will($this->returnValue('F'));

		$handler = $this->getMock('stdClass', array('exec'));
		$handler->expects($this->once())->method('exec')->
			with('CREATE TABLE prefix_foo (f1 F, PRIMARY KEY (id), KEY idx1 (f1)) TYPE InnoDB');
		$handler->options = new stdClass;
		$handler->options->tableNamePrefix = 'prefix_';
		$db = $this->getMock('stdClass', array('getHandler'));
		$db->expects($this->once())->method('getHandler')->will($this->returnValue($handler));
		DB::setMock($db);

		$driver->createTable('foo', array('f1' => array()), 'id',
			array('idx1' => array('fields' => array('f1'))));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Driver_MySQL::dropTable
	 */
	public function test_dropTable()
	{
		$driver = new ORM_Driver_MySQL();

		$handler = $this->getMock('stdClass', array('exec'));
		$handler->expects($this->once())->method('exec')->with('DROP TABLE prefix_foo');
		$handler->options = new stdClass;
		$handler->options->tableNamePrefix = 'prefix_';
		$db = $this->getMock('stdClass', array('getHandler'));
		$db->expects($this->once())->method('getHandler')->will($this->returnValue($handler));
		DB::setMock($db);

		$driver->dropTable('foo');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Driver_MySQL::getDefinitionFor_DEFAULT
	 */
	public function test_getDefinitionFor_DEFAULT()
	{
		$method = new ReflectionMethod('ORM_Driver_MySQL', 'getDefinitionFor_DEFAULT');
		$method->setAccessible(true);
		$driver = new ORM_Driver_MySQL();

		$this->assertEquals('', $method->invoke($driver, array()));
		$this->assertEquals(' DEFAULT NULL', $method->invoke($driver, array('default' => null)));
		$this->assertEquals(' DEFAULT \'foo\'', $method->invoke($driver, array('default' => 'foo',
			'type' => 'string')));
		$this->assertEquals(' DEFAULT 123', $method->invoke($driver, array('default' => 123,
			'type' => 'int')));
	}
	//-----------------------------------------------------------------------------
}