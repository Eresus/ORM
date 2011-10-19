<?php
/**
 * UI
 *
 * Элемент списка {@link UI_List}
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
 *
 * $Id$
 */

/**
 * Элемент списка {@link UI_List}
 *
 * @package ORM
 */
class ORM_UI_List_Item implements UI_List_Item_Interface
{
	/**
	 * Сущность
	 *
	 * @var ORM_Entity
	 * @since 1.00
	 */
	private $entity;

	/**
	 * Конструктор элемента
	 *
	 * @param ORM_Entity $entity  сущность
	 *
	 * @return ORM_UI_List_Item
	 *
	 * @since 1.00
	 */
	public function __construct(ORM_Entity $entity)
	{
		$this->entity = $entity;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Прокси к свойствам сущности
	 *
	 * @param string $property  имя свойства
	 *
	 * @return mixed
	 *
	 * @since 1.00
	 */
	public function __get($property)
	{
		return $this->entity->$property;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает идентификатор элемента
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	public function getId()
	{
		return $this->entity->id;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает состояние элемента (вкл/выкл)
	 *
	 * @return bool
	 *
	 * @since 1.00
	 */
	public function isEnabled()
	{
		return $this->entity->active;
	}
	//-----------------------------------------------------------------------------
}
