<?php
/**
 * ORM
 *
 * Драйвер MySQL
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
 * Драйвер MySQL
 *
 * @package ORM
 * @since 1.00
 */
class ORM_Driver_MySQL
{
	/**
	 * Создаёт таблицу
	 *
	 * @param string $tableName   имя таблицы
	 * @param array  $columns     описание столбцов
	 * @param string $primaryKey  первичный ключ
	 * @param array  $indexes     описание индексов
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function createTable($tableName, array $columns, $primaryKey, array $indexes)
	{
		$db = DB::getHandler();
		$tableName = $db->options->tableNamePrefix . $tableName;

		$sql = array();
		foreach ($columns as $name => $attrs)
		{
			$sql []= $name . ' ' . $this->getFieldDefinition($attrs);
		}
		$sql []= 'PRIMARY KEY (' . $primaryKey . ')';
		foreach ($indexes as $name => $params)
		{
			$sql []= 'KEY ' . $name . ' (' . implode(', ', $params['fields']) . ')';
		}
		$sql = "CREATE TABLE $tableName (" . implode(', ', $sql) . ') TYPE InnoDB';
		$db->exec($sql);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Удаляет таблицу
	 *
	 * @param string $tableName  имя таблицы
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function dropTable($tableName)
	{
		$db = DB::getHandler();
		$tableName = $db->options->tableNamePrefix . $tableName;
		$sql = "DROP TABLE $tableName";
		$db->exec($sql);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает объявление поля
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @throws InvalidArgumentException  в случае если в $attrs нет нужных элементов или их значения
	 *                                   неверны
	 *
	 * @return string
	 *
	 * @since 1.00
	 */
	public function getFieldDefinition(array $attrs)
	{
		if (!array_key_exists('type', $attrs))
		{
			throw new InvalidArgumentException('No type specified for field');
		}

		if (!in_array($attrs['type'], ORM::fieldTypes()))
		{
			throw new InvalidArgumentException('Invalid type "' . $attrs['type'] . '"');
		}

		$method = 'getDefinitionFor_' . $attrs['type'];
		$sql = $this->$method($attrs);

		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа boolean
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_boolean(array $attrs)
	{
		$sql = 'BOOL';
		if (@$attrs['autoincrement'])
		{
			$sql .= ' AUTO_INCREMENT';
		}
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа date
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_date(array $attrs)
	{
		$sql = 'DATE';
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа float
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_float(array $attrs)
	{
		if (isset($attrs['length']) && 2147483647 == $attrs['length'])
		{
			$sql = 'DOUBLE';
		}
		else
		{
			$sql = 'FLOAT';
		}
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа integer
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_integer(array $attrs)
	{
		$sql = 'INT';
		$length = isset($attrs['length']) ? $attrs['length'] : 10;
		$sql .= '(' . $length . ')';
		if (@$attrs['unsigned'])
		{
			$sql .= ' UNSIGNED';
		}
		if (@$attrs['autoincrement'])
		{
			$sql .= ' AUTO_INCREMENT';
		}
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа string
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_string(array $attrs)
	{
		if (isset($attrs['length']) && 255 >= $attrs['length'])
		{
			$sql = 'VARCHAR(' . $attrs['length'] . ')';
		}
		elseif (!isset($attrs['length']) || 65535 >= $attrs['length'])
		{
			$sql = 'TEXT';
		}
		else
		{
			$sql = 'LONGTEXT';
		}
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа time
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_time(array $attrs)
	{
		$sql = 'TIME';
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление поля типа timestamp
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function getDefinitionFor_timestamp(array $attrs)
	{
		$sql = 'TIMESTAMP';
		$sql .= $this->getDefinitionFor_DEFAULT($attrs);
		return $sql;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает SQL-объявление значения по умолчанию для поля
	 *
	 * @param array $attrs  атрибуты поля
	 *
	 * @return string  SQL
	 *
	 * @since 1.00
	 */
	private function getDefinitionFor_DEFAULT(array $attrs)
	{
		$sql = '';
		if (array_key_exists('default', $attrs))
		{
			$sql .= ' DEFAULT ';
			if (is_null($attrs['default']))
			{
				$sql .= 'NULL';
			}
			elseif (in_array($attrs['type'], array('date', 'string', 'time', 'timestamp')))
			{
				$sql .= '\'' . $attrs['default'] . '\'';
			}
			else
			{
				$sql .= $attrs['default'];
			}
		}
		return $sql;
	}
	//-----------------------------------------------------------------------------
}
