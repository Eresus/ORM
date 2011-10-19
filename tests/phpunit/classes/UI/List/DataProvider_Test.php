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


require_once __DIR__ . '/../../../bootstrap.php';
require_once TESTS_SRC_DIR . '/orm/classes/UI/List/DataProvider.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_UI_List_DataProvider_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers ORM_UI_List_DataProvider::__construct
	 */
	public function test_construct()
	{
		$obj = new ORM_UI_List_DataProvider(new ORM_UI_List_DataProvider_Test_Pluign(), 'foo');
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_UI_List_DataProvider::filterInclude
	 */
	public function test_filterInclude()
	{
		$provider = $this->getMockBuilder('ORM_UI_List_DataProvider')->disableOriginalConstructor()->
			setMethods(array('__none__'))->getMock();
		$p_filter = new ReflectionProperty('ORM_UI_List_DataProvider', 'filter');
		$p_filter->setAccessible(true);
		$provider->filterInclude('foo', 123);
		$provider->filterInclude('bar', 456, '<');
		$this->assertEquals(array(array('foo', 123, '='), array('bar', 456, '<')),
			$p_filter->getValue($provider));
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_UI_List_DataProvider::setFilter
	 */
	public function test_setFilter()
	{
		$provider = $this->getMockBuilder('ORM_UI_List_DataProvider')->disableOriginalConstructor()->
			setMethods(array('__none__'))->getMock();
		$m_setFilter = new ReflectionMethod('ORM_UI_List_DataProvider', 'setFilter');
		$m_setFilter->setAccessible(true);

		$p_filter = new ReflectionProperty('ORM_UI_List_DataProvider', 'filter');
		$p_filter->setAccessible(true);
		$p_filter->setValue($provider, array(
			array('foo', 123, '='),
			array('foo', array(1, 2, 3), '='),
			array('bar', 456, '<'),
		));

		$q = $this->getMock('ezcQuerySelect');
		$q->expr = $this->getMock('stdClass', array('eq', 'lt', 'lAnd', 'in'));
		$q->expr->expects($this->once())->method('eq');
		$q->expr->expects($this->once())->method('lt');
		$q->expr->expects($this->once())->method('lAnd');
		$q->expr->expects($this->once())->method('in');

		$m_setFilter->invoke($provider, $q);
	}
	//-----------------------------------------------------------------------------
}

class ORM_UI_List_DataProvider_Test_Pluign extends Plugin {};
class ORM_UI_List_DataProvider_Test_Pluign_Entity_Table_foo extends ORM_Table
{
	protected function setTableDefinition()
	{
	}
	//-----------------------------------------------------------------------------
};