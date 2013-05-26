<?php
/**
 * Абстрактная кэшируемая таблица БД
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
 * Абстрактная кэшируемая таблица БД
 *
 * Можно использовать для таблиц небольшого размера, записи которых часто нужны. Все записи читаются
 * разом и помещаются в кэш, откуда потом и выдаются по запросу.
 *
 * @package ORM
 * @since 1.00
 */
abstract class ORM_Table_Cached extends ORM_Table
{
    /**
     * Кэш записей
     *
     * @var array
     */
    protected $cache = null;

    /**
     * @see ORM_Table::find()
     */
    public function find($id)
    {
        $this->fillCache();
        if (isset($this->cache[$id]))
        {
            return $this->cache[$id];
        }
        return null;
    }

    /**
     * Заполняет кэш, если он пуст
     *
     * @return void
     *
     * @since 1.00
     */
    protected function fillCache()
    {
        if (null === $this->cache)
        {
            $q = $this->createSelectQuery();
            $tmp = $this->loadFromQuery($q);
            $this->cache = array();
            foreach ($tmp as $item)
            {
                $this->cache[$item->id] = $item;
            }
        }
    }
}

