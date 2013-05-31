<?php
/**
 * Поставщик данных из таблицы модуля ORM.
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
 * Поставщик данных из таблицы модуля ORM
 *
 * @package ORM
 */
class ORM_UI_List_DataProvider implements UI_List_DataProvider_Interface
{
    /**
     * Основной объект плагина-владельца
     *
     * @var Eresus_Plugin|TPlugin
     */
    private $plugin;

    /**
     * Таблица БД
     *
     * @var ORM_Table
     */
    private $table;

    /**
     * Фильтр
     *
     * @var array
     */
    private $filter = array();

    /**
     * Запросы полнотекстового поиска
     *
     * @var array
     */
    //private $fulltext = array();

    /**
     * Правила сортировки
     *
     * @var array
     */
    private $orderBy = array();

    /**
     * Конструктор
     *
     * @param Eresus_Plugin|TPlugin $plugin
     * @param string $entityName
     *
     * @return ORM_UI_List_DataProvider
     *
     * @since 1.00
     */
    public function __construct($plugin, $entityName)
    {
        $this->plugin = $plugin;
        $this->table = ORM::getTable($plugin, $entityName);
    }

    /**
     * Добавляет разрешающее условие фильтрации
     *
     * @param string $property  имя свойства
     * @param mixed  $value     значение
     * @param string $cond      условие (=, <, >, <=, =>)
     *
     * @return void
     *
     * @since 1.00
     */
    public function filterInclude($property, $value, $cond = '=')
    {
        $this->filter []= array($property, $value, $cond);
    }

    /* *
     * Добавляет запрос полнотекстового поиска
     *
     * @param array  $properties  имена свойств
     * @param string $query       запрос
     *
     * @return void
     *
     * @since 1.00
     */
    /*public function filterFullText($properties, $query)
    {
        $this->fulltext []= array($properties, $query);
    }*/

    /**
     * Задаёт сортировку списка
     *
     * @param string $field1  имя поля
     * @param bool   $desc1   обратное направление (по умолчанию false)
     * @param ...
     *
     * @return void
     *
     * @since 1.00
     */
    public function orderBy($field1, $desc1 = false)
    {
        $this->orderBy = func_get_args();
    }

    /**
     * Возвращает элементы списка
     *
     * @param int $limit   максимум элементов, который следует вернуть
     * @param int $offset  сколько элементов пропустить
     *
     * @return array
     *
     * @since 1.00
     */
    public function getItems($limit = null, $offset = 0)
    {
        $items = array();
        $query = $this->table->createSelectQuery();
        for ($i = 0; $i < count($this->orderBy); $i += 2)
        {
            $field = $this->orderBy[$i];
            $dir = @$this->orderBy[$i + 1] ? ezcQuerySelect::DESC : ezcQuerySelect::ASC;
            $query->orderBy($field, $dir);
        }

        $this->setFilter($query);
        $entities = $this->table->loadFromQuery($query, $limit, $offset);
        foreach ($entities as $entity)
        {
            $items []= new ORM_UI_List_Item($entity);
        }

        return $items;
    }

    /**
     * Возвращает общее количество записей в списке
     *
     * @return int
     *
     * @since 1.00
     */
    public function getCount()
    {
        $query = $this->table->createCountQuery();
        $this->setFilter($query);
        return $this->table->count($query);
    }

    /**
     * Применяет фильтр к запросу
     *
     * @param ezcQuerySelect $query
     *
     * @return void
     *
     * @since 1.00
     */
    private function setFilter(ezcQuerySelect $query)
    {
        $map = array('<' => 'lt', '<=' => 'lte', '>' => 'gt', '>=' => 'gte');
        $andParts = array();
        foreach ($this->filter as $rule)
        {
            $method = null;
            if ($rule[2] == '=')
            {
                $method = 'eq';
                if (is_array($rule[1]))
                {
                    $method = 'in';
                }
            }
            elseif (isset($map[$rule[2]]))
            {
                $method = $map[$rule[2]];
            }
            if ($method)
            {
                $andParts []= $query->expr->$method($rule[0],
                    is_array($rule[1]) ? $rule[1] : $query->bindValue($rule[1]));
            }
        }
        /*
        if (count($this->fulltext))
        {
            foreach ($this->fulltext as $fulltext)
            {
                $q = $this->buildFullTextQuery($fulltext[0], $fulltext[1]);
                if ($q)
                {
                    $andParts []= '(' . $q . ')';
                }
            }
        }
        */

        if (count($andParts))
        {
            $where = call_user_func_array(array($query->expr, 'lAnd'), $andParts);
            $query->where($where);
        }
    }

    /* *
     * Строит запрос полнотекствового поиска
     *
     * @param array $fields  поля
     * @param string $query  поисковый запрос
     *
     * @return string
     *
     * @since 1.00
     */
    /*private function buildFullTextQuery($fields, $query)
    {
        if (class_exists('SearchAPI_QueryBuilder_LIKE'))
        {
            $builder = new SearchAPI_QueryBuilder_LIKE();
            return $builder->getWherePart($fields, $query);
        }
        else
        {
            eresus_log(__METHOD__, LOG_ERR, 'Class "SearchAPI_QueryBuilder_LIKE" not found!');
            return '';
        }
    }*/
}

