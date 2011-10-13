<?php
/**
 * ORM
 *
 * Абстрактная таблица БД
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
 * Абстрактная таблица БД
 *
 * @package ORM
 * @since 1.00
 */
abstract class ORM_Table
{
	/**
	 * Модуль
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	private $tableName ;

	/**
	 * Описание столбцов
	 *
	 * @var array
	 */
	private $columns;

	/**
	 * Имя класса сущности
	 *
	 * @var string
	 */
	private $entityClass = null;

	/**
	 * Имя поля основного ключа
	 *
	 * @var string
	 */
	private $primaryKey = null;

	/**
	 * Порядок сортировки
	 *
	 * Массив пар «поле=>ezcQuerySelect::ASC/DESC»
	 *
	 * @var array
	 */
	private $ordering = array();

	/**
	 * Индексы
	 *
	 * @var array
	 */
	private $indexes = array();

	/**
	 * Конструктор
	 *
	 * @param Plugin $plugin
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$this->setTableDefinition();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Создаёт таблицу в БД
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function create()
	{
		$db = DB::getHandler();
		$tableName = $db->options->tableNamePrefix . $this->getTableName();
		$sql = array();
		foreach ($this->columns as $name => $attrs)
		{
			$field = $name . ' ';
			$isStringType = false;
			switch (@$attrs['type'])
			{
				case 'boolean':
					$field .= 'BOOL';
					if (@$attrs['autoincrement'])
					{
						$field .= ' AUTO_INCREMENT';
					}
				break;

				case 'float':
					if (isset($attrs['length']) && 2147483647 == $attrs['length'])
					{
						$field .= 'DOUBLE';
					}
					else
					{
						$field .= 'FLOAT';
					}
				break;

				case 'integer':
					$field .= 'INT';
					$length = isset($attrs['length']) ? $attrs['length'] : 10;
					$field .= '(' . $length . ')';
					if (@$attrs['unsigned'])
					{
						$field .= ' UNSIGNED';
					}
					if (@$attrs['autoincrement'])
					{
						$field .= ' AUTO_INCREMENT';
					}
				break;

				case 'string':
					if (isset($attrs['length']) && 255 >= $attrs['length'])
					{
						$field .= 'VARCHAR(' . $attrs['length'] . ')';
					}
					elseif (!isset($attrs['length']) || 65535 >= $attrs['length'])
					{
						$field .= 'TEXT';
					}
					else
					{
						$field .= 'LONGTEXT';
					}
					$isStringType = true;
				break;

				case 'timestamp':
					$field .= 'TIMESTAMP';
					$isStringType = true;
				break;

				case 'date':
					$field .= 'DATE';
					$isStringType = true;
				break;

				case 'time':
					$field .= 'TIME';
					$isStringType = true;
				break;

				default:
					throw new LogicException('Invalid type "' . @$attrs['type'] . '" for column' . $name);
			}
			if (array_key_exists('default', $attrs))
			{
				$field .= ' DEFAULT ';
				if (is_null($attrs['default']))
				{
					$field .= 'NULL';
				}
				elseif ($isStringType)
				{
					$field .= '\'' . $attrs['default'] . '\'';
				}
				else
				{
					$field .= $attrs['default'];
				}
			}
			$sql []= $field;
		}
		$sql []= 'PRIMARY KEY (' . $this->getPrimaryKey() . ')';
		foreach ($this->indexes as $name => $params)
		{
			$sql []= 'KEY ' . $name . ' (' . implode(', ', $params['fields']) . ')';
		}
		$sql = "CREATE TABLE $tableName (" . implode(', ', $sql) . ') TYPE InnoDB';
		$db->exec($sql);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Помещает сущность в таблицу
	 *
	 * @param ORM_Entity $entity
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses ORM_Entity::afterSave()
	 * @uses DB::createInsertQuery()
	 * @uses DB::execute()
	 * @uses ezcQueryInsert::insertInto()
	 * @uses ezcQueryInsert::set()
	 * @uses ezcQueryInsert::bindValue()
	 * @uses DB::getHandler()
	 * @uses ezcDbHandler::lastInsertId()
	 */
	public function persist(ORM_Entity $entity)
	{
		$q = DB::createInsertQuery();
		$q->insertInto($this->getTableName());
		$autoincrementField = null;
		foreach ($this->columns as $name => $attrs)
		{
			$type = $this->pdoFieldType(@$attrs['type']);
			$value = $this->pdoFieldValue($entity->getProperty($name), @$attrs['type']);
			$q->set($name, $q->bindValue($value, null, $type));
		}
		DB::execute($q);
		if (@$this->columns[$this->primaryKey]['autoincrement'])
		{
			$entity->{$this->primaryKey} = DB::getHandler()->lastInsertId();
		}
		$entity->afterSave();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Обновляет сущность в таблице
	 *
	 * @param ORM_Entity $entity
	 *
	 * @return void
	 *
	 * @since 1.00
	 * @uses ORM_Entity::afterSave()
	 * @uses DB::createUpdateQuery()
	 * @uses DB::execute()
	 * @uses ezcQueryUpdate::update()
	 * @uses ezcQueryUpdate::$expr
	 * @uses ezcQueryUpdate::bindValue()
	 */
	public function update(ORM_Entity $entity)
	{
		$pKey = $this->getPrimaryKey();
		$q = DB::createUpdateQuery();
		$q->update($this->getTableName())->
			where($q->expr->eq($pKey,
				$q->bindValue($entity->$pKey, null, $this->pdoFieldType(@$this->columns[$pKey]['type']))
			));
		foreach ($this->columns as $name => $attrs)
		{
			$type = $this->pdoFieldType(@$attrs['type']);
			$value = $this->pdoFieldValue($entity->getProperty($name), @$attrs['type']);
			$q->set($name, $q->bindValue($value, null, $type));
		}
		DB::execute($q);
		$entity->afterSave();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет сущность из таблицы
	 *
	 * @param ORM_Entity $entity
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function delete(ORM_Entity $entity)
	{
		$pKey = $this->getPrimaryKey();
		$q = DB::createDeleteQuery();
		$q->deleteFrom($this->getTableName())->
			where($q->expr->eq($pKey,
				$q->bindValue($entity->$pKey, null, $this->pdoFieldType(@$this->columns[$pKey]['type']))
			));
		$entity->beforeDelete();
		DB::execute($q);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает количество записей
	 *
	 * @param ezcQuerySelect $query
	 *
	 * @return int
	 *
	 * @since 1.00
	 */
	public function count(ezcQuerySelect $query = null)
	{
		$q = $query ? $query : $this->createCountQuery();
		$raw = DB::fetch($q);
		if ($raw)
		{
			return $raw['record_count'];
		}
		return 0;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает количество записей по полю
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return int
	 *
	 * @since 1.00
	 */
	public function countByField($field, $value)
	{
		$q = $this->createCountQuery();
		$q->where($q->expr->eq($field, $q->bindValue($value)));
		$raw = DB::fetch($q);
		if ($raw)
		{
			return $raw['record_count'];
		}
		return 0;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает все записи
	 *
	 * @param int $limit   максимум элементов, который следует вернуть
	 * @param int $offset  сколько элементов пропустить
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public function findAll($limit = null, $offset = 0)
	{
		$q = $this->createSelectQuery();
		$items = $this->loadFromQuery($q, $limit, $offset);
		return $items;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает сущность по основному ключу
	 *
	 * @param mixed $id
	 *
	 * @return ORM_Entity
	 *
	 * @since 1.00
	 */
	public function find($id)
	{
		$pKey = $this->getPrimaryKey();
		$q = $this->createSelectQuery();
		$q->where($q->expr->eq($pKey,
			$q->bindValue($id, null, $this->pdoFieldType(@$this->columns[$pKey]['type']))));
		return $this->loadOneFromQuery($q);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает заготовку запроса SELECT
	 *
	 * @param bool $fill  заполнить запрос начальными данными
	 *
	 * @return ezcQuerySelect
	 *
	 * @since 1.00
	 */
	public function createSelectQuery($fill = true)
	{
		$q = DB::getHandler()->createSelectQuery();
		$q->from($this->getTableName());
		if ($fill)
		{
			$q->select('*');
			if (count($this->ordering))
			{
				foreach ($this->ordering as $orderBy)
				{
					call_user_func_array(array($q, 'orderBy'), $orderBy);
				}
			}
			elseif (isset($this->columns['position']))
			{
				$q->orderBy('position');
			}
		}
		return $q;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает заготовку запроса SELECT для подсчёта количества записей
	 *
	 * @return ezcQuerySelect
	 *
	 * @since 1.00
	 */
	public function createCountQuery()
	{
		$q = DB::getHandler()->createSelectQuery();
		$q->select($q->alias($q->expr->count('*'), 'record_count'))->from($this->getTableName())->
			limit(1);
		return $q;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает массив сущностей на основе запроса
	 *
	 * @param ezcQuerySelect $query
	 * @param int            $limit   максимум элементов, который следует вернуть
	 * @param int            $offset  сколько элементов пропустить
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public function loadFromQuery(ezcQuerySelect $query, $limit = null, $offset = 0)
	{
		if ($limit)
		{
			$query->limit($limit, $offset);
		}
		$raw = DB::fetchAll($query);
		$items = array();
		if ($raw)
		{
			foreach ($raw as $attrs)
			{
				$items []= $this->entityFactory($attrs);
			}
		}
		return $items;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает сущность на основе запроса
	 *
	 * @param ezcQuerySelect $query
	 *
	 * @return ORM_Entity
	 *
	 * @since 1.00
	 */
	public function loadOneFromQuery(ezcQuerySelect $query)
	{
		$query->limit(1);
		$attrs = DB::fetch($query);
		if ($attrs)
		{
			return $this->entityFactory($attrs);
		}
		return null;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Метод должен устанавливать свойства таблицы БД
	 *
	 * В этом методе необходимо сделать следующее:
	 *
	 * 1. Задать имя таблицы вызовом {@link setTableName()}
	 * 2. Определить столбцы вызовом {@link hasColumns()}
	 * 3. Создать необходимые индексы вызовом {@link index()}
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	abstract protected function setTableDefinition();
	//-----------------------------------------------------------------------------

	/**
	 * Устанавлвиает имя таблицы
	 *
	 * @param string $name
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function setTableName($name)
	{
		$this->tableName = $name;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает имя таблицы
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getTableName()
	{
		return $this->tableName;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Устанавливает описания столбцов
	 *
	 * Аргумент $columns должен быть ассоциативным массивом, где каждый элемент соответствует
	 * одному столбцу таблицы, при этом ключ элемента должен задавать имя столбца, а тело элемента —
	 * описание этого столбца. В свою очередь, тело элемента должно быть ассоциативным массивом, в
	 * котором можно использовать следующие ключи:
	 *
	 * - type (string) — тип поля (см. ниже).
	 * - autoincrement (bool) — Автоинкримент. Допустим только у первого столбца.
	 * - length (int) — размер столбца.
	 * - unsigned (bool) — беззнаковое число.
	 *
	 * Первый столбцец всегда назначается основным ключом.
	 *
	 * Возможные типы полей:
	 *
	 * - boolean
	 * - integer
	 * - float
	 * - string
	 * - timestamp
	 * - time
	 * - date
	 *
	 * @param array $columns
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function hasColumns(array $columns)
	{
		$this->columns = $columns;
		reset($columns);
		$this->primaryKey = key($columns);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Определяет индекс
	 *
	 * @param string $name    имя индекса
	 * @param array  $params  свойства индекса
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function index($name, array $params)
	{
		$this->indexes[$name] = $params;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает имя класса сущности
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getEntityClass()
	{
		if (is_null($this->entityClass))
		{
			$thisClass = get_class($this);
			$this->entityClass = str_replace('_Table_', '_', $thisClass);
		}
		return $this->entityClass;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает имя основного ключа
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	protected function getPrimaryKey()
	{
		return $this->primaryKey;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Задаёт порядок сортировки по умолчанию
	 *
	 * Если порядок сортировки не задан, но есть поле «position» сортировка будет осуществляться по
	 * нему по умочланию.
	 *
	 * Примеры:
	 * <code>
	 * // По порядковому номеру
	 * $this->setOrdering('position');
	 * // По дате, новые в начале
	 * $this->setOrdering('date', ezcQuerySelect::DESC);
	 * // Сначала по названию, потом по дате
	 * $this->setOrdering('title', 'ASC', 'date', 'DESC');
	 * </code>
	 *
	 * @param string $field
	 * @param string $dir
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	protected function setOrdering($field, $dir = ezcQuerySelect::ASC)
	{
		$args = func_get_args();
		if (count($args) % 2 != 0)
		{
			$args []= ezcQuerySelect::ASC;
		}
		$this->ordering = array();
		while (count($args))
		{
			$field = array_shift($args);
			$dir = array_shift($args);
			$this->ordering []= array($field, $dir);
		}
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает тип поля PDO на основе типа поля ORM
	 *
	 * @param string $ormFieldType
	 *
	 * @throws InvalidArgumentException если $ormFieldType не строка или содержит неизвестный тип
	 *
	 * @return int|null
	 *
	 * @since 1.00
	 */
	protected function pdoFieldType($ormFieldType)
	{
		if (!is_string($ormFieldType))
		{
			throw new InvalidArgumentException('$ormFieldType must be of type string, ' .
			gettype($ormFieldType) . ' given');
		}

		switch ($ormFieldType)
		{
			case 'boolean':
				$type = PDO::PARAM_BOOL;
			break;

			case 'integer':
				$type = PDO::PARAM_INT;
			break;

			case 'float':
				$type = null;
			break;

			case 'string':
			case 'datetime':
			case 'time':
			case 'date':
				$type = PDO::PARAM_STR;
			break;

			default:
				throw new InvalidArgumentException('Unknown field type: ' . $ormFieldType);
		}
		return $type;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает значение поля, пригодное для использования с PDO
	 *
	 * @param mixed  $ormValue      значение поля
	 * @param string $ormFieldType  см. {@link hasColumns()}
	 *
	 * @return mixed
	 *
	 * @since 1.00
	 */
	protected function pdoFieldValue($ormValue, $ormFieldType)
	{
		if (!is_string($ormFieldType))
		{
			throw new InvalidArgumentException('$ormFieldType must be of type string, ' .
			gettype($ormFieldType) . ' given');
		}

		if (is_null($ormValue))
		{
			return null;
		}

		switch ($ormFieldType)
		{
			case 'timestamp':
				if (!($ormValue instanceof DateTime))
				{
					throw new InvalidArgumentException('Value of $ormValue must be a DateTime');
				}
				$ormValue = $ormValue->format('Y-m-d H:i:s');
			break;

			case 'date':
				if (!($ormValue instanceof DateTime))
				{
					throw new InvalidArgumentException('Value of $ormValue must be a DateTime');
				}
				$ormValue = $ormValue->format('Y-m-d');
			break;

			case 'time':
				if (!($ormValue instanceof DateTime))
				{
					throw new InvalidArgumentException('Value of $ormValue must be a DateTime');
				}
				$ormValue = $ormValue->format('H:i:s');
			break;
		}
		return $ormValue;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Фабрика сущностей
	 *
	 * @param array $values
	 *
	 * @return ORM_Entity
	 *
	 * @since 1.00
	 */
	protected function entityFactory(array $values)
	{
		$entityClass = $this->getEntityClass();
		foreach ($this->columns as $name => $attrs)
		{
			switch (@$attrs['type'])
			{
				case 'datetime':
					$values[$name] = new DateTime($values[$name]);
				break;
			}
		}
		$entity = new $entityClass($this->plugin, $values);
		return $entity;
	}
	//-----------------------------------------------------------------------------
}