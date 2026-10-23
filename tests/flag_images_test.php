<?php
/**
 * Image path and deletion regression checks. Run with php tests/flag_images_test.php.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
require dirname(__DIR__) . '/core/flag_images.php';

class flag_image_fixture extends \rmcgirr83\nationalflags\core\flag_images
{
	public function __construct($root)
	{
		$this->root_path = $root . '/';
		$this->legacy_path = $root . '/flags/';
		$this->legacy_url = '/ext/flags/';
		$this->filesystem = new class
		{
			public function remove($path)
			{
				unlink($path);
			}
		};
		$this->helper = new class
		{
			public function route($route, $parameters)
			{
				return '/flag-image/' . $parameters['filename'];
			}
		};
	}
}

$root = sys_get_temp_dir() . '/nationalflags-' . bin2hex(random_bytes(8));
mkdir($root . '/store/rmcgirr83_nationalflags', 0700, true);
mkdir($root . '/flags');
$filename = str_repeat('a', 32) . '.png';
$managed = $root . '/store/rmcgirr83_nationalflags/' . $filename;
file_put_contents($managed, 'fixture');
file_put_contents($root . '/flags/us.png', 'bundled');
file_put_contents($root . '/marker.txt', 'preserve');
$checks = 0;
$check = function ($condition, $label) use (&$checks)
{
	++$checks;
	if (!$condition)
	{
		throw new \RuntimeException($label);
	}
};
try
{
	$images = new flag_image_fixture($root);
	$check($images->exists('us.png'), 'Bundled image resolves');
	$check($images->url('us.png') === '/ext/flags/us.png', 'Bundled URL remains supported');
	$check($images->url('upload/' . $filename) === '/flag-image/' . $filename, 'Managed images use delivery route');
	foreach (['../marker.txt', '../../../../marker.txt', '/us.png', 'upload/../us.png', 'upload/short.png', 'us.png/../us.png', 'us.png"', "us.png\n", 'US.php', 'missing.png', '..\\marker.txt'] as $image)
	{
		$check(!$images->exists($image), 'Reject unsafe or missing image: ' . $image);
		$check($images->url($image) === '', 'No unsafe image URL');
		$images->remove($image);
	}
	$images->remove('us.png');
	$check(is_file($root . '/flags/us.png'), 'Bundled file survives deletion');
	$check(is_file($root . '/marker.txt'), 'Traversal target survives deletion');
	unlink($managed);
	symlink($root . '/marker.txt', $managed);
	$check(!$images->exists('upload/' . $filename), 'Managed symlink rejected');
	$images->remove('upload/' . $filename);
	$check(is_file($root . '/marker.txt'), 'Symlink target preserved');
	unlink($managed);
	file_put_contents($managed, 'fixture');
	$images->remove('upload/' . $filename);
	$check(!file_exists($managed), 'Managed file deleted');
	echo $checks . " flag image checks passed.\n";
}
finally
{
	foreach ([$managed, $root . '/flags/us.png', $root . '/marker.txt'] as $path)
	{
		if (is_file($path) || is_link($path))
		{
			unlink($path);
		}
	}
	rmdir($root . '/store/rmcgirr83_nationalflags');
	rmdir($root . '/store');
	rmdir($root . '/flags');
	rmdir($root);
}
