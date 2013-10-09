<?php
/**
 * Базовый класс для переопределения типов полей для разных диалектов SQL
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
 * Базовый класс для переопределения типов полей для разных диалектов SQL
 *
 * @package ORM
 * @since 3.00
 */
abstract class ORM_Driver_SQL_Field
{
    /**
     * @var ORM_Field_Abstract
     */
    protected $field;

    /**
     * @param ORM_Field_Abstract $field
     */
    public function __construct(ORM_Field_Abstract $field)
    {
        $this->field = $field;
    }

    /**
     * Возвращает выражение SQL для описания поля при создании таблицы
     *
     * @param string $name  имя поля
     *
     * @return string
     */
    abstract public function getSqlFieldDefinition($name);
}
