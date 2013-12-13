<?php
/**
 * Поле типа «datetime»
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
 * Поле типа «datetime»
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Field_Datetime extends ORM_Field_Abstract
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
        return 'datetime';
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
     * Преобразует значение ORM в значение PDO
     *
     * @param mixed $ormValue
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function orm2pdo($ormValue)
    {
        if (is_null($ormValue))
        {
            return null;
        }
        if (!($ormValue instanceof DateTime))
        {
            throw new InvalidArgumentException('$ormValue must be a DateTime object');
        }
        /* @var DateTime $ormValue */
        return $ormValue->format('Y-m-d H:i:s');
    }

    /**
     * Преобразует значение PDO в значение ORM
     *
     * @param mixed $pdoValue
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function pdo2orm($pdoValue)
    {
        return new DateTime(strval($pdoValue));
    }

    /**
     * Возвращает выражение SQL для описания поля при создании таблицы
     *
     * @return string
     */
    public function getSqlFieldDefinition()
    {
        return $this->getName() . ' TIMESTAMP';
    }
}

