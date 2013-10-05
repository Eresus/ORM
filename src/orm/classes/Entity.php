<?php
/**
 * Абстрактная сущность
 *
 * @version ${product.version}
 *
 * @copyright 2011, Михаил Красильников <m.krasilnikov@yandex.ru>
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
 * Абстрактная сущность
 *
 * @package ORM
 * @since 1.00
 */
abstract class ORM_Entity
{
    /**
     * Состояние сущности: новый объект
     * @since unstable
     */
    const IS_NEW = 1;

    /**
     * Состояние сущности: объект соответствует записи в БД
     * @since unstable
     */
    const IS_PERSISTENT = 2;

    /**
     * Состояние сущности: в объекте есть изменения, несохранённые в БД
     * @since unstable
     */
    const IS_DIRTY = 3;

    /**
     * Состояние сущности: объект удалён из БД
     * @since unstable
     */
    const IS_DELETED = 4;

    /**
     * Модуль
     *
     * @var Plugin|TPlugin
     */
    protected $plugin;

    /**
     * Состояние сущности
     * @var int
     *
     * @since unstable
     */
    private $state = self::IS_NEW;

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
     * @param Plugin|TPlugin $plugin  модуль
     * @param array          $attrs   исходные значения полей
     *
     * @return ORM_Entity
     *
     * @since 1.00
     */
    public function __construct($plugin, array $attrs = array())
    {
        $this->plugin = $plugin;
        $this->attrs = $attrs;
    }

    /**
     * "Магический" метод для доступа к свойствам объекта
     *
     * Если есть метод, имя которого состоит из префикса "get" и имени свойства, вызывает этот
     * метод для получения значения. В противном случае вызывает {@link getProperty}.
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
        unset($this->gettersCache[$key]);
        if ($this->getEntityState() == self::IS_PERSISTENT)
        {
            $this->setEntityState(self::IS_DIRTY);
        }
    }

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

    /**
     * Возвращает состояние объекта
     *
     * См. константы ORM_Entity::IS_…
     *
     * @return int
     *
     * @since unstable
     */
    public function getEntityState()
    {
        return $this->state;
    }

    /**
     * Задаёт состояние объекта
     *
     * @param int $state  новое состояние (см. константы ORM_Entity::IS_…)
     *
     * @since unstable
     */
    protected function setEntityState($state)
    {
        $this->state = intval($state);
    }

