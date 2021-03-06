<?php
/**
 * Поле типа «string»
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
 * Поле типа «string»
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Field_String extends ORM_Field_Abstract
{
    /**
     * Возвращает имя типа
     *
     * @return string
     *
     * @since 3.00
     */
    public function getTypeName()
    {
        return 'string';
    }

    /**
     * Возвращает соответствующий тип PDO (PDO::PARAM_…) или null
     *
     * @return null|int
     *
     * @since 3.00
     */
    public function getPdoType()
    {
        return PDO::PARAM_STR;
    }

    /**
     * Возвращает выражение SQL для описания поля при создании таблицы
     *
     * @return string
     */
    public function getSqlFieldDefinition()
    {
        $sql = $this->getName() . ' ';
        if ($this->getParam('length') && $this->getParam('length') <= 255)
        {
            $sql .= 'CHARACTER VARYING(' . $this->getParam('length') . ')';
        }
        else
        {
            $sql .= 'CHARACTER LARGE OBJECT';
        }
        return $sql;
    }

    /**
     * Возвращает список возможных необязательных параметров
     *
     * @return string[]
     *
     * @since 3.00
     */
    protected function getOptionalParams()
    {
        return array('length');
    }
}

