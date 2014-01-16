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
class ORM extends Eresus_Plugin
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
    public $kernel = '3.01a';

    /**
     * Название плагина
     * @var string
     */
    public $title = 'ORM';

    /**
     * Описание плагина
     * @var string
     */
    public $description = 'Средства ORM для использования в других плагинах';

    /**
     * Менеджер
     * @var ORM_Manager
     * @since 3.00
     */
    private $manager = null;

    /**
     * Возвращает менеджера ORM
     * @return ORM_Manager
     */
    public static function getManager()
    {
        /** @var ORM $orm */
        $orm = Eresus_Plugin_Registry::getInstance()->load('orm');
        if (null === $orm->manager)
        {
            $orm->manager = new ORM_Manager();
        }
        return $orm->manager;
    }

    /**
     * Возвращает объект таблицы для указанной сущности указанного плагина
     *
     * @param Eresus_Plugin|string $plugin      плагин, которому принадлежит сущность
     * @param string               $entityName  имя сущности (без имени плагина и слова «Entity»)
     *
     * @return ORM_Table
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @since 1.00
     */
    public static function getTable($plugin, $entityName)
    {
        if (is_string($plugin))
        {
            $name = $plugin;
            $plugin = Eresus_Plugin_Registry::getInstance()->load($plugin);
            if (false === $plugin)
            {
                throw new RuntimeException(sprintf('Plugin "%s" not found or inactive', $name));
            }
        }
        return self::getManager()->getTable($plugin, $entityName);
    }
}

