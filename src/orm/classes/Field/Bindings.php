<?php
/**
 * Поле типа «bindings»
 *
 * @version ${product.version}
 *
 * @copyright 2013, Михаил Красильников <m.krasilnikov@yandex.ru>
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
 */


/**
 * Поле типа «bindings»
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Field_Bindings extends ORM_Field_Abstract
{
    /**
     * Возвращает имя типа
     *
     * @return string
     *
     * @since 3.00
     */
    public function getTypeName()
    {
        return 'bindings';
    }

    /**
     * Возвращает true если это виртуальный тип (т. е. для него не надо создавать поле в таблице)
     *
     * @return bool
     *
     * @since 3.00
     */
    public function isVirtual()
    {
        return true;
    }

    /**
     * Может ли это поле участвовать в разделе WHERE запросов SQL
     *
     * @return bool
     *
     * @since 3.00
     */
    public function canBeUsedInWhere()
    {
        return true;
    }

    /**
     * Вычисляет и возвращает значение виртуального поля
     *
     * @param ORM_Entity $entity
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function evaluateVirtualValue(ORM_Entity $entity)
    {
        if ($entity->getPrimaryKey())
        {
            $table = new ORM_Table_Bindings($this->table, $this->getName());

            $q = DB::getHandler()->createSelectQuery();
            $q->select($this->getName());
            $q->from($table->getName());
            $q->where(
                $q->expr->eq(
                    preg_replace('/^.*?_/', '', $entity->getTable()->getName()),
                    $q->bindValue($entity->getPrimaryKey())
                ));
            $bindings = DB::fetchAll($q);
            $value = array();
            foreach ($bindings as $binding)
            {
                $value [] = $binding[$this->getName()];
            }
            $targetTable = $this->table->getDriver()->getManager()
                ->getTableByEntityClass($this->getParam('class'));
            $collection = new ORM_Entity_Collection();
            foreach ($value as $item)
            {
                $collection->attach($targetTable->find($item));
            }
            $value = $collection;
            return $value;
        }
        else
        {
            return new ORM_Entity_Collection();
        }
    }

    /**
     * Преобразует значение ORM в значение PDO
     *
     * @param mixed $ormValue
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function orm2pdo($ormValue)
    {
        if (!(is_array($ormValue) || $ormValue instanceof ArrayAccess))
        {
            throw new InvalidArgumentException(
                sprintf('Argument 1 passed to %s should be array (or ArrayAccess) but %s given',
                    __METHOD__, gettype($ormValue)));
        }

        $manager = $this->table->getDriver()->getManager();
        $table = $manager->getTableByEntityClass($this->getParam('class'));

        foreach ($ormValue as &$element)
        {
            if (!($element instanceof ORM_Entity))
            {
                $element = $table->find($element);
            }
        }
        return $ormValue;
    }

    /**
     * Добавляет дополнительные таблицы к запросу
     *
     * @param ezcQuerySelect $query
     */
    public function joinTables(ezcQuerySelect $query)
    {
        $joinTable = new ORM_Table_Bindings($this->table, $this->getName());
        $query->select($joinTable->getName() . '.' . $this->name);
        $query->leftJoin($joinTable->getName(), $query->expr->eq(
            "{$joinTable->getName()}.{$joinTable->getSourceField()}",
            "{$joinTable->getName()}.{$this->getName()}"
        ));
    }

    /**
     * Действия, выполняемые после создания таблицы
     */
    public function afterTableCreate()
    {
        $table = new ORM_Table_Bindings($this->table, $this->getName());
        $table->getDriver()->createTable($table);
    }

    /**
     * Действия, выполняемые после удаления таблицы
     */
    public function afterTableDrop()
    {
        $table = new ORM_Table_Bindings($this->table, $this->getName());
        $table->getDriver()->dropTable($table);
    }

    /**
     * Действия, выполняемые после сохранения сущности
     *
     * @param ORM_Entity $entity
     */
    public function afterEntitySave(ORM_Entity $entity)
    {
        $inMemoryBindings = array();
        foreach ($entity->{$this->getName()} as $bindedEntity)
        {
            /** @var ORM_Entity $bindedEntity */
            $inMemoryBindings[$bindedEntity->getPrimaryKey()] = $bindedEntity;
        }

        $table = new ORM_Table_Bindings($this->table, $this->getName());
        $bindingsTableName = $table->getName();
        $sourceField = $table->getSourceField();

        /*
         * Загружаем список привязок, сохранённых в БД
         */
        $q = DB::getHandler()->createSelectQuery();
        $q->select('*');
        $q->from($bindingsTableName);
        $q->where($q->expr->eq($sourceField, $q->bindValue($entity->getPrimaryKey())));
        $storedBindings = DB::fetchAll($q);

        /*
         * Удаляем из $inMemoryBindings те, что уже есть в БД. Добавляем в $toDelete те,
         * которые есть в БД, но которых нет в $inMemoryBindings.
         */
        $toDelete = array();
        foreach ($storedBindings as $storedBinding)
        {
            if (array_key_exists($storedBinding[$sourceField], $inMemoryBindings))
            {
                unset($inMemoryBindings[$storedBinding[$sourceField]]);
            }
            else
            {
                $toDelete []= $storedBinding;
            }
        }

        /*
         * Удаляем из БД привязки, которых нет у объекта
         */
        if (count($toDelete) > 0)
        {
            $q = DB::getHandler()->createDeleteQuery();
            $keys = array();
            foreach ($toDelete as $record)
            {
                $keys []= $q->expr->lAnd(
                    $q->expr->eq($sourceField,
                        $q->bindValue($record[$sourceField], null, PDO::PARAM_INT)),
                    $q->expr->eq($this->getName(),
                        $q->bindValue($record[$this->getName()], null, PDO::PARAM_INT))
                );
            }
            $q->deleteFrom($bindingsTableName)->where($q->expr->lOr($keys));
            DB::execute($q);
        }

        /*
         * Сохраняем в БД те привязки, которых там ещё нет
         */
        if (count($inMemoryBindings) > 0)
        {
            $q = DB::getHandler()->createInsertQuery();
            $q->insertInto($bindingsTableName);
            $q->set($sourceField, $q->bindValue($entity->getPrimaryKey()));
            $bindedId = null;
            $q->set($this->getName(), $q->bindParam($bindedId));
            foreach ($inMemoryBindings as $binding)
            {
                /** @var ORM_Entity $binding */
                /** @noinspection PhpUnusedLocalVariableInspection */
                $bindedId = $binding->getPrimaryKey();
                DB::execute($q);
            }
        }
    }

    /**
     * Возвращает список обязательных параметров
     *
     * @return string[]
     *
     * @since 3.00
     */
    protected function getRequiredParams()
    {
        return array('class');
    }

    /**
     * Возвращает список возможных необязательных параметров
     *
     * @return string[]
     *
     * @since 3.00
     */
    protected function getOptionalParams()
    {
        return array('reverse');
    }
}

