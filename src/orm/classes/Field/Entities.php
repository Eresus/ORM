<?php
/**
 * Поле типа «entities»
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
 * Поле типа «entities»
 *
 * Обязательные параметры:
 *
 * - «class» — полное имя класса объектов, привязанных к этому полю
 * - «reference» — имя поля привязанных объектов, ссылающегося на этот объект
 *
 * Необязательные параметры:
 *
 * - «cascade» — (массив) определяет, какие действия над этим объектом, должны быть повторены над
 *   объектами, привязанными к нему. Возможные ключевые слова: «persist» (добавление), «update»
 *   (обновление), «delete» (удаление)
 * - «orderBy» — (массив) определяет порядок сортировки привязанных объектов. Нечётные элементы
 *   массива должны задавать поля, чётные — направление сортировки.
 * - «filter» — (массив) фильтр привязанных объектов. Каждый элемент должен в свою очередь быть
 *   массивом, состоящим из трёх элементов: имя поля, оператор, значение. Оператор — это метод
 *   класса ezcQueryExpression.
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Field_Entities extends ORM_Field_Abstract
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
        return 'entities';
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
     * Вычисляет и возвращает значение виртуального поля
     *
     * @param ORM_Entity $entity
     *
     * @throws LogicException
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function evaluateVirtualValue(ORM_Entity $entity)
    {
        if ($entity->getPrimaryKey())
        {
            $table = $this->table->getDriver()->getManager()
                ->getTableByEntityClass($this->getParam('class'));
            if (is_null($table))
            {
                return array();
            }
            $referenceField = $this->getParam('reference');
            $columns = $table->getColumns();
            if (!array_key_exists($referenceField, $columns))
            {
                throw new LogicException(sprintf('Unknown reference column: %s', $referenceField));
            }
            if (!$columns[$referenceField]->canBeUsedInWhere())
            {
                throw new LogicException(
                    sprintf('Field "%s" can not be used as a reference field', $referenceField));
            }
            $q = $table->createSelectQuery();

            /* Формируем условие выборки */
            $where = array($q->expr->eq($referenceField,
                $q->bindValue($entity->getPrimaryKey(), ":$referenceField",
                    $columns[$referenceField]->getPdoType())));

            if ($this->hasParam('filter'))
            {
                foreach ($this->getParam('filter') as $filter)
                {
                    $where []= call_user_func(array($q->expr, $filter[1]), $filter[0], $filter[2]);
                }
            }
            $q->where($where);

            /* Задаём сортировку */
            if ($this->hasParam('orderBy'))
            {
                $pairs = array_chunk($this->getParam('orderBy'), 2);
                foreach ($pairs as $pair)
                {
                    $q->orderBy(reset($pair), next($pair));
                }
            }

            return $table->loadFromQuery($q);
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
     */
    public function afterEntitySave(ORM_Entity $entity)
    {
        if (in_array('persist', $this->getParam('cascade', array())))
        {
            $table = $this->table->getDriver()->getManager()
                ->getTableByEntityClass($this->getParam('class'));
            if (is_null($table))
            {
                return;
            }
            foreach ($entity->{$this->getName()} as $childEntity)
            {
                /** @var ORM_Entity $childEntity */
                if ($childEntity->getEntityState() == ORM_Entity::IS_NEW)
                {
                    $childEntity->{$this->getParam('reference')} = $entity;
                    $table->persist($childEntity);
                }
            }
        }

        if (in_array('update', $this->getParam('cascade', array())))
        {
            $table = $this->table->getDriver()->getManager()
                ->getTableByEntityClass($this->getParam('class'));
            if (is_null($table))
            {
                return;
            }
            foreach ($entity->{$this->getName()} as $childEntity)
            {
                /** @var ORM_Entity $childEntity */
                if ($childEntity->getEntityState() == ORM_Entity::IS_DIRTY)
                {
                    $table->update($childEntity);
                }
            }
        }
    }

    /**
     * Действия, выполняемые после удаления сущности
     *
     * @param ORM_Entity $entity
     */
    public function afterEntityDelete(ORM_Entity $entity)
    {
        if (in_array('delete', $this->getParam('cascade', array())))
        {
            $table = $this->table->getDriver()->getManager()
                ->getTableByEntityClass($this->getParam('class'));
            if (is_null($table))
            {
                return;
            }
            foreach ($entity->{$this->getName()} as $childEntity)
            {
                $table->delete($childEntity);
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
        return array('class', 'reference');
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
        return array('cascade', 'filter', 'orderBy');
    }
}

