<?php
/**
 * Абстрактное поле
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
 * Абстрактное поле
 *
 * @package ORM
 * @since 3.00
 */
abstract class ORM_Field_Abstract
{
    /**
     * Параметры поля
     *
     * @var array
     */
    protected $params;

    /**
     * Менеджер
     * @var ORM_Manager
     * @since 3.00
     */
    protected $manager;

    /**
     * Параметры поля
     *
     * @param array       $params
     * @param ORM_Manager $manager
     */
    public function __construct(array $params, ORM_Manager $manager)
    {
        $this->checkParams($params);
        $this->params = $params;
        $this->manager = $manager;
    }

    /**
     * Возвращает имя типа
     *
     * @return string
     *
     * @since 3.00
     */
    abstract public function getTypeName();

    /**
     * Возвращает true если это виртуальный тип (т. е. для него не надо создавать поле в таблице)
     *
     * @return bool
     *
     * @see evaluateVirtualValue()
     * @since 3.00
     */
    public function isVirtual()
    {
        return false;
    }

    /**
     * Вычисляет и возвращает значение виртуального поля
     *
     * @param ORM_Entity $entity
     * @param string     $fieldName
     *
     * @return mixed
     *
     * @see isVirtual()
     * @since 3.00
     */
    public function evaluateVirtualValue(ORM_Entity $entity, $fieldName)
    {
        return null;
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
        return null;
    }

    /**
     * Преобразует значение ORM в значение PDO
     *
     * @param mixed $ormValue
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function orm2pdo($ormValue)
    {
        return $ormValue;
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
        return $pdoValue;
    }

    /**
     * Возвращает true если указанный параметр задан
     *
     * @param string $name
     *
     * @return bool
     *
     * @since 3.00
     */
    public function hasParam($name)
    {
        assert('is_string($name)');
        return array_key_exists($name, $this->params);
    }

    /**
     * Возвращает значение параметра поля
     *
     * @param string $name
     *
     * @return mixed
     *
     * @since 3.00
     */
    public function getParam($name)
    {
        return $this->hasParam($name) ? $this->params[$name] : null;
    }

    /**
     * Может ли это поле участвовать в разделе WHERE запросов SQL
     *
     * @return bool
     *
     * @since 3.00
     */
    public function canBeUsedInWhere()
    {
        return !$this->isVirtual();
    }

    /**
     * Возвращает true если поле автоинкрементируемое
     *
     * @return bool
     */
    public function isAutoIncrementing()
    {
        return array_key_exists('autoincrement', $this->params) && $this->params['autoincrement'];
    }

    /**
     * Действия, выполняемые после создания таблицы
     *
     * @param ORM_Table $table
     * @param string    $field
     */
    public function afterTableCreate(ORM_Table $table, $field)
    {
    }

    /**
     * Действия, выполняемые после удаления таблицы
     *
     * @param ORM_Table $table
     * @param string    $field
     */
    public function afterTableDrop(ORM_Table $table, $field)
    {
    }

    /**
     * Действия, выполняемые после сохранения сущности
     *
     * @param ORM_Entity $entity
     * @param string     $field
     */
    public function afterEntitySave(ORM_Entity $entity, $field)
    {
    }

    /**
     * Действия, выполняемые после удаления сущности
     *
     * @param ORM_Entity $entity
     * @param string $field
     */
    public function afterEntityDelete(ORM_Entity $entity, $field)
    {
    }

    /**
     * Возвращает выражение SQL для описания поля при создании таблицы
     *
     * @param string $name  имя поля
     *
     * @throws LogicException
     *
     * @return string
     */
    public function getSqlFieldDefinition($name)
    {
        if (!$this->isVirtual())
        {
            throw new LogicException(get_class($this)
                . ' should override method "getSqlFieldDefinition"');
        }
        return '';
    }

    /**
     * Проверяет параметры в описании поля
     *
     * @param array $params
     *
     * @throws InvalidArgumentException
     *
     * @uses getValidParams()
     */
    protected function checkParams(array $params)
    {
        $unknown = array_diff(array_keys($params),
            $this->getRequiredParams(), $this->getOptionalParams(), array('default', 'comment'));
        if (count($unknown) > 0)
        {
            throw new InvalidArgumentException(
                sprintf('Unknown option(s) "%s" in field definition of type "%s"',
                    implode(',', $unknown), $this->getTypeName()));
        }

        $missed = array_diff($this->getRequiredParams(), array_keys($params));
        if (count($missed) > 0)
        {
            throw new InvalidArgumentException(
                sprintf('Missed required option(s) "%s" in field definition of type "%s"',
                    implode(',', $missed), $this->getTypeName()));
        }
    }

    /**
     * Возвращает список обязательных параметров
     *
     * @return string[]
     *
     * @since 3.00
     */
    protected function getRequiredParams()
    {
        return array();
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
        return array();
    }
}

