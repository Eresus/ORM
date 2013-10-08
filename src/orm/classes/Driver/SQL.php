<?php
/**
 * Драйвер на основе стандарта SQL
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
 * Драйвер на основе стандарта SQL
 *
 * @package ORM
 * @since 2.02
 */
class ORM_Driver_SQL
{
    /**
     * Создаёт таблицу
     *
     * @param ORM_Table $table
     *
     * @return void
     *
     * @since 2.02
     */
    public function createTable(ORM_Table $table)
    {
        if ($table->isAlias())
        {
            return;
        }
        $db = DB::getHandler();
        $tableName = $db->options->tableNamePrefix . $table->getName();

        $fieldDefinitions = array();
        foreach ($table->getColumns() as $name => $field)
        {
            if (!$field->isVirtual())
            {
                $fieldDefinitions []= $this->getFieldDefinition($name, $field);
            }
        }
        $primaryKey = $this->getPrimaryKeyDefinition($table->getPrimaryKey());
        $indexDefinitions = array();
        foreach ($table->getIndexes() as $name => $params)
        {
            $indexDefinitions []= $this->getIndexDefinition($name, $params);
        }
        $sql = $this->getCreateTableDefinition($tableName, $fieldDefinitions, $primaryKey,
            $indexDefinitions);
        $db->exec($sql);
    }

    /**
     * Удаляет таблицу
     *
     * @param ORM_Table $table
     *
     * @return void
     *
     * @since 2.02
     */
    public function dropTable(ORM_Table $table)
    {
        if ($table->isAlias())
        {
            return;
        }
        $db = DB::getHandler();
        $tableName = $db->options->tableNamePrefix . $table->getName();

        $sql = $this->getDropTableDefinition($tableName);
        $db->exec($sql);
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
            . ')';
    }

    /**
     * Возвращает выражение SQL для удаления таблицы
     *
     * @param string   $name        имя таблицы
     *
     * @return string  SQL
     */
    protected function getDropTableDefinition($name)
    {
        return "DROP TABLE $name";
    }

    /**
     * Возвращает выражение SQL для описания поля таблицы
     *
     * @param string             $name   имя поля
     * @param ORM_Field_Abstract $field  поле
     *
     * @return string  SQL
     */
    protected function getFieldDefinition($name, ORM_Field_Abstract $field)
    {
        $sql = $field->getSqlFieldDefinition($name);
        if ($field->hasParam('default'))
        {
            $sql .= ' DEFAULT ';
            if ($field->getParam('default') === null)
            {
                $sql .= 'NULL';
            }
            elseif ($field->getPdoType() == PDO::PARAM_STR)
            {
                $sql .= "'" . $field->getParam('default') . "'";
            }
            elseif ($field->getPdoType() == PDO::PARAM_BOOL)
            {
                $sql .= $field->getParam('default') ? '1' : '0';
            }
            else
            {
                $sql .= $field->getParam('default');
            }
        }
        return $sql;
    }

    /**
     * Возвращает выражение SQL для главного ключа
     *
     * @param string $key
     *
     * @return string
     */
    protected function getPrimaryKeyDefinition($key)
    {
        return 'PRIMARY KEY (' . $key . ')';
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
        return 'KEY ' . $name . ' (' . implode(', ', $params['fields']) . ')';
    }
}

