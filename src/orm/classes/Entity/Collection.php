<?php
/**
 * Коллекция сущностей
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
 * Коллекция сущностей
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Entity_Collection extends SplObjectStorage
{
    /**
     * Очищает коллекцию, удаляя все объекты
     */
    public function clear()
    {
        parent::rewind();
        while (parent::valid())
        {
            $this->detach(parent::current());
        }
    }

    /**
     * @param ORM_Entity|int $entity
     * @return bool
     */
    public function contains($entity)
    {
        if ($entity instanceof ORM_Entity)
        {
            if ($entity->getEntityState() == $entity::IS_DELETED)
            {
                return false;
            }
            return parent::contains($entity);
        }
        else
        {
            $id = $entity;
            $this->rewind();
            while ($this->valid())
            {
                $entity = $this->current();
                if ($entity->getPrimaryKey() == $id)
                {
                    return true;
                }
                $this->next();
            }
            return false;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        $count = 0;
        foreach ($this as $entity)
        {
            /** @var ORM_Entity $entity */
            if ($entity->getEntityState() != $entity::IS_DELETED)
            {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return void
     */
    public function next()
    {
        do
        {
            parent::next();
        }
        while ($this->valid() && $this->isCurrentEntityDeleted());
    }

    /**
     * @return void
     */
    public function rewind()
    {
        parent::rewind();
        if ($this->valid() && $this->isCurrentEntityDeleted())
        {
            $this->next();
        }
    }

    /**
     * @param int|object $offset
     *
     * @throws OutOfBoundsException
     *
     * @return ORM_Entity
     */
    public function offsetGet($offset)
    {
        if (is_object($offset))
        {
            return parent::offsetGet($offset);
        }

        if (!$this->valid() || $this->key() > $offset)
        {
            $this->rewind();
        }
        while ($this->key() < $offset && $this->valid())
        {
            $this->next();
        }
        if ($this->valid())
        {
            return $this->current();
        }
        else
        {
            throw new OutOfBoundsException(sprintf('Invalid index "%s"', $offset));
        }
    }

    /**
     * Возвращает true если текущая сущность помечена как удалённая
     * @return bool
     */
    private function isCurrentEntityDeleted()
    {
        /** @var ORM_Entity $entity */
        $entity = $this->current();
        return $entity->getEntityState() == ORM_Entity::IS_DELETED;
    }
}

