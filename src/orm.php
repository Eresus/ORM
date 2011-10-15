<?php
/**
 * ORM
 *
 * ��������-����������� �����������.
 *
 * @version ${product.version}
 *
 * @copyright 2011, ������ ������������ <mihalych@vsepofigu.ru>
 * @license http://www.gnu.org/licenses/gpl.txt	GPL License 3
 * @author ������ ������������ <mihalych@vsepofigu.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package ORM
 *
 * $Id$
 */

/**
 * �������� ����� �������
 *
 * @package ORM
 */
class ORM extends Plugin
{
	/**
	 * ������ �������
	 * @var string
	 */
	public $version = '${product.version}';

	/**
	 * ��������� ������ ����
	 * @var string
	 */
	public $kernel = '2.15';

	/**
	 * �������� �������
	 * @var string
	 */
	public $title = 'ORM';

	/**
	 * ������� �������
	 * @var string
	 */
	public $description = '�������� ORM ��� ������������� � ������ ��������';

	/**
	 * ��� ������
	 *
	 * @var array
	 * @since 1.00
	 */
	private static $tables = array();

	/**
	 * ���� �����
	 *
	 * @var array
	 * @since 1.00
	 */
	private static $filedTypes = array('boolean', 'date', 'float', 'integer', 'string', 'time',
		'timestamp');

	/**
	 * ���������� ������ ������� ��� ��������� �������� ���������� �������
	 *
	 * @param Plugin $plugin      ������, �������� ����������� ��������
	 * @param string $entityName  ��� �������� (��� ����� ������� � ����� �Entity�)
	 *
	 * @return ORM_Table
	 *
	 * @since 1.00
	 */
	public static function getTable(Plugin $plugin, $entityName)
	{
		$className = get_class($plugin) . '_Entity_Table_' . $entityName;
		if (!isset(self::$tables[$className]))
		{
			self::$tables[$className] = new $className($plugin);
		}
		return self::$tables[$className];
	}
	//-----------------------------------------------------------------------------

	/**
	 * ���������� ��������� ���� �����
	 *
	 * @return array
	 *
	 * @since 1.00
	 */
	public static function fieldTypes()
	{
		return self::$filedTypes;
	}
	//-----------------------------------------------------------------------------
}
