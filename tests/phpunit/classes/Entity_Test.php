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


require_once __DIR__ . '/../bootstrap.php';
require_once TESTS_SRC_DIR . '/orm/classes/Entity.php';

/**
 * @package ORM
 * @subpackage Tests
 */
class ORM_Entity_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers ORM_Entity::__construct
	 * @covers ORM_Entity::getProperty
	 * @covers ORM_Entity::setProperty
	 * @covers ORM_Entity::__get
	 * @covers ORM_Entity::__set
	 */
	public function test_overview()
	{
		$entity = $this->getMockBuilder('ORM_Entity')->disableOriginalConstructor()->
			setMethods(array('getFoo', 'setFoo'))->getMock();
		$entity->expects($this->once())->method('getFoo')->will($this->returnValue('baz'));
		$entity->expects($this->once())->method('setFoo')->with('baz');
		$plugin = new Plugin;
		$attrs = array('foo' => 'bar');
		$entity->__construct($plugin, $attrs);

		$p_plugin = new ReflectionProperty('ORM_Entity', 'plugin');
		$p_plugin->setAccessible(true);
		$this->assertSame($plugin, $p_plugin->getValue($entity));

		$p_attrs = new ReflectionProperty('ORM_Entity', 'attrs');
		$p_attrs->setAccessible(true);
		$this->assertEquals($attrs, $p_attrs->getValue($entity));

		$this->assertEquals('bar', $entity->getProperty('foo'));
		$this->assertNull($entity->getProperty('bar'));
		$entity->setProperty('bar', 'foo');
		$this->assertEquals('foo', $entity->getProperty('bar'));

		$this->assertEquals('baz', $entity->foo);
		$entity->foo = 'baz';
		$this->assertEquals('foo', $entity->bar);
		$entity->bar = 'foo';
	}
	//-----------------------------------------------------------------------------

	/**
	 * @covers ORM_Entity::getTable
	 */
	public function test_getTable()
	{
		$entity = $this->getMockForAbstractClass('ORM_Entity', array(new Plugin),
			'ORM_Entity_Test__Entity_GetTable');

		$p_tables = new ReflectionProperty('ORM', 'tables');
		$p_tables->setAccessible(true);
		$p_tables->setValue('ORM', array('Plugin_Entity_Table_GetTable' => true));

		$this->assertTrue($entity->getTable());
	}
	//-----------------------------------------------------------------------------
}