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
require_once TESTS_SRC_DIR . '/orm/classes/Table.php';
require_once TESTS_SRC_DIR . '/orm/classes/Helper/Ordering.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Helper_Ordering_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers ORM_Helper_Ordering::moveUp
	 */
	public function test_moveUp()
	{
		$helper = $this->getMock('ORM_Helper_Ordering', array('swap'));
		$plugin = new ORM_Helper_Ordering_Test_Plugin();
		$entity = new ORM_Helper_Ordering_Test_Plugin_Entity_Foo($plugin);
		$helper->moveUp($entity);
		$entity->position = 1;
		$p_groupBy = new ReflectionProperty('ORM_Helper_Ordering', 'groupBy');
		$p_groupBy->setAccessible(true);
		$p_groupBy->setValue($helper, array('foo'));
		$helper->moveUp($entity);
		$GLOBALS['ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo::loadOneFromQuery'] = null;
		$helper->moveUp($entity);
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Helper_Ordering::moveDown
	 */
	public function test_moveDown()
	{
		$helper = $this->getMock('ORM_Helper_Ordering', array('swap'));
		$plugin = new ORM_Helper_Ordering_Test_Plugin();
		$entity = new ORM_Helper_Ordering_Test_Plugin_Entity_Foo($plugin);
		$p_groupBy = new ReflectionProperty('ORM_Helper_Ordering', 'groupBy');
		$p_groupBy->setAccessible(true);
		$p_groupBy->setValue($helper, array('foo'));
		$helper->moveDown($entity);
		$GLOBALS['ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo::loadOneFromQuery'] = null;
		$helper->moveDown($entity);
	}
	//-----------------------------------------------------------------------------
}

class ORM_Helper_Ordering_Test_Plugin extends Plugin {}
class ORM_Helper_Ordering_Test_Plugin_Entity_Foo extends ORM_Entity {}
class ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo extends ORM_Table
{
	protected function setTableDefinition() {}
	//-----------------------------------------------------------------------------
	public function createSelectQuery($fill = true)
	{
		return new ezcQuerySelect();
	}
	//-----------------------------------------------------------------------------
	public function loadOneFromQuery(ezcQuerySelect $query)
	{
		$key = 'ORM_Helper_Ordering_Test_Plugin_Entity_Table_Foo::loadOneFromQuery';
		return array_key_exists($key, $GLOBALS) ? $GLOBALS[$key] :
			new ORM_Helper_Ordering_Test_Plugin_Entity_Foo(new ORM_Helper_Ordering_Test_Plugin);
	}
	//-----------------------------------------------------------------------------
}