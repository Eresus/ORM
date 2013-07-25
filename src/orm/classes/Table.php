<?php
/**
 * Абстрактная таблица БД
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
     * @var Eresus_Plugin|TPlugin
     */
    protected $plugin;

    /**
     * Драйвер СУБД
     * @var ORM_Driver_Abstract
     * @since 2.01
     */
    private $driver;

    /**
     * Имя таблицы
     *
     * @var string
     */
    private $tableName;

    /**
     * Описание столбцов
     *
     * @var array
     */
    private $columns = array();

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
    private $primaryKey = 'id';

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
     * Реестр объектов
     *
     * @var ORM_Entity[]
     *
     * @since 2.02
     */
    private $registry = array();

    /**
     * Конструктор
     *
     * @param Eresus_Plugin|TPlugin      $plugin
     * @param ORM_Driver_Abstract $driver
     *
     * @return ORM_Table
     *
     * @since 1.00
     */
    public function __construct($plugin, ORM_Driver_Abstract $driver = null)
    {
        $this->plugin = $plugin;
        if (null === $driver)
        {
            $driver = new ORM_Driver_MySQL();
        }
        $this->driver = $driver;
        $this->setTableDefinition();
    }

    /**
     * Возвращает драйвер СУБД
     *
     * @return ORM_Driver_Abstract
     *
     * @since 2.02
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Возвращает имя таблицы
     *
     * @return string
     *
     * @since 1.00, публичный с 2.02
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Возвращает имя класса сущности
     *
     * @return string
     *
     * @since 1.00, публичный с 2.02
     */
    public function getEntityClass()
    {
        if (is_null($this->entityClass))
        {
            $thisClass = get_class($this);
            $this->entityClass = str_replace('_Table_', '_', $thisClass);
        }
        return $this->entityClass;
    }

    /**
     * Возвращает поля таблицы
     *
     * @return array
     *
     * @since 2.02
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Возвращает имя основного ключа
     *
     * @return string
     *
     * @since 1.00, публичный с 2.02
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Возвращает сортировку
     *
     * @return array
     *
     * @since 2.02
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Возвращает индексы
     *
     * @return array
     *
     * @since 2.02
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Создаёт таблицу в БД
     *
     * @return void
     *
     * @since 1.00
     */
    public function create()
    {
        $columns = $this->getColumns();
        foreach ($columns as &$attrs)
        {
            switch (@$attrs['type'])
            {
                case 'entity':
                    $attrs['type'] = 'integer';
                    $attrs['unsigned'] = true;
                    break;
            }
        }
        $this->getDriver()->createTable($this->getTableName(), $columns, $this->getPrimaryKey(),
            $this->getIndexes());
    }

    /**
     * Удаляет таблицу из БД
     *
     * @return void
     *
     * @since 1.00
     */
    public function drop()
    {
        $this->getDriver()->dropTable($this->getTableName());
    }

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
        $q = DB::getHandler()->createInsertQuery();
        $q->insertInto($this->getTableName());
        $this->bindValuesToQuery($entity, $q);
        $entity->beforeSave($q);
        DB::execute($q);
        $columns = $this->getColumns();
        if (@$columns[$this->getPrimaryKey()]['autoincrement'])
        {
            $entity->{$this->getPrimaryKey()} = DB::getHandler()->lastInsertId();
        }
        $entity->afterSave();
    }

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
        $columns = $this->getColumns();
        $q = DB::getHandler()->createUpdateQuery();
        $q->update($this->getTableName())->
            where($q->expr->eq($pKey,
                $q->bindValue($entity->$pKey, null, $this->pdoFieldType(@$columns[$pKey]['type']))
            ));
        $this->bindValuesToQuery($entity, $q);
        $entity->beforeSave($q);
        DB::execute($q);
        $entity->afterSave();
    }

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
        $columns = $this->getColumns();
        $q = DB::getHandler()->createDeleteQuery();
        $q->deleteFrom($this->getTableName())->
            where($q->expr->eq($pKey,
                $q->bindValue($entity->$pKey, null, $this->pdoFieldType(@$columns[$pKey]['type']))
            ));
        $entity->beforeDelete($q);
        DB::execute($q);
        $entity->afterDelete();
    }

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
        if (array_key_exists($id, $this->registry))
        {
            return $this->registry[$id];
        }

        $pKey = $this->getPrimaryKey();
        $columns = $this->getColumns();
        $q = $this->createSelectQuery();
        $q->where($q->expr->eq($pKey,
            $q->bindValue($id, null, $this->pdoFieldType(@$columns[$pKey]['type']))));
        $entity = $this->loadOneFromQuery($q);

        if (null !== $entity)
        {
            $this->registry[$id] = $entity;
        }
        return $entity;
    }

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
            $columns = $this->getColumns();
            $q->select('*');
            if (count($this->ordering))
            {
                foreach ($this->ordering as $orderBy)
                {
                    call_user_func_array(array($q, 'orderBy'), $orderBy);
                }
            }
            elseif (isset($columns['position']))
            {
                $q->orderBy('position');
            }
        }
        return $q;
    }

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
        $q->select($q->alias($q->expr->count('*'), 'record_count'));
        $q->from($this->getTableName());
        $q->limit(1);
        return $q;
    }

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

    //@codeCoverageIgnoreStart
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
    //@codeCoverageIgnoreEnd

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
            case 'entity':
                $type = PDO::PARAM_INT;
                break;
            case 'float':
                $type = null;
                break;
            case 'string':
            case 'timestamp':
            case 'time':
            case 'date':
                $type = PDO::PARAM_STR;
                break;
            default:
                throw new InvalidArgumentException('Unknown field type: ' . $ormFieldType);
        }
        return $type;
    }

    /**
     * Возвращает значение поля, пригодное для использования с PDO
     *
     * @param mixed  $ormValue      значение поля
     * @param string $ormFieldType  см. {@link hasColumns()}
     *
     * @throws InvalidArgumentException
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

        switch ($ormFieldType)
        {
            case 'entity':
                $ormFieldType = 'integer';
                if (is_object($ormValue))
                {
                    $ormValue = $ormValue->{$this->getPrimaryKey()};
                }
        }

        return $this->getDriver()->pdoFieldValue($ormValue, $ormFieldType);
    }

    /**
     * Фабрика сущностей
     *
     * @param array $values
     *
     * @throws InvalidArgumentException
     *
     * @return ORM_Entity
     *
     * @since 1.00
     */
    protected function entityFactory(array $values)
    {
        if (!array_key_exists($this->getPrimaryKey(), $values))
        {
            throw new InvalidArgumentException('Primary key value not found $values argument.');
        }

        $id = $values[$this->getPrimaryKey()];
        if (array_key_exists($id, $this->registry))
        {
            return $this->registry[$id];
        }

        $entityClass = $this->getEntityClass();
        foreach ($this->getColumns() as $name => $attrs)
        {
            switch (@$attrs['type'])
            {
                case 'date':
                case 'time':
                case 'timestamp':
                    $values[$name] = new DateTime($values[$name]);
                    break;
            }
        }
        $entity = new $entityClass($this->plugin, $values);
        $this->registry[$id] = $entity;
        return $entity;
    }

    /**
     * Привязывает значения свойств объекта к запросу
     *
     * @param ORM_Entity $entity  объект
     * @param ezcQuery   $query   запрос
     */
    private function bindValuesToQuery(ORM_Entity $entity, ezcQuery $query)
    {
        /** @var ezcQueryInsert|ezcQueryUpdate $query */
        foreach ($this->getColumns() as $name => $attrs)
        {
            $type = $this->pdoFieldType(@$attrs['type']);
            $value = $this->pdoFieldValue($entity->getProperty($name), @$attrs['type']);
            $query->set($name, $query->bindValue($value, ":$name", $type));
        }
    }
}

