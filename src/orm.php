<?php
/**
 * ORM
 *
 * Простое объектно-реляционное отображение для Eresus.
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
 * Основной класс плагина
 *
 * @package ORM
 */
class ORM extends Plugin
{
    /**
     * Версия плагина
     * @var string
     */
    public $version = '${product.version}';

    /**
     * Требуемая версия ядра
     * @var string
     */
    public $kernel = '3.00';

    /**
     * Название плагина
     * @var string
     */
    public $title = 'ORM';

    /**
     * Опиание плагина
     * @var string
     */
    public $description = 'Средства ORM для использования в других плагинах';

    /**
     * Драйвер СУБД
     * @var ORM_Driver_Abstract
     * @since 2.02
     */
    private static $driver = null;

    /**
     * Реестр таблиц
     *
     * @var ORM_Table[]
     * @since 1.00
     */
    private static $tables = array();

    /**
     * Типы полей
     *
     * @var array
     * @since 1.00
     */
    private static $filedTypes = array('boolean', 'date', 'datetime', 'entity', 'entities', 'float',
        'integer', 'string', 'time', 'timestamp');

    /**
     * Задаёт используемый драйвер СУБД
     *
     * @param ORM_Driver_Abstract $driver
     *
     * @since 2.02
     */
    public static function setDriver(ORM_Driver_Abstract $driver)
    {
        self::$driver = $driver;
    }

    /**
     * Возвращает используемый драйвер СУБД
     *
     * @return ORM_Driver_Abstract
     *
     * @since 2.02
     */
    public static function getDriver()
    {
        if (null === self::$driver)
        {
            self::$driver = new ORM_Driver_MySQL();
        }
        return self::$driver;
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
    public static function getTable($plugin, $entityName)
    {
        if (!($plugin instanceof Plugin) && !($plugin instanceof TPlugin))
        {
            throw new InvalidArgumentException(
                '$plugin must be Eresus_Plugin or TPlugin instance.'
            );
        }
        $className = get_class($plugin);
        if ($plugin instanceof TPlugin)
        {
            // Удаляем букву «T» из начала имени класса
            $className = substr($className, 1);
        }
        $className .= '_Entity_Table_' . $entityName;
        if (!isset(self::$tables[$className]))
        {
            self::$tables[$className] = new $className(self::getDriver(), $plugin);
        }
        return self::$tables[$className];
    }

    /**
     * Возвращает возможные типы полей
     *
     * @return array
     *
     * @since 1.00
     */
    public static function fieldTypes()
    {
        return self::$filedTypes;
    }
}

