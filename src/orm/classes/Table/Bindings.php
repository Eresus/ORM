<?php
/**
 * Таблица привязок
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
 * Таблица привязок
 *
 * @package ORM
 * @since 3.00
 */
class ORM_Table_Bindings extends ORM_Table
{
    /**
     * @var ORM_Table
     */
    private $baseTable;

    /**
     * @var string
     */
    private $property;

    /**
     * Конструктор
     *
     * @param ORM_Table $baseTable  таблица, хранящая базовые объекты
     * @param string    $property   свойство, привязки к которому хранит таблица
     */
    public function __construct(ORM_Table $baseTable, $property)
    {
        assert('is_string($property)');
        assert('"" != $property');
        $this->baseTable = $baseTable;
        $this->property = $property;
        parent::__construct($baseTable->getPlugin(), $baseTable->getDriver());
    }

    /**
     * Возвращает имя поля, относящегося к базовой таблице
     *
     * @return string
     */
    public function getSourceField()
    {
        return preg_replace('/^.*?_/', '', $this->baseTable->getName());
    }

    /**
     * Описание таблицы
     */
    protected function setTableDefinition()
    {
        $this->setTableName($this->baseTable->getName() . '_' . $this->property);

        $sourceField = $this->getSourceField();
        $this->hasColumns(array(
            $sourceField => array(
                'type' => 'integer',
                'unsigned' => true
            ),
            $this->property => array(
                'type' => 'integer',
                'unsigned' => true
            ),
        ));
        $this->setPrimaryKey(array($sourceField, $this->property));
    }
}

