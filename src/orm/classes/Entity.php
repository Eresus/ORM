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
     * @since 3.00
     */
    const IS_NEW = 1;

    /**
     * Состояние сущности: объект соответствует записи в БД
     * @since 3.00
     */
    const IS_PERSISTENT = 2;

    /**
     * Состояние сущности: в объекте есть изменения, несохранённые в БД
     * @since 3.00
     */
    const IS_DIRTY = 3;

    /**
     * Состояние сущности: объект удалён из БД
     * @since 3.00
     */
    const IS_DELETED = 4;

    /**
     * Таблица, где хранится этот объект
     *
     * @var ORM_Table
     */
    private $table;

    /**
     * Состояние сущности
     * @var int
     *
     * @since 3.00
     */
    private $state = self::IS_NEW;

    /**
     * Необработанные значения свойств (значения PDO)
     *
     * По мере обращения к свойствам сущности, эти значения трансформируются в значения ORM и
     * помещаются в {@link $ormValues}.
     *
     * @var array
     */
    private $pdoValues = array();

    /**
     * Кэш значений свойств, приведённых к типам ORM
     *
     * @var array
     */
    private $ormValues = array();

    /**
     * Конструктор
     *
     * @param array $pdoValues  исходные PDO-значения полей
     *
     * @return ORM_Entity
     *
     * @since 1.00
     */
    public function __construct(array $pdoValues = array())
    {
        $this->table = ORM::getManager()->getTableByEntityClass(get_class($this));
        $columns = $this->getTable()->getColumns();
        foreach ($pdoValues as $name => $value)
        {
            if (!array_key_exists($name, $columns))
            {
                continue;
            }
            if ($columns[$name]->isVirtual())
            {
                continue;
            }
            $this->pdoValues[$name] = $value;
        }
        if ($this->getPrimaryKey() !== null)
        {
            $this->setEntityState(self::IS_PERSISTENT);
        }
    }

    /**
     * "Магический" метод для доступа к свойствам объекта
     *
     * Если есть геттер (метод, имя которого состоит из префикса "get" и имени свойства), вызывает
     * его для получения значения. В противном случае вызывает {@link getPdoValue}.
     *
     * Результат метода кэшируется.
     *
     * @param string $property  имя свойства
     *
     * @return mixed  ORM-значение свойства
     *
     * @uses getPdoValue()
     * @since 1.00
     */
    public function __get($property)
    {
        if (!array_key_exists($property, $this->ormValues))
        /*
         * Если значение PDO запрошенного свойства ещё трансформировано в значение ORM, делаем
         * это сейчас.
         */
        {
            $pdoValue = $this->getPdoValue($property);

            $getter = 'get' . $property;
            if (method_exists($this, $getter))
            {
                $ormValue = $this->$getter($pdoValue);
            }
            else
            {
                $table = $this->getTable();
                $columns = $table->getColumns();
                if (array_key_exists($property, $columns))
                {
                    if (null === $pdoValue && $columns[$property]->isVirtual())
                    {
                        $pdoValue = $columns[$property]->evaluateVirtualValue($this);
                    }
                    $ormValue = $columns[$property]->pdo2orm($pdoValue);
                }
                else
                {
                    $ormValue = $pdoValue;
                }
            }
            $this->ormValues[$property] = $ormValue;
        }
        return $this->ormValues[$property];
    }

    /**
     * "Магический" метод для установки свойств объекта
     *
     * Если есть сеттер (метод, имя которого состоит из префикса "set" и имени свойства), вызывает
     * его для установки значения. В противном случае вызывает {@link setOrmValue()}.
     *
     * @param string $property  имя свойства
     * @param mixed  $value     ORM-значение
     *
     * @return void
     *
     * @uses setPdoValue()
     * @since 1.00
     */
    public function __set($property, $value)
    {
        $setter = 'set' . $property;
        if (method_exists($this, $setter))
        {
            unset($this->ormValues[$property]);
            $this->$setter($value);
        }
        else
        {
            $this->setOrmValue($property, $value);
        }
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
        return $this->table;
    }

    /**
     * Возвращает состояние объекта
     *
     * См. константы ORM_Entity::IS_…
     *
     * @return int
     *
     * @since 3.00
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
     * @since 3.00
     */
    protected function setEntityState($state)
    {
        $this->state = intval($state);
    }

    /**
     * Возвращает значение основного ключа для этого объекта
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function getPrimaryKey()
    {
        return $this->{$this->getTable()->getPrimaryKey()};
    }

    /**
     * Возвращает PDO-значение свойства
     *
     * Сначала метод пытается получить значение из {@link $ormValues}, если там значения нет, то
     * из {@link $pdoValues}.
     *
     * @param string $property  имя свойства
     *
     * @return mixed  PDO-значение свойства
     *
     * @since 3.00
     */
    public function getPdoValue($property)
    {
        if (array_key_exists($property, $this->ormValues))
        {
            $value = $this->getOrmValue($property);
            $columns = $this->getTable()->getColumns();
            if (array_key_exists($property, $columns))
            {
                $value = $columns[$property]->orm2pdo($value);
            }
        }
        else
        {
            $value = array_key_exists($property, $this->pdoValues)
                ? $this->pdoValues[$property]
                : null;
        }
        return $value;
    }

    /**
     * Устанавливает PDO-значение свойства
     *
     * При этом удаляется значение этого свойства из {@link $ormValues}.
     *
     * @param string $property  имя свойства
     * @param mixed  $value     PDO-значение
     *
     * @return void
     *
     * @since 3.00
     */
    public function setPdoValue($property, $value)
    {
        $this->pdoValues[$property] = $value;
        unset($this->ormValues[$property]);
    }

    /**
     * Возвращает ORM-значение свойства
     *
     * @param string $property  имя свойства
     *
     * @return mixed  ORM-значение свойства
     *
     * @since 3.00
     */
    public function getOrmValue($property)
    {
        return array_key_exists($property, $this->ormValues) ? $this->ormValues[$property] : null;
    }

    /**
     * Устанавливает ORM-значение свойства
     *
     * @param string $property  имя свойства
     * @param mixed  $value     ORM-значение
     *
     * @return void
     *
     * @since 3.00
     */
    public function setOrmValue($property, $value)
    {
        $this->ormValues[$property] = $value;
    }

    /**
     * Вызывается перед изменением в БД
     *
     * @param ezcQuery $query  запрос, который будет выполнен для сохранения записи
     *
     * @return ezcQuery
     *
     * @since 1.00
     */
    public function beforeSave(ezcQuery $query)
    {
        return $query;
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
        foreach ($this->getTable()->getColumns() as $column)
        {
            $column->afterEntitySave($this);
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
        foreach ($this->getTable()->getColumns() as $key => $column)
        {
            $column->afterEntityDelete($this, $key);
        }
        $this->setEntityState(self::IS_DELETED);
    }
}

