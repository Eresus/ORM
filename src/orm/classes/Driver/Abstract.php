<?php
/**
 * Абстрактный драйвер СУБД
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
 * Абстрактный драйвер СУБД
 *
 * @package ORM
 * @since 2.01
 */
abstract class ORM_Driver_Abstract
{
    /**
     * Создаёт таблицу
     *
     * @param string $tableName   имя таблицы
     * @param array  $columns     описание столбцов
     * @param string $primaryKey  первичный ключ
     * @param array  $indexes     описание индексов
     *
     * @return void
     *
     * @since 2.01
     */
    abstract public function createTable($tableName, array $columns, $primaryKey, array $indexes);

    /**
     * Удаляет таблицу
     *
     * @param string $tableName  имя таблицы
     *
     * @return void
     *
     * @since 2.01
     */
    abstract public function dropTable($tableName);

    /**
     * Преобразует значение поля ORM в значение PDO
     *
     * @param mixed  $ormValue      значение поля
     * @param string $ormFieldType  тип поля
     *
     * @return mixed
     *
     * @since 2.01
     */
    abstract public function pdoFieldValue($ormValue, $ormFieldType);
}

