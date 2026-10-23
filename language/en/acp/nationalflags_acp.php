<?php

/**
*
*
* @package - National Flags language
* @copyright (c) RMcGirr83
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
// Some characters you may want to copy&paste:
// ’ » “ ” …

$lang = array_merge($lang, [
	'ACP_FLAGS_EXPLAIN'					=> 'Here you can add/edit and delete the different flags.',
	'ACP_NO_UPLOAD'						=> '<br><strong>If you want to use images you should upload them to ext/rmcgirr83/nationalflags/flags before you add the new Flag.  The flag must have a lower case name, ie uk.gif</strong>',
	'ACP_FLAG_USERS'					=> 'Number of Users',

	//Add/Edit Flags
	'FLAG_DEFAULT'						=> 'Default Flag',
	'FLAG_DEFAULT_EXPLAIN'				=> 'Setting this to yes will show the flag first in the user profile selection if a user hasn’t selected a flag yet.',
	'FLAG_EDIT'							=> 'Edit flag',
	'FLAG_NAME'							=> 'Flag Name',
	'FLAG_NAME_EXPLAIN'					=> 'The name of the flag. The flag title is displayed as it is here.',
	'FLAG_IMG'							=> 'Image Name',
	'FLAG_IMG_EXPLAIN'					=> 'The name of the image. Example: uk.gif. New images should be uploaded to ext/rmcgirr83/nationalflags/flags.',
	'FLAG_IMAGE'						=> 'Flag Image',
	'FLAG_ADD'							=> 'Add new flag',
	'FLAG_UPLOAD'						=> 'Upload flag',
	'FLAG_IMAGE_UPLOAD_DIRECTORY' => 'The flag upload directory in store could not be created. Check its permissions.',
	'FLAG_UPLOAD_NOTICE'				=> 'Uploaded images are stored with unique names in the board’s store directory. Existing flag images are preserved.',
	'FLAG_UPLOAD_NO_OVERWRITE'			=> 'You will not be able to overwrite an already existing image with the same name and extension as shown below.',
	'FLAG_FOUND'						=> 'Flag found',
	'IMAGES_ON_SERVER'					=> '%d Image names on server',

	//Settings
	'FLAGS_REQUIRED'					=> 'Required field',
	'FLAGS_REQUIRED_EXPLAIN'			=> 'Choosing Yes here will force new registrants as well as those that visit their user profile to choose a flag',
	'FLAGS_DISPLAY_AVATARS'				=> 'Display avatars',
	'FLAGS_DISPLAY_AVATARS_EXPLAIN'		=> 'If set to yes, avatars for users will display on the flag page. If a user doesn’t have an avatar then the default no-avatar for the style will display.',
	'FLAGS_DISPLAY_MSG'					=> 'Display a message',
	'FLAGS_DISPLAY_MSG_EXPLAIN'			=> 'Choosing Yes here will display a message on the forum for a user to choose a flag',
	'FLAGS_NUM_DISPLAY'					=> 'Number of flags',
	'FLAGS_NUM_DISPLAY_EXPLAIN'			=> 'The number of flags to display on the index page of the forum',
	'FLAGS_ON_INDEX'					=> 'Display on Index',
	'FLAGS_ON_INDEX_EXPLAIN'			=> 'Display a summary of flag users on index page',
	'FLAGS_DISPLAY_OPTIONS'				=> 'Display Options',
	'FLAGS_DISPLAY_VIEWFORUM'			=> 'Display flags when viewing topics within forums',
	'FLAGS_DISPLAY_FORUMROW'			=> 'Display flags on index and forum views (next to user names)',
	'FLAGS_DISPLAY_SEARCH'				=> 'Display flags within search of posts',
	'FLAGS_DISPLAY_MEMBERLIST'			=> 'Display flags within the memberlist',
	'FLAGS_DISPLAY_TO_GUESTS'			=> 'Display flags to guests',
	'FLAGS_DISPLAY_TO_GUESTS_EXPLAIN'	=> 'Choosing Yes here will display the flags to guests and bots',

	'FLAGS_VIEWTOPIC_POSITION'			=> 'Position of Flag',
	'FLAGS_VIEWTOPIC_POSITION_EXPLAIN'	=> 'Where would you like a users flag to display',
	'FLAG_POSITION_AFTER_AVATAR'		=> 'After a users avatar', //1
	'FLAG_POSITION_BEFORE_AVATAR'		=> 'Before a users avatar', //2
	'FLAG_POSITION_AFTER_USER_NAME'		=> 'After a user name', //3
	'FLAG_POSITION_BEFORE_USER_NAME'	=> 'Before a user name', //4
	'FLAG_POSITION_ABOVE_RANK'			=> 'Above a users rank', //5
	'FLAG_POSITION_BELOW_RANK'			=> 'Below a users rank', //6
	'FLAG_POSITION_AFTER_PROFILE_FIELDS'	=> 'After profile fields', //7
	'FLAG_POSITION_BEFORE_PROFILE_FIELDS'	=> 'Before profile fields', //8
	'FLAG_POSITION_AFTER_CONTACT_FIELDS'	=> 'After contact fields', //0

	//Logs, messages and errors
	'MSG_FLAGS_DELETED'					=> 'Flag has been deleted.',
	'MSG_CONFIRM'						=> '<strong>Are you sure you want to delete this flag?</strong>',
	'MSG_FLAG_CONFIRM_DELETE'			=> [
		1	=> '<br><strong>%d</strong> user has this flag and will have to select a different flag if you choose to delete this one.',
		2	=> '<br><strong>%d</strong> users have this flag and will have to select a different flag if you choose to delete this one.',
	],
	'MSG_FLAG_EDITED'					=> 'Flag has been edited.',
	'MSG_FLAG_ADDED'					=> 'New flag has been added.',
	'FLAG_ERROR_NO_FLAG_NAME'			=> 'No flag name defined, this is a required field.',
	'FLAG_ERROR_NO_FLAG_IMG'			=> 'No flag image defined, this is a required field.',
	'FLAG_ERROR_NOT_EXIST'				=> 'The selected flag does not exist.',
	'FLAG_NAME_EXISTS'					=> 'A flag with that name already exists',
	'FLAG_SETTINGS_CHANGED'				=> 'National Flags settings changed.',
	'FLAG_IMAGE_GENERAL_UPLOAD_ERROR'	=> 'Could not upload flag to %s. The flag image may already exist.',
	'FLAG_IMAGE_DISALLOWED_CONTENT'			=> 'The transfer has been interrupted because the file had been identified as a potential threat.',
	'FLAG_IMAGE_DISALLOWED_EXTENSION'		=> 'This file can’t be shown because the extension <strong>%s</strong> is not allowed.',
	'FLAG_IMAGE_EMPTY_FILEUPLOAD'			=> 'The flag file is empty.',
	'FLAG_IMAGE_EMPTY_REMOTE_DATA'			=> 'The submitted flag can’t be transfered because the datas seem incorrect or corrupted.',
	'FLAG_IMAGE_IMAGE_FILETYPE_MISMATCH'	=> 'Flag file type mismatch: expected extension %1$s but extension %2$s given.',
	'FLAG_IMAGE_INVALID_FILENAME'			=> '%s is an invalid file name.',
	'FLAG_IMAGE_NOT_UPLOADED'				=> 'The flag cannot be transfered',
	'FLAG_IMAGE_PARTIAL_UPLOAD'				=> 'The file cannot be totally transfered.',
	'FLAG_IMAGE_PHP_SIZE_NA'				=> 'The flag size is too big.<br>The maximum size set in php.ini could not be determined.',
	'FLAG_IMAGE_PHP_SIZE_OVERRUN'			=> 'The flag size is too big.<br>The maximum size set in php.ini could not be determined.',
	'FLAG_IMAGE_REMOTE_UPLOAD_TIMEOUT'		=> 'The specified flag could not be uploaded because the request timed out.',
	'FLAG_IMAGE_UNABLE_GET_IMAGE_SIZE'		=> 'It was not possible to determine the flag’s dimensions',
	'FLAG_IMAGE_URL_INVALID'				=> 'The flag address is invalid',
	'FLAG_IMAGE_URL_NOT_FOUND'				=> 'The file cannot be found.',
	'FLAG_IMAGE_WRONG_FILESIZE'				=> 'The flag size must be between 0 and %1d %2s.',
	'FLAG_IMAGE_WRONG_SIZE'					=> 'The flag must be exactly %3$s wide and %4$s high. The submitted image is %5$s wide and %6$s high.',
	'FLAGS_REQUIRE_540'			=> 'This extension requires at least PHP version 8.3.36 and phpBB version 3.3.19 or higher.  Please update your PHP version and/or your phpBB version in order to use the extension.',

	// end comment
	'TOO_SMALL_FLAGS_NUM_DISPLAY'	=> 'The number chosen for the number of flags to display is too small',
	'TOO_LARGE_FLAGS_NUM_DISPLAY'	=> 'The number chosen for the number of flags to display too large',
	//Donation
	'PAYPAL_IMAGE_URL'          => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/silver-pill-paypal-26px.png',
	'PAYPAL_ALT'                => 'Donate using PayPal',
	'BUY_ME_A_BEER_URL'         => 'https://paypal.me/RMcGirr83',
	'BUY_ME_A_BEER'				=> 'Buy me a beer for creating this extension',
	'BUY_ME_A_BEER_SHORT'		=> 'Make a donation for this extension',
	'BUY_ME_A_BEER_EXPLAIN'		=> 'This extension is completely free. It is a project that I’m spending my time on for the enjoyment and use of the phpBB community. If you enjoy using this extension, or if it has benefited your forum, please consider <a href="https://paypal.me/RMcGirr83" target="_blank" rel="noreferrer noopener">buying me a beer</a>. It would be greatly appreciated. <i class="fa fa-smile-o" style="color:green;font-size: 1.5em;" aria-hidden="true"></i>',
]);
