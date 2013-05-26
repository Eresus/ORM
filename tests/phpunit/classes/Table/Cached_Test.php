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
require_once TESTS_SRC_DIR . '/orm/classes/Table.php';
require_once TESTS_SRC_DIR . '/orm/classes/Table/Cached.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Table_Cached_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers ORM_Table_Cached::fillCache
	 */
	public function test_fillCache()
	{
		$table = $this->getMockBuilder('ORM_Table_Cached')->disableOriginalConstructor()->
			setMethods(array('setTableDefinition', 'createSelectQuery', 'loadFromQuery'))->getMock();
		$table->expects($this->once())->method('createSelectQuery')->
			will($this->returnValue(new ezcQuerySelect()));
		$item = new stdClass();
		$item->id = 0;
		$table->expects($this->once())->method('loadFromQuery')->will($this->returnValue(array($item)));

		$m_fillCache = new ReflectionMethod('ORM_Table_Cached', 'fillCache');
		$m_fillCache->setAccessible(true);

		$m_fillCache->invoke($table);
		$m_fillCache->invoke($table);
	}
	//-----------------------------------------------------------------------------
}
