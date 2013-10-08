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
 * @since 2.02
 */
class ORM_Field_Bindings extends ORM_Field_Abstract
{
    /**
     * Возвращает имя типа
     *
     * @return string
     *
     * @since 2.02
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
     * @since 2.02
     */
    public function isVirtual()
    {
        return true;
    }

    /**
     * Вычисляет и возвращает значение виртуального поля
     *
     * @param ORM_Entity $entity
     * @param string     $fieldName
     *
     * @return mixed
     *
     * @see isVirtual()
     * @since 2.02
     */
    public function evaluateVirtualValue(ORM_Entity $entity, $fieldName)
    {
        if ($entity->getPrimaryKey())
        {
            $q = DB::getHandler()->createSelectQuery();
            $q->select($fieldName);
            $q->from($this->getBindingsTableName($entity->getTable(), $fieldName));
            $q->where(
                $q->expr->eq(
                    preg_replace('/^.*?_/', '', $entity->getTable()->getName()),
                    $q->bindValue($entity->getPrimaryKey())
                ));
            $bindings = DB::fetchAll($q);
            $value = array();
            foreach ($bindings as $binding)
            {
                $value [] = $binding[$fieldName];
            }
            $targetTable = $this->orm->getTableByEntityClass($this->getParam('class'));
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
     * Действия, выполняемые после сохранения сущности
     *
     * @param ORM_Entity $entity
     * @param string $field
     */
    public function afterEntitySave(ORM_Entity $entity, $field)
    {
        $bindingsTableName = $this->getBindingsTableName($entity->getTable(), $field);
        $sourceField = preg_replace('/^.*?_/', '', $entity->getTable()->getName());

        $inMemoryBindings = array();
        foreach ($entity->{$field} as $bindedEntity)
        {
            /** @var ORM_Entity $bindedEntity */
            $inMemoryBindings[$bindedEntity->getPrimaryKey()] = $bindedEntity;
        }

        $q = DB::getHandler()->createSelectQuery();
        $q->select('*');
        $q->from($bindingsTableName);
        $q->where($q->expr->eq($sourceField, $q->bindValue($entity->getPrimaryKey())));
        $storedBindings = DB::fetchAll($q);

        $toDelete = array();
        foreach ($storedBindings as $storedBinding)
        {
            if (array_key_exists($storedBinding[$field], $inMemoryBindings))
            {
                unset($inMemoryBindings[$storedBinding[$field]]);
            }
            else
            {
                $toDelete [] = $storedBinding;
            }
        }

        if (count($toDelete) > 0)
        {
            $q = DB::getHandler()->createDeleteQuery();
            $keys = array();
            foreach ($toDelete as $record)
            {
                $keys []= $q->expr->lAnd(
                    $q->expr->eq($sourceField,
                        $q->bindValue($record[$sourceField], null, PDO::PARAM_INT)),
                    $q->expr->eq($field, $q->bindValue($record[$field], null, PDO::PARAM_INT))
                );
            }
            $q->deleteFrom($bindingsTableName)->where($q->expr->lOr($keys));
            DB::execute($q);
        }

        if (count($inMemoryBindings) > 0)
        {
            $q = DB::getHandler()->createInsertQuery();
            $q->insertInto($bindingsTableName);
            $q->set($sourceField, $q->bindValue($entity->getPrimaryKey()));
            $q->set($field, $q->bindParam($bindedId));
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
     * @since 2.02
     */
    protected function getRequiredParams()
    {
        return array('class');
    }

    /**
     * Возвращает имя таблицы привязок для указанного свойства
     *
     * @param ORM_Table $table
     * @param string    $fieldName  имя свойства
     *
     * @return string
     */
    protected function getBindingsTableName(ORM_Table $table, $fieldName)
    {
        return $table->getName() . '_' . $fieldName;
    }
}

