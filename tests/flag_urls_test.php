<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * Regression checks for readable flag identifiers. Run with php tests/flag_urls_test.php.
 */

require dirname(__DIR__) . '/core/nationalflags.php';

class flag_url_fixture extends \rmcgirr83\nationalflags\core\nationalflags
{
	private $flags;

	public function __construct(array $flags)
	{
		$this->flags = $flags;
	}

	public function get_flag_cache()
	{
		return $this->flags;
	}
}

$flags = [
	204 => ['flag_image' => 'US.png'],
	35 => ['flag_image' => 'ca.png'],
	120 => ['flag_image' => 'MX.png'],
	250 => ['flag_image' => 'gb-eng.png'],
	251 => ['flag_image' => 'custom-banner.png'],
	252 => ['flag_image' => 'FR.png'],
	253 => ['flag_image' => 'fr.gif'],
];
$fixture = new flag_url_fixture($flags);
$checks = 0;
$check = function ($actual, $expected, $label) use (&$checks)
{
	++$checks;
	if ($actual !== $expected)
	{
		throw new \RuntimeException($label . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
	}
};

foreach (['US' => 204, 'us' => 204, 'USA' => 204, 'usa' => 204, 'CA' => 35, 'mx' => 120, 'gb-ENG' => 250, '204' => 204] as $code => $id)
{
	$check($fixture->resolve_flag_id($code), $id, 'Resolve ' . $code);
}
foreach (['ZZ', '9999', 'FR', 'fr', '../US', 'US.png', 'US/CA', '', "US\n", 'custom-banner'] as $code)
{
	$check($fixture->resolve_flag_id($code), false, 'Reject ' . $code);
}
foreach ([204 => 'US', 35 => 'CA', 120 => 'MX', 250 => 'GB-ENG', 251 => 251, 252 => 252, 253 => 253] as $id => $code)
{
	$check($fixture->flag_url_identifier($id), $code, 'Generate ' . $id);
	$check($fixture->resolve_flag_id($code), $id, 'Round trip ' . $id);
}
$fixture = new flag_url_fixture($flags + [254 => ['flag_image' => 'USA.png']]);
$check($fixture->resolve_flag_id('USA'), 254, 'Explicit USA overrides alias');
$check($fixture->flag_url_identifier(254), 'USA', 'Explicit USA URL');
$fixture = new flag_url_fixture([35 => ['flag_image' => 'CA.png']]);
$check($fixture->resolve_flag_id('USA'), false, 'Missing US alias target');
$fixture = new flag_url_fixture($flags + [255 => ['flag_image' => 'us.gif']]);
$check($fixture->resolve_flag_id('USA'), false, 'Ambiguous US alias target');
$check($fixture->flag_url_identifier(204), 204, 'Ambiguous US uses numeric ID');
echo $checks . " flag URL checks passed.\n";
