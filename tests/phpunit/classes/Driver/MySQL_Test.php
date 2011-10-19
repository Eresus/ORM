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