<?php
/**
 * Поле типа «string» для MySQL
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
 * Поле типа «string» для MySQL
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Driver_MySQL_String extends ORM_Driver_SQL_Field
{
    /**
     * Возвращает выражение SQL для описания поля при создании таблицы
     *
     * @return string
     */
    public function getSqlFieldDefinition()
    {
        $sql = $this->field->getName() . ' ';
        if ($this->field->hasParam('length') && $this->field->getParam('length') <= 255)
        {
            $sql .= 'VARCHAR(' . $this->field->getParam('length') . ')';
        }
        elseif (!$this->field->hasParam('length') || $this->field->getParam('length') <= 65535)
        {
            $sql .= 'TEXT';
        }
        else
        {
            $sql .= 'LONGTEXT';
        }
        return $sql;
    }
}

