<?php
/**
 * Менеджер ORM
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
 * Менеджер ORM
 *
 * @package ORM
 */
class ORM_Manager
{
    /**
     * Драйвер СУБД
     * @var ORM_Driver_SQL
     * @since 3.00
     */
    private $driver = null;

    /**
     * Реестр таблиц
     *
     * @var ORM_Table[]
     * @since 1.00
     */
    private $tables = array();

    /**
     * Типы полей
     *
     * @var array
     * @since 1.00
     */
    private $filedTypes = array(
        'bindings' => 'ORM_Field_Bindings',
        'boolean' => 'ORM_Field_Boolean',
        'date' => 'ORM_Field_Date',
        'datetime' => 'ORM_Field_Datetime',
        'entity' => 'ORM_Field_Entity',
        'entities' => 'ORM_Field_Entities',
        'float' => 'ORM_Field_Float',
        'integer' => 'ORM_Field_Integer',
        'string' => 'ORM_Field_String',
        'timestamp' => 'ORM_Field_Timestamp'
    );

    /**
     * Задаёт используемый драйвер СУБД
     *
     * Внимание! Этот метод можно вызывать только до первого обращения к {@link getDriver()}.
     *
     * @param ORM_Driver_SQL $driver
     *
     * @throws LogicException
     *
     * @since 3.00
     */
    public function setDriver(ORM_Driver_SQL $driver)
    {
        if (null !== $this->driver)
        {
            throw new LogicException('ORM Driver is already set');
        }
        $this->driver = $driver;
    }

    /**
     * Возвращает используемый драйвер СУБД
     *
     * @return ORM_Driver_SQL
     *
     * @since 3.00
     */
    public function getDriver()
    {
        if (null === $this->driver)
        {
            $this->driver = new ORM_Driver_MySQL($this);
        }
        return $this->driver;
    }

    /**
     * Возвращает объект таблицы для указанной сущности указанного плагина
     *
     * @param Plugin|TPlugin $plugin      плагин, которому принадлежит сущность
     * @param string         $entityName  имя сущности (без имени плагина и слова «Entity»)
     *
     * @return ORM_Table
     *
     * @throws InvalidArgumentException
     *
     * @since 1.00
     */
    public function getTable($plugin, $entityName)
    {
        if (!($plugin instanceof Plugin) && !($plugin instanceof TPlugin))
        {
            throw new InvalidArgumentException(
                sprintf('Argument 1 passed to %s must be Plugin or TPlugin instance, %s given',
                    __METHOD__, is_object($plugin) ? get_class($plugin) : gettype($plugin)));
        }
        $className = get_class($plugin);
        if ($plugin instanceof TPlugin)
        {
            // Удаляем букву «T» из начала имени класса
            $className = substr($className, 1);
        }
        $className .= '_Entity_Table_' . $entityName;
        if (!array_key_exists($className, $this->tables))
        {
            $this->tables[$className] = new $className($plugin, $this->getDriver());
        }
        return $this->tables[$className];
    }

    /**
     * Возвращает возможные типы полей
     *
     * @return ORM_Field_Abstract[]
     *
     * @since 3.00
     */
    public function getFieldTypes()
    {
        return $this->filedTypes;
    }

    /**
     * Регистрирует тип поля
     *
     * $typeClass должен быть потомком ORM_Field_Abstract и содержать в имени строку «_Field_».
     * Также должен существовать класс унаследованный от X, имя которого совпадает с $typeClass,
     * но строка «_Field_» заменена на «_Driver_SQL_»
     *
     * @param string $typeName   имя типа (латинские буквы в нижнем регистре и цифры)
     * @param string $typeClass  имя класса типа
     *
     * @since 3.00
     */
    public function registerField($typeName, $typeClass)
    {
        $this->filedTypes[$typeName] = $typeClass;
    }

    /**
     * Возвращает таблицу по имени класса сущности
     *
     * @param string $entityClass
     *
     * @throws InvalidArgumentException
     *
     * @return ORM_Table|null
     *
     * @since 3.00
     */
    public function getTableByEntityClass($entityClass)
    {
        if ('' === strval($entityClass))
        {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s can not be blank', __METHOD__));
        }
        $entityPluginName = substr($entityClass, 0, strpos($entityClass, '_'));
        $entityPluginName = strtolower($entityPluginName);
        $plugin = Eresus_Kernel::app()->getLegacyKernel()->plugins
            ->load($entityPluginName);
        if (!$plugin)
        {
            return null;
        }
        $entityName = substr($entityClass, strrpos($entityClass, '_') + 1);
        $table = self::getTable($plugin, $entityName);
        return $table;
    }
}

