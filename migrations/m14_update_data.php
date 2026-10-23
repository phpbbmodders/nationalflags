<?php
/**
*
* @package National Flags
* @copyright (c) 2020 Rich Mcgirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\nationalflags\migrations;

/* Due to m13 storing a legacy non-UTF-8 name the query in this file doesn't find anything in the database so nothing is updated.
 *  Migration file m15 corrects the issue with the cedilla.
 */
class m14_update_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\rmcgirr83\nationalflags\migrations\m13_update_data'];
	}

	public function update_data()
	{
		return [
			['custom', [
				[&$this, 'flag_update_image']
			]],
		];
	}

	public function flag_update_image()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'flags'))
		{
			$sql_ary = [
				[
					'flag_name'		=> 'Curacao',
					'flag_image'	=> 'cw.png',
				],
			];
			foreach ($sql_ary as $num => $flag)
			{
				$sql = 'UPDATE ' . $this->table_prefix . 'flags
					SET ' . $this->db->sql_build_array('UPDATE', [
								'flag_name'	=> (string) $flag['flag_name']]
							) .
					" WHERE flag_image = '" . (string) $flag['flag_image'] . "'";
				$this->db->sql_query($sql);
			}
		}
	}
}
