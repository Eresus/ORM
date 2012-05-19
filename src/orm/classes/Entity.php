<?php
/**
 * ORM
 *
 * Абстрактная сущность
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
 * Абстрактная сущность
 *
 * @package ORM
 * @since 1.00
 */
abstract class ORM_Entity
{
	/**
	 * Модуль
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Атрибуты
	 *
	 * @var array
	 */
	private $attrs = array();

	/**
	 * Кэш геттеров
	 *
	 * @var array
	 */
	private $gettersCache = array();

	/**
	 * Конструктор
	 *
	 * @param Plugin $plugin  модуль
	 * @param array  $attrs   исходные значения полей
	 *
	 * @return ORM_Entity
	 *
	 * @since 1.00
	 */
	public function __construct(Plugin $plugin, array $attrs = array())
	{
		$this->plugin = $plugin;
		$this->attrs = $attrs;
	}
	//-----------------------------------------------------------------------------

	/**
	 * "Магический" метод для доступа к свойствам объекта
	 *
	 * Если есть метод, имя которого состоит из префикса "get" и имени свойства, вызывает этот
	 * метод для полчения значения. В противном случае вызывает {@link getProperty}.
	 *
	 * @param string $key  Имя поля
	 *
	 * @return mixed  Значение поля
	 *
	 * @uses getProperty
	 * @since 1.00
	 */
	public function __get($key)
	{
		$getter = 'get' . $key;
		if (method_exists($this, $getter))
		{
			if (!isset($this->gettersCache[$key]))
			{
				$this->gettersCache[$key] = $this->$getter();
			}
			return $this->gettersCache[$key];
		}

		return $this->getProperty($key);
	}
	//-----------------------------------------------------------------------------

	/**
	 * "Магический" метод для установки свойств объекта
	 *
	 * Если есть метод, имя которого состоит из префикса "set" и имени свойства, вызывает этот
	 * метод для установки значения. В противном случае вызывает {@link setProperty()}.
	 *
	 * @param string $key    Имя поля
	 * @param mixed  $value  Значение поля
	 *
	 * @return void
	 *
	 * @uses setProperty()
	 * @since 1.00
	 */
	public function __set($key, $value)
	{
		$setter = 'set' . $key;
		if (method_exists($this, $setter))
		{
			$this->$setter($value);
		}
		else
		{
			$this->setProperty($key, $value);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает таблицу этой сущности
	 *
	 * @return ORM_Table
	 *
	 * @since 1.00
	 */
	public function getTable()
	{
		$entityName = get_class($this);
		$entityName = substr($entityName, strrpos($entityName, '_') + 1);
		return ORM::getTable($this->plugin, $entityName);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает значение свойства
	 *
	 * Метод не инициирует вызов сеттеров, но обрабатывает значение фильтрами
	 *
	 * @param string $key    Имя свойства
	 * @param mixed  $value  Значение
	 *
	 * @return void
	 *
	 * @uses PDO
	 * @since 1.00
	 */
	public function setProperty($key, $value)
	{
		$this->attrs[$key] = $value;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает значение свойства
	 *
	 * Читает значение непосредственно из массива свойств, не инициируя вызов геттеров
	 *
	 * @param string $key  имя свойства
	 *
	 * @return mixed  значение свойства
	 *
	 * @since 1.00
	 */
	public function getProperty($key)
	{
		if (isset($this->attrs[$key]))
		{
			return $this->attrs[$key];
		}

		return null;
	}
	//-----------------------------------------------------------------------------

	//@codeCoverageIgnoreStart
	/**
	 * Вызывается перед изменением в БД
	 *
	 * @param ezcQuery $query  запрос, который будет выполнен для сохранения записи
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function beforeSave(ezcQuery $query)
	{
	}
	//@codeCoverageIgnoreEnd
	//-----------------------------------------------------------------------------

	//@codeCoverageIgnoreStart
	/**
	 * Вызывается после записи изменений в БД
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function afterSave()
	{
	}
	//@codeCoverageIgnoreEnd
	//-----------------------------------------------------------------------------

	//@codeCoverageIgnoreStart
	/**
	 * Вызывается перед удалением записи из БД
	 *
	 * @param ezcQuery $query  запрос, который будет выполнен для удаления записи
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function beforeDelete(ezcQuery $query)
	{
	}
	//@codeCoverageIgnoreEnd
	//-----------------------------------------------------------------------------

	//@codeCoverageIgnoreStart
	/**
	 * Вызывается после удаления записи из БД
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function afterDelete()
	{
	}
	//@codeCoverageIgnoreEnd
	//-----------------------------------------------------------------------------
}