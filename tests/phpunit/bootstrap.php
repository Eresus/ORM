<?php
/**
 * Стартовый файл тестов
 *
 * @package ORM
 * @subpackage Tests
 */

/**
 * Путь к папке исходные кодов
 */
define('TESTS_SRC_DIR', realpath(__DIR__ . '/../../src'));

spl_autoload_register(
    function ($class)
    {
        if ('ORM' == $class)
        {
            require TESTS_SRC_DIR . '/orm.php';
        }
        elseif (substr($class, 0, 4) == 'ORM_')
        {
            $path = str_replace('_', '/', substr($class, 4)) . '.php';
            require TESTS_SRC_DIR . '/orm/classes/' . $path;
        }
    }
);

require_once 'stubs.php';

