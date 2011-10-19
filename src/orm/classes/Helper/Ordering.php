<?php
/**
 * ORM
 *
 * Помощник ручной сортировки
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
 * $Id: Entity.php 1309 2011-10-19 12:59:03Z mk $
 */


/**
 * Помощник ручной сортировки
 *
 * @package ORM
 * @since 1.00
 */
class ORM_Helper_Ordering
{
	/**
	 * Поле, хранящее порядковый номер
	 *
	 * @var string
	 */
	private $fieldName = 'position';

	/**
	 * Список полей для группировки
	 *
	 * @var array
	 */
	private $groupBy = array();

	/**
	 * Задаёт список полей по которым надо выполнять группировку
	 *
	 * Считается что записи принадлежат к одной группе, если знаения перечисленных полей у всех
	 * записей совпадают.
	 *
	 * @param string $field1…$fieldN  список полей для группировки
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function groupBy()
	{
		$this->groupBy = func_get_args();
	}
	//-----------------------------------------------------------------------------

	/**
	 * Задаёт имя поля, содержащего порядковый номер
	 *
	 * @param string $name
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function setFieldName($name)
	{
		$this->fieldName = $name;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает запись выше в группе
	 * @param ORM_Entity $entity
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function moveUp(ORM_Entity $entity)
	{
		if (0 == $entity->{$this->fieldName})
		{
			// Выше некуда
			return;
		}
		$table = $entity->getTable();
		$q = $table->createSelectQuery(false);
		$e = $q->expr;
		$group = array();
		foreach ($this->groupBy as $field)
		{
			$group []= $e->eq($field, $q->bindValue($entity->$field));
		}
		$group []= $e->lt($this->fieldName, $q->bindValue($entity->{$this->fieldName}, null,
			PDO::PARAM_INT));
		$q->select('*')->where(call_user_func_array(array($e, 'lAnd'), $group))->
			orderBy($this->fieldName, ezcQuerySelect::DESC);
		$swap = $table->loadOneFromQuery($q);

		if (!$swap)
		{
			// Выше некуда
			return;
		}

		$pos = $entity->{$this->fieldName};
		$entity->{$this->fieldName} = $swap->{$this->fieldName};
		$swap->{$this->fieldName} = $pos;
		$table->update($entity);
		$table->update($swap);
	}
	//-----------------------------------------------------------------------------

	/**
	 * Перемещает запись ниже в группе
	 * @param ORM_Entity $entity
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function moveDown(ORM_Entity $entity)
	{
		$table = $entity->getTable();
		$q = $table->createSelectQuery(false);
		$e = $q->expr;
		$group = array();
		foreach ($this->groupBy as $field)
		{
			$group []= $e->eq($field, $q->bindValue($entity->$field));
		}
		$group []= $e->gt($this->fieldName, $q->bindValue($entity->{$this->fieldName}, null,
			PDO::PARAM_INT));
		$q->select('*')->where(call_user_func_array(array($e, 'lAnd'), $group))->
			orderBy($this->fieldName, ezcQuerySelect::ASC);
		$swap = $table->loadOneFromQuery($q);

		if (!$swap)
		{
			// Ниже некуда
			return;
		}

		$pos = $entity->{$this->fieldName};
		$entity->{$this->fieldName} = $swap->{$this->fieldName};
		$swap->{$this->fieldName} = $pos;
		$table->update($entity);
		$table->update($swap);
	}
	//-----------------------------------------------------------------------------
}