    /**
     * Возвращает значение основного ключа для этого объкта
     *
     * @return mixed
     *
     * @since 2.02
     */
    public function getPrimaryKey()
    {
        return $this->{$this->getTable()->getPrimaryKey()};
    }

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
        $columns = $this->getTable()->getColumns();
        if (array_key_exists($key, $columns))
        {
            $column = $columns[$key];
            switch (@$column['type'])
            {
                case 'bindings':
                    foreach ($value as &$item)
                    {
                        if ($item instanceof ORM_Entity)
                        {
                            $item = $item->{$item->getTable()->getPrimaryKey()};
                        }
                    }
                    break;
                case 'entity':
                    if (is_object($value))
                    {
                        $primaryKey = $this->getTable()->getPrimaryKey();
                        $value = $value->{$primaryKey};
                    }
            }
        }
        $this->attrs[$key] = $value;
    }

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
        $value = isset($this->attrs[$key]) ? $this->attrs[$key] : null;

        $table = $this->getTable();
        $columns = $table->getColumns();
        if (array_key_exists($key, $columns))
        {
            $column = $columns[$key];
            switch (@$column['type'])
            {
                case 'bindings':
                    $value = $this->getBindedEntities($key, $column);
                    break;
                case 'entities':
                    $value = $this->getEntities($column);
                    break;
                case 'entity':
                    $table = $this->getTableByEntityClass(@$column['class']);
                    $value = $table->find($value);
            }
        }
        return $value;
    }

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

    /**
     * Вызывается после записи изменений в БД
     *
     * @return void
     *
     * @since 1.00
     */
    public function afterSave()
    {
        foreach ($this->getTable()->getColumns() as $key => $column)
        {
            if ('bindings' == @$column['type'])
            {
                $bindingsTableName = $this->getTable()->getBindingsTableName($key);
                $sourceField = preg_replace('/^.*?_/', '', $this->getTable()->getName());
                $actualBindings = array();
                foreach ($this->{$key} as $bindedEntity)
                {
                    /** @var ORM_Entity $bindedEntity */
                    $actualBindings[$bindedEntity->getPrimaryKey()] = $bindedEntity;
                }

                $q = DB::getHandler()->createSelectQuery();
                $q->select('*');
                $q->from($bindingsTableName);
                $q->where($q->expr->eq($sourceField, $q->bindValue($this->getPrimaryKey())));
                $storedBindings = DB::fetchAll($q);

                $toDelete = array();
                foreach ($storedBindings as $storedBinding)
                {
                    if (array_key_exists($storedBinding[$key], $actualBindings))
                    {
                        unset($actualBindings[$storedBinding[$key]]);
                    }
                    else
                    {
                        $toDelete []= $storedBinding['id'];
                    }
                }

                if (count($toDelete) > 0)
                {
                    $q = DB::getHandler()->createDeleteQuery();
                    $q->deleteFrom($bindingsTableName)->where($q->expr->in('id', $toDelete));
                    DB::execute($q);
                }

                if (count($actualBindings) > 0)
                {
                    $q = DB::getHandler()->createInsertQuery();
                    $q->insertInto($bindingsTableName);
                    $q->set($sourceField, $q->bindValue($this->getPrimaryKey()));
                    $q->set($key, $q->bindParam($bindedId));
                    foreach ($actualBindings as $binding)
                    {
                        /** @var ORM_Entity $binding */
                        $bindedId = $binding->getPrimaryKey();
                        DB::execute($q);
                    }
                }
            }
        }
        $this->setEntityState(self::IS_PERSISTENT);
    }

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

    /**
     * Вызывается после удаления записи из БД
     *
     * @return void
     *
     * @since 1.00
     */
    public function afterDelete()
    {
        $this->setEntityState(self::IS_DELETED);
    }

    /**
     * Возвращает таблицу по имени класса сущности
     *
     * @param string $entityClass
     *
     * @throws InvalidArgumentException
     *
     * @return ORM_Table
     *
     * @since 2.02
     */
    protected function getTableByEntityClass($entityClass)
    {
        if ('' === strval($entityClass))
        {
            throw new InvalidArgumentException('$entityClass can not be blank');
        }
        $entityPluginName = substr($entityClass, 0, strpos($entityClass, '_'));
        $entityPluginName = strtolower($entityPluginName);
        $plugin = Eresus_Kernel::app()->getLegacyKernel()->plugins
            ->load($entityPluginName);
        $entityName = substr($entityClass, strrpos($entityClass, '_') + 1);
        $table = ORM::getTable($plugin, $entityName);
        return $table;
    }

    /**
     * Возвращает объекты-значения указанного поля
     *
     * @param array  $column
     *
     * @return ORM_Entity_Collection
     */
    protected function getEntities(array $column)
    {
        if ($this->getPrimaryKey())
        {
            $table = $this->getTableByEntityClass(@$column['class']);
            return $table->findAllBy(array(@$column['reference'] => $this->getPrimaryKey()));
        }
        else
        {
            return new ORM_Entity_Collection();
        }
    }

    /**
     * Возвращает объекты, привязанные к указанному полю
     *
     * @param string $key
     * @param array  $column
     *
     * @return ORM_Entity_Collection
     */
    protected function getBindedEntities($key, array $column)
    {
        if ($this->getPrimaryKey())
        {
            $q = DB::getHandler()->createSelectQuery();
            $q->select($key);
            $q->from($this->getTable()->getBindingsTableName($key));
            $q->where(
                $q->expr->eq(
                    preg_replace('/^.*?_/', '', $this->getTable()->getName()),
                    $q->bindValue($this->getPrimaryKey())
                ));
            $bindings = DB::fetchAll($q);
            $value = array();
            foreach ($bindings as $binding)
            {
                $value [] = $binding[$key];
            }
            $targetTable = $this->getTableByEntityClass(@$column['class']);
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
}

