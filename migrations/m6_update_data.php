<?php
/**
*
* @package National Flags
* @copyright (c) 2015 Rich Mcgirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\nationalflags\migrations;

class m6_update_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['nationalflags_version']) && version_compare($this->config['nationalflags_version'], '1.0.4', '>=');
	}

	static public function depends_on()
	{
		return ['\rmcgirr83\nationalflags\migrations\m5_update_data'];
	}

	public function update_data()
	{
		return [
			['config.update', ['nationalflags_version', '1.0.4']],
			['custom', [
				[&$this, 'flag_update_images']
			]],
		];
	}

	public function flag_update_images()
	{
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'flags'))
		{
			$sql_ary = [
				[
					'flag_name'		=> 'Afghanistan',
					'flag_image'	=> 'af.png',
				],
				[
					'flag_name'		=> 'Albania',
					'flag_image'	=> 'al.png',
				],
				[
					'flag_name'		=> 'Algeria',
					'flag_image'	=> 'dz.png',
				],
				[
					'flag_name'		=> 'American Samoa',
					'flag_image'	=> 'as.png',
				],
				[
					'flag_name'		=> 'Andorra',
					'flag_image'	=> 'ad.png',
				],
				[
					'flag_name'		=> 'Angola',
					'flag_image'	=> 'ao.png',
				],
				[
					'flag_name'		=> 'Anguilla',
					'flag_image'	=> 'ai.png',
				],
				[
					'flag_name'		=> 'Antigua &amp; Barbuda',
					'flag_image'	=> 'ag.png',
				],
				[
					'flag_name'		=> 'Argentina',
					'flag_image'	=> 'ar.png',
				],
				[
					'flag_name'		=> 'Armenia',
					'flag_image'	=> 'am.png',
				],
				[
					'flag_name'		=> 'Aruba',
					'flag_image'	=> 'aw.png',
				],
				[
					'flag_name'		=> 'Australia',
					'flag_image'	=> 'au.png',
				],
				[
					'flag_name'		=> 'Austria',
					'flag_image'	=> 'at.png',
				],
				[
					'flag_name'		=> 'Azerbaijan',
					'flag_image'	=> 'az.png',
				],
				[
					'flag_name'		=> 'Bahamas',
					'flag_image'	=> 'bs.png',
				],
				[
					'flag_name'		=> 'Bahrain',
					'flag_image'	=> 'bh.png',
				],
				[
					'flag_name'		=> 'Bangladesh',
					'flag_image'	=> 'bd.png',
				],
				[
					'flag_name'		=> 'Barbados',
					'flag_image'	=> 'bb.png',
				],
				[
					'flag_name'		=> 'Belarus',
					'flag_image'	=> 'by.png',
				],
				[
					'flag_name'		=> 'Belgium',
					'flag_image'	=> 'be.png',
				],
				[
					'flag_name'		=> 'Belize',
					'flag_image'	=> 'bz.png',
				],
				[
					'flag_name'		=> 'Benin',
					'flag_image'	=> 'bj.png',
				],
				[
					'flag_name'		=> 'Bermuda',
					'flag_image'	=> 'bm.png',
				],
				[
					'flag_name'		=> 'Bhutan',
					'flag_image'	=> 'bt.png',
				],
				[
					'flag_name'		=> 'Bolivia',
					'flag_image'	=> 'bo.png',
				],
				[
					'flag_name'		=> 'Bonaire',
					'flag_image'	=> 'bl.png',
				],
				[
					'flag_name'		=> 'Bosnia &amp; Herzegovina',
					'flag_image'	=> 'ba.png',
				],
				[
					'flag_name'		=> 'Botswana',
					'flag_image'	=> 'bw.png',
				],
				[
					'flag_name'		=> 'Brazil',
					'flag_image'	=> 'br.png',
				],
				[
					'flag_name'		=> 'Brunei',
					'flag_image'	=> 'bn.png',
				],
				[
					'flag_name'		=> 'Bulgaria',
					'flag_image'	=> 'bg.png',
				],
				[
					'flag_name'		=> 'Burkina Faso',
					'flag_image'	=> 'bf.png',
				],
				[
					'flag_name'		=> 'Burundi',
					'flag_image'	=> 'bi.png',
				],
				[
					'flag_name'		=> 'Cambodia',
					'flag_image'	=> 'kh.png',
				],
				[
					'flag_name'		=> 'Cameroon',
					'flag_image'	=> 'cm.png',
				],
				[
					'flag_name'		=> 'Canada',
					'flag_image'	=> 'ca.png',
				],
				[
					'flag_name'		=> 'Cape Verde',
					'flag_image'	=> 'cv.png',
				],
				[
					'flag_name'		=> 'Cayman Islands',
					'flag_image'	=> 'ky.png',
				],
				[
					'flag_name'		=> 'Central African Republic',
					'flag_image'	=> 'cf.png',
				],
				[
					'flag_name'		=> 'Chad',
					'flag_image'	=> 'td.png',
				],
				[
					'flag_name'		=> 'Chile',
					'flag_image'	=> 'cl.png',
				],
				[
					'flag_name'		=> 'China',
					'flag_image'	=> 'cn.png',
				],
				[
					'flag_name'		=> 'Columbia',
					'flag_image'	=> 'co.png',
				],
				[
					'flag_name'		=> 'Comoros',
					'flag_image'	=> 'km.png',
				],
				[
					'flag_name'		=> 'Congo',
					'flag_image'	=> 'cg.png',
				],
				[
					'flag_name'		=> 'Congo Democratic Republic',
					'flag_image'	=> 'cd.png',
				],
				[
					'flag_name'		=> 'Costa Rica',
					'flag_image'	=> 'cr.png',
				],
				[
					'flag_name'		=> 'Cote D-Ivoire',
					'flag_image'	=> 'ci.png',
				],
				[
					'flag_name'		=> 'Croatia',
					'flag_image'	=> 'hr.png',
				],
				[
					'flag_name'		=> 'Cuba',
					'flag_image'	=> 'cu.png',
				],
				[
					'flag_name'		=> 'Cyprus',
					'flag_image'	=> 'cy.png',
				],
				[
					'flag_name'		=> 'Czech Republic',
					'flag_image'	=> 'cz.png',
				],
				[
					'flag_name'		=> 'Denmark',
					'flag_image'	=> 'dk.png',
				],
				[
					'flag_name'		=> 'Djibouti',
					'flag_image'	=> 'dj.png',
				],
				[
					'flag_name'		=> 'Dominica',
					'flag_image'	=> 'dm.png',
				],
				[
					'flag_name'		=> 'Dominican Republic',
					'flag_image'	=> 'do.png',
				],
				[
					'flag_name'		=> 'East Timor',
					'flag_image'	=> 'tl.png',
				],
				[
					'flag_name'		=> 'Ecuador',
					'flag_image'	=> 'ec.png',
				],
				[
					'flag_name'		=> 'Egypt',
					'flag_image'	=> 'eg.png',
				],
				[
					'flag_name'		=> 'El Salvador',
					'flag_image'	=> 'sv.png',
				],
				[
					'flag_name'		=> 'Equatorial Guinea',
					'flag_image'	=> 'gq.png',
				],
				[
					'flag_name'		=> 'Eritrea',
					'flag_image'	=> 'er.png',
				],
				[
					'flag_name'		=> 'Estonia',
					'flag_image'	=> 'ee.png',
				],
				[
					'flag_name'		=> 'Ethiopia',
					'flag_image'	=> 'et.png',
				],
				[
					'flag_name'		=> 'Falkland Islands',
					'flag_image'	=> 'fk.png',
				],
				[
					'flag_name'		=> 'Faroe Islands',
					'flag_image'	=> 'fo.png',
				],
				[
					'flag_name'		=> 'Fiji',
					'flag_image'	=> 'fj.png',
				],
				[
					'flag_name'		=> 'Finland',
					'flag_image'	=> 'fi.png',
				],
				[
					'flag_name'		=> 'France',
					'flag_image'	=> 'fr.png',
				],
				[
					'flag_name'		=> 'Gabon',
					'flag_image'	=> 'ga.png',
				],
				[
					'flag_name'		=> 'Gambia',
					'flag_image'	=> 'gm.png',
				],
				[
					'flag_name'		=> 'Georgia',
					'flag_image'	=> 'ge.png',
				],
				[
					'flag_name'		=> 'Germany',
					'flag_image'	=> 'de.png',
				],
				[
					'flag_name'		=> 'Ghana',
					'flag_image'	=> 'gh.png',
				],
				[
					'flag_name'		=> 'Great Britain',
					'flag_image'	=> 'gb.png',
				],
				[
					'flag_name'		=> 'Greece',
					'flag_image'	=> 'gr.png',
				],
				[
					'flag_name'		=> 'Greenland',
					'flag_image'	=> 'gl.png',
				],
				[
					'flag_name'		=> 'Grenada',
					'flag_image'	=> 'gd.png',
				],
				[
					'flag_name'		=> 'Guam',
					'flag_image'	=> 'gu.png',
				],
				[
					'flag_name'		=> 'Guatemala',
					'flag_image'	=> 'gt.png',
				],
				[
					'flag_name'		=> 'Guinea',
					'flag_image'	=> 'gn.png',
				],
				[
					'flag_name'		=> 'Guinea Bissau',
					'flag_image'	=> 'gw.png',
				],
				[
					'flag_name'		=> 'Guyana',
					'flag_image'	=> 'gy.png',
				],
				[
					'flag_name'		=> 'Haiti',
					'flag_image'	=> 'ht.png',
				],
				[
					'flag_name'		=> 'Honduras',
					'flag_image'	=> 'hn.png',
				],
				[
					'flag_name'		=> 'Hong Kong',
					'flag_image'	=> 'hk.png',
				],
				[
					'flag_name'		=> 'Hungary',
					'flag_image'	=> 'hu.png',
				],
				[
					'flag_name'		=> 'Iceland',
					'flag_image'	=> 'is.png',
				],
				[
					'flag_name'		=> 'India',
					'flag_image'	=> 'in.png',
				],
				[
					'flag_name'		=> 'Indonesia',
					'flag_image'	=> 'id.png',
				],
				[
					'flag_name'		=> 'Iran',
					'flag_image'	=> 'ir.png',
				],
				[
					'flag_name'		=> 'Iraq',
					'flag_image'	=> 'iq.png',
				],
				[
					'flag_name'		=> 'Ireland',
					'flag_image'	=> 'ie.png',
				],
				[
					'flag_name'		=> 'Isle of Man',
					'flag_image'	=> 'im.png',
				],
				[
					'flag_name'		=> 'Israel',
					'flag_image'	=> 'il.png',
				],
				[
					'flag_name'		=> 'Italy',
					'flag_image'	=> 'it.png',
				],
				[
					'flag_name'		=> 'Jamaica',
					'flag_image'	=> 'jm.png',
				],
				[
					'flag_name'		=> 'Japan',
					'flag_image'	=> 'jp.png',
				],
				[
					'flag_name'		=> 'Jordan',
					'flag_image'	=> 'jo.png',
				],
				[
					'flag_name'		=> 'Kazakhstan',
					'flag_image'	=> 'kz.png',
				],
				[
					'flag_name'		=> 'Kenya',
					'flag_image'	=> 'ke.png',
				],
				[
					'flag_name'		=> 'Kiribati',
					'flag_image'	=> 'ki.png',
				],
				[
					'flag_name'		=> 'Korea North',
					'flag_image'	=> 'kp.png',
				],
				[
					'flag_name'		=> 'Korea South',
					'flag_image'	=> 'kr.png',
				],
				[
					'flag_name'		=> 'Kuwait',
					'flag_image'	=> 'kw.png',
				],
				[
					'flag_name'		=> 'Kyrgyzstan',
					'flag_image'	=> 'kg.png',
				],
				[
					'flag_name'		=> 'Laos',
					'flag_image'	=> 'la.png',
				],
				[
					'flag_name'		=> 'Latvia',
					'flag_image'	=> 'lv.png',
				],
				[
					'flag_name'		=> 'Lebanon',
					'flag_image'	=> 'lb.png',
				],
				[
					'flag_name'		=> 'Lesotho',
					'flag_image'	=> 'ls.png',
				],
				[
					'flag_name'		=> 'Liberia',
					'flag_image'	=> 'lr.png',
				],
				[
					'flag_name'		=> 'Libya',
					'flag_image'	=> 'ly.png',
				],
				[
					'flag_name'		=> 'Liechtenstein',
					'flag_image'	=> 'li.png',
				],
				[
					'flag_name'		=> 'Lithuania',
					'flag_image'	=> 'lt.png',
				],
				[
					'flag_name'		=> 'Luxembourg',
					'flag_image'	=> 'lu.png',
				],
				[
					'flag_name'		=> 'Macau',
					'flag_image'	=> 'mo.png',
				],
				[
					'flag_name'		=> 'Macedonia',
					'flag_image'	=> 'mk.png',
				],
				[
					'flag_name'		=> 'Madagascar',
					'flag_image'	=> 'mg.png',
				],
				[
					'flag_name'		=> 'Malawi',
					'flag_image'	=> 'mw.png',
				],
				[
					'flag_name'		=> 'Malaysia',
					'flag_image'	=> 'my.png',
				],
				[
					'flag_name'		=> 'Maldives',
					'flag_image'	=> 'mv.png',
				],
				[
					'flag_name'		=> 'Mali',
					'flag_image'	=> 'ml.png',
				],
				[
					'flag_name'		=> 'Malta',
					'flag_image'	=> 'mt.png',
				],
				[
					'flag_name'		=> 'Marshall Islands',
					'flag_image'	=> 'mh.png',
				],
				[
					'flag_name'		=> 'Mauritania',
					'flag_image'	=> 'mr.png',
				],
				[
					'flag_name'		=> 'Mauritius',
					'flag_image'	=> 'mu.png',
				],
				[
					'flag_name'		=> 'Mexico',
					'flag_image'	=> 'mx.png',
				],
				[
					'flag_name'		=> 'Micronesia',
					'flag_image'	=> 'fm.png',
				],
				[
					'flag_name'		=> 'Moldova',
					'flag_image'	=> 'md.png',
				],
				[
					'flag_name'		=> 'Monaco',
					'flag_image'	=> 'mc.png',
				],
				[
					'flag_name'		=> 'Mongolia',
					'flag_image'	=> 'mn.png',
				],
				[
					'flag_name'		=> 'Montserrat',
					'flag_image'	=> 'ms.png',
				],
				[
					'flag_name'		=> 'Morocco',
					'flag_image'	=> 'ma.png',
				],
				[
					'flag_name'		=> 'Mozambique',
					'flag_image'	=> 'mz.png',
				],
				[
					'flag_name'		=> 'Myanmar',
					'flag_image'	=> 'mm.png',
				],
				[
					'flag_name'		=> 'Nambia',
					'flag_image'	=> 'na.png',
				],
				[
					'flag_name'		=> 'Nauru',
					'flag_image'	=> 'nr.png',
				],
				[
					'flag_name'		=> 'Nepal',
					'flag_image'	=> 'np.png',
				],
				[
					'flag_name'		=> 'Netherland Antilles',
					'flag_image'	=> 'an.png',
				],
				[
					'flag_name'		=> 'Netherlands',
					'flag_image'	=> 'nl.png',
				],
				[
					'flag_name'		=> 'New Zealand',
					'flag_image'	=> 'nz.png',
				],
				[
					'flag_name'		=> 'Nicaragua',
					'flag_image'	=> 'ni.png',
				],
				[
					'flag_name'		=> 'Niger',
					'flag_image'	=> 'ne.png',
				],
				[
					'flag_name'		=> 'Nigeria',
					'flag_image'	=> 'ng.png',
				],
				[
					'flag_name'		=> 'Norfolk Island',
					'flag_image'	=> 'nf.png',
				],
				[
					'flag_name'		=> 'Norway',
					'flag_image'	=> 'no.png',
				],
				[
					'flag_name'		=> 'Oman',
					'flag_image'	=> 'om.png',
				],
				[
					'flag_name'		=> 'Pakistan',
					'flag_image'	=> 'pk.png',
				],
				[
					'flag_name'		=> 'Palau Island',
					'flag_image'	=> 'pw.png',
				],
				[
					'flag_name'		=> 'Palestine',
					'flag_image'	=> 'ps.png',
				],
				[
					'flag_name'		=> 'Panama',
					'flag_image'	=> 'pa.png',
				],
				[
					'flag_name'		=> 'Papua New Guinea',
					'flag_image'	=> 'pg.png',
				],
				[
					'flag_name'		=> 'Paraguay',
					'flag_image'	=> 'py.png',
				],
				[
					'flag_name'		=> 'Peru',
					'flag_image'	=> 'pe.png',
				],
				[
					'flag_name'		=> 'Philippines',
					'flag_image'	=> 'ph.png',
				],
				[
					'flag_name'		=> 'Pitcairn Island',
					'flag_image'	=> 'pn.png',
				],
				[
					'flag_name'		=> 'Poland',
					'flag_image'	=> 'pl.png',
				],
				[
					'flag_name'		=> 'Portugal',
					'flag_image'	=> 'pt.png',
				],
				[
					'flag_name'		=> 'Puerto Rico',
					'flag_image'	=> 'pr.png',
				],
				[
					'flag_name'		=> 'Qatar',
					'flag_image'	=> 'qa.png',
				],
				[
					'flag_name'		=> 'Romania',
					'flag_image'	=> 'ro.png',
				],
				[
					'flag_name'		=> 'Russia',
					'flag_image'	=> 'ru.png',
				],
				[
					'flag_name'		=> 'Rwanda',
					'flag_image'	=> 'rw.png',
				],
				[
					'flag_name'		=> 'Samoa',
					'flag_image'	=> 'ws.png',
				],
				[
					'flag_name'		=> 'San Marino',
					'flag_image'	=> 'sm.png',
				],
				[
					'flag_name'		=> 'Sao Tome &amp; Principe',
					'flag_image'	=> 'st.png',
				],
				[
					'flag_name'		=> 'Saudi Arabia',
					'flag_image'	=> 'sa.png',
				],
				[
					'flag_name'		=> 'Senegal',
					'flag_image'	=> 'sn.png',
				],
				[
					'flag_name'		=> 'Seychelles',
					'flag_image'	=> 'sc.png',
				],
				[
					'flag_name'		=> 'Sierra Leone',
					'flag_image'	=> 'sl.png',
				],
				[
					'flag_name'		=> 'Singapore',
					'flag_image'	=> 'sg.png',
				],
				[
					'flag_name'		=> 'Slovakia',
					'flag_image'	=> 'sk.png',
				],
				[
					'flag_name'		=> 'Slovenia',
					'flag_image'	=> 'si.png',
				],
				[
					'flag_name'		=> 'Solomon Islands',
					'flag_image'	=> 'sb.png',
				],
				[
					'flag_name'		=> 'Somalia',
					'flag_image'	=> 'so.png',
				],
				[
					'flag_name'		=> 'South Africa',
					'flag_image'	=> 'za.png',
				],
				[
					'flag_name'		=> 'Spain',
					'flag_image'	=> 'es.png',
				],
				[
					'flag_name'		=> 'Sri Lanka',
					'flag_image'	=> 'lk.png',
				],
				[
					'flag_name'		=> 'St Helena',
					'flag_image'	=> 'sh.png',
				],
				[
					'flag_name'		=> 'St Kitts-Nevis',
					'flag_image'	=> 'kn.png',
				],
				[
					'flag_name'		=> 'St Lucia',
					'flag_image'	=> 'lc.png',
				],
				[
					'flag_name'		=> 'St Vincent &amp; Grenadines',
					'flag_image'	=> 'vc.png',
				],
				[
					'flag_name'		=> 'Sudan',
					'flag_image'	=> 'so.png',
				],
				[
					'flag_name'		=> 'Suriname',
					'flag_image'	=> 'sr.png',
				],
				[
					'flag_name'		=> 'Swaziland',
					'flag_image'	=> 'sz.png',
				],
				[
					'flag_name'		=> 'Sweden',
					'flag_image'	=> 'se.png',
				],
				[
					'flag_name'		=> 'Switzerland',
					'flag_image'	=> 'ch.png',
				],
				[
					'flag_name'		=> 'Syria',
					'flag_image'	=> 'sy.png',
				],
				[
					'flag_name'		=> 'Taiwan',
					'flag_image'	=> 'tw.png',
				],
				[
					'flag_name'		=> 'Tajikistan',
					'flag_image'	=> 'tj.png',
				],
				[
					'flag_name'		=> 'Tanzania',
					'flag_image'	=> 'tz.png',
				],
				[
					'flag_name'		=> 'Thailand',
					'flag_image'	=> 'th.png',
				],
				[
					'flag_name'		=> 'Togo',
					'flag_image'	=> 'tg.png',
				],
				[
					'flag_name'		=> 'Tonga',
					'flag_image'	=> 'to.png',
				],
				[
					'flag_name'		=> 'Trinidad &amp; Tobago',
					'flag_image'	=> 'tt.png',
				],
				[
					'flag_name'		=> 'Tunisia',
					'flag_image'	=> 'tn.png',
				],
				[
					'flag_name'		=> 'Turkey',
					'flag_image'	=> 'tr.png',
				],
				[
					'flag_name'		=> 'Turkmenistan',
					'flag_image'	=> 'tm.png',
				],
				[
					'flag_name'		=> 'Turks &amp; Caicos Is',
					'flag_image'	=> 'tc.png',
				],
				[
					'flag_name'		=> 'Tuvalu',
					'flag_image'	=> 'tv.png',
				],
				[
					'flag_name'		=> 'Uganda',
					'flag_image'	=> 'ug.png',
				],
				[
					'flag_name'		=> 'Ukraine',
					'flag_image'	=> 'ua.png',
				],
				[
					'flag_name'		=> 'United Arab Emirates',
					'flag_image'	=> 'ae.png',
				],
				[
					'flag_name'		=> 'United States of America',
					'flag_image'	=> 'us.png',
				],
				[
					'flag_name'		=> 'Uruguay',
					'flag_image'	=> 'uy.png',
				],
				[
					'flag_name'		=> 'Uzbekistan',
					'flag_image'	=> 'uz.png',
				],
				[
					'flag_name'		=> 'Vanuatu',
					'flag_image'	=> 'vu.png',
				],
				[
					'flag_name'		=> 'Venezuela',
					'flag_image'	=> 've.png',
				],
				[
					'flag_name'		=> 'Vietnam',
					'flag_image'	=> 'vn.png',
				],
				[
					'flag_name'		=> 'Virgin Islands (Brit)',
					'flag_image'	=> 'vg.png',
				],
				[
					'flag_name'		=> 'Virgin Islands (USA)',
					'flag_image'	=> 'vi.png',
				],
				[
					'flag_name'		=> 'Wales',
					'flag_image'	=> 'wls.png',
				],
				[
					'flag_name'		=> 'Western Sahara',
					'flag_image'	=> 'eh.png',
				],
				[
					'flag_name'		=> 'Yemen',
					'flag_image'	=> 'ye.png',
				],
				[
					'flag_name'		=> 'Zambia',
					'flag_image'	=> 'zm.png',
				],
				[
					'flag_name'		=> 'Zimbabwe',
					'flag_image'	=> 'zw.png',
				],
				[
					'flag_name'		=> 'Cocos (Keeling) Islands ',
					'flag_image'	=> 'cc.png',
				],
				[
					'flag_name'		=> 'Cook Islands',
					'flag_image'	=> 'ck.png',
				],
				[
					'flag_name'		=> "Cura\xE7ao",
					'flag_image'	=> 'cw.png',
				],
				[
					'flag_name'		=> 'Christmas Island',
					'flag_image'	=> 'cx.png',
				],
				[
					'flag_name'		=> 'Gibraltar',
					'flag_image'	=> 'gi.png',
				],
				[
					'flag_name'		=> 'Saint-Martin (French part)',
					'flag_image'	=> 'mf.png',
				],
				[
					'flag_name'		=> 'Martinique',
					'flag_image'	=> 'mq.png',
				],
				[
					'flag_name'		=> 'New Caledonia',
					'flag_image'	=> 'nc.png',
				],
				[
					'flag_name'		=> 'Niue',
					'flag_image'	=> 'nu.png',
				],
				[
					'flag_name'		=> 'French Polynesia',
					'flag_image'	=> 'pf.png',
				],
				[
					'flag_name'		=> 'South Sudan',
					'flag_image'	=> 'ss.png',
				],
				[
					'flag_name'		=> 'French Southern Territories',
					'flag_image'	=> 'tf.png',
				],
				[
					'flag_name'		=> 'Tokelau',
					'flag_image'	=> 'tk.png',
				],
				[
					'flag_name'		=> 'Wallis and Futuna Islands',
					'flag_image'	=> 'wf.png',
				],
				[
					'flag_name'		=> 'Mayotte',
					'flag_image'	=> 'yt.png',
				],
			];
			foreach ($sql_ary as $num => $flag)
			{
				$sql = 'UPDATE ' . $this->table_prefix . 'flags
					SET ' . $this->db->sql_build_array('UPDATE', [
								'flag_image'	=> (string) $flag['flag_image']]
							) .
					" WHERE flag_name = '" . (string) $flag['flag_name'] . "'";
				$this->db->sql_query($sql);
			}
		}
	}
}
