<?php
/**
 * Драйвер MySQL
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
 * Драйвер MySQL
 *
 * @package ORM
 * @since 1.00
 */
class ORM_Driver_MySQL extends ORM_Driver_SQL
{
    /**
     * Возвращает выражение SQL для описания поля таблицы
     *
     * @param ORM_Field_Abstract $field  поле
     *
     * @return string  SQL
     */
    protected function getFieldDefinition(ORM_Field_Abstract $field)
    {
        $sql = parent::getFieldDefinition($field);
        $sql = preg_replace('/^(\w+)/', '`$1`', $sql);
        return $sql;
    }

    /**
     * Возвращает выражение SQL для создания таблицы
     *
     * @param string   $name        имя таблицы
     * @param string[] $fields      выражения для полей
     * @param string   $primaryKey  выражение для главного ключа
     * @param string[] $indexes     выражения для индексов
     *
     * @return string  SQL
     */
    protected function getCreateTableDefinition($name, $fields, $primaryKey, $indexes)
    {
        return "CREATE TABLE $name ("
        . implode(', ', array_merge($fields, array($primaryKey), $indexes))
        . ') ENGINE InnoDB DEFAULT CHARSET=utf8';
    }

    /**
     * Возвращает объявление индекса
     *
     * @param string $name
     * @param array  $params
     *
     * @return string  SQL
     */
    protected function getIndexDefinition($name, array $params)
    {
        foreach ($params['fields'] as &$fieldName)
        {
            $fieldName = "`$fieldName`";
        }
        $sql = parent::getIndexDefinition($name, $params);
        return $sql;
    }
}

