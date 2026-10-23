<?php
/**
 * Flag image storage and delivery. Managed uploads live outside the extension.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
namespace rmcgirr83\nationalflags\core;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use phpbb\exception\http_exception;

class flag_images
{
	protected $root_path;
	protected $helper;
	protected $filesystem;
	protected $legacy_path;
	protected $legacy_url;

	public function __construct($root_path, \phpbb\controller\helper $helper, \phpbb\filesystem\filesystem $filesystem, \phpbb\extension\manager $manager, \phpbb\path_helper $path_helper)
	{
		$this->root_path = $root_path;
		$this->helper = $helper;
		$this->filesystem = $filesystem;
		$this->legacy_path = $manager->get_extension_path('rmcgirr83/nationalflags', true) . 'flags/';
		$this->legacy_url = $path_helper->update_web_root_path($this->legacy_path);
	}

	/** Relative to the board root, as required by phpBB's filespec. */
	public function upload_directory()
	{
		return 'store/rmcgirr83_nationalflags';
	}

	public function can_upload()
	{
		$path = $this->root_path . $this->upload_directory();
		return !is_link($path) && $this->filesystem->is_writable(is_dir($path) ? $path : $this->root_path . 'store');
	}

	public function prepare_upload_directory()
	{
		$path = $this->root_path . $this->upload_directory();
		if (is_link($path))
		{
			throw new \phpbb\filesystem\exception\filesystem_exception('FILESYSTEM_CANNOT_CHANGE_PERMISSIONS', $path);
		}
		$this->filesystem->mkdir($path);
	}

	/** Only generated filenames belong to managed storage. */
	protected function is_managed($image)
	{
		return (bool) preg_match('#^upload/[a-f0-9]{32}\.(?:png|gif|jpe?g)$#D', $image);
	}

	/** Resolve existing, regular files without accepting paths or symbolic links. */
	protected function path($image)
	{
		if ($this->is_managed($image))
		{
			$directory = $this->root_path . $this->upload_directory() . '/';
			$name = substr($image, 7);
		}
		else if (preg_match('#^[a-zA-Z0-9][a-zA-Z0-9 _.-]*\.(?:png|gif|jpe?g)$#Di', $image))
		{
			$directory = $this->legacy_path;
			$name = $image;
		}
		else
		{
			return false;
		}
		$file = $directory . $name;
		$parent = realpath($directory);
		$real = realpath($file);
		return !is_link(rtrim($directory, '/')) && !is_link($file) && $parent !== false && $real !== false &&
			dirname($real) === $parent && is_file($real) ? $real : false;
	}

	public function exists($image)
	{
		return $this->path($image) !== false;
	}

	public function url($image)
	{
		if (!$this->exists($image))
		{
			return '';
		}
		return $this->is_managed($image) ? $this->helper->route('rmcgirr83_nationalflags_image', ['filename' => substr($image, 7)]) : $this->legacy_url . rawurlencode($image);
	}

	/** Delete only managed uploads; bundled and manually installed images survive. */
	public function remove($image)
	{
		if ($this->is_managed($image) && ($path = $this->path($image)))
		{
			$this->filesystem->remove($path);
		}
	}

	/** Stream a validated image through phpBB because store is not web-accessible. */
	public function display_image($filename)
	{
		$image = 'upload/' . $filename;
		$path = $this->is_managed($image) ? $this->path($image) : false;
		$types = [IMAGETYPE_GIF => 'image/gif', IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png'];
		$info = $path ? @getimagesize($path) : false;
		if (!$info || !isset($types[$info[2]]))
		{
			throw new http_exception(404, 'FLAG_NOT_EXIST');
		}
		return new BinaryFileResponse($path, 200, ['Content-Type' => $types[$info[2]], 'X-Content-Type-Options' => 'nosniff']);
	}
}
