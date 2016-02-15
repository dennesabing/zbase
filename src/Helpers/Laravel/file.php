<?php

/**
 * Zbase-Laravel Helpers-File/Directories
 *
 * Functions and Helpers for File and Directories manipulation
 *
 * @link http://zbase.dennesabing.com
 * @author Dennes B Abing <dennes.b.abing@gmail.com>
 * @license proprietary
 * @copyright Copyright (c) 2016 ClaremontDesign/MadLabs-Dx
 * @file file.php
 * @project Zbase
 * @package Zbase/Laravel/Helpers
 */

/**
 * Return the Asset base path
 * @return string
 */
function zbase_path_asset($path = null)
{
	return '/' . zbase_tag() . '/assets/' . $path;
}

/**
 * Return the path to theme.
 *
 * @param string $theme
 * @param string $section
 * @return string
 */
function zbase_path_asset_theme($theme, $section)
{
	return 'templates/' . $section . '/' . $theme . '/';
}

/**
 * Return the package-based asset path
 *
 * @param string $package
 * @return string
 */
function zbase_path_asset_package($package)
{
	return 'packages/' . $package . '/';
}

/**
 * Return the package-theme-based asset path
 *
 * @param string $package
 * @param string $theme
 * @param string $section
 * @return type
 */
function zbase_path_asset_package_theme($package, $theme, $section)
{
	return 'packages/' . $package . '/templates/' . $section . '/' . $theme . '/';
}

/**
 * Application path
 * return laravel\app
 *
 * @return string
 */
function zbase_app_path($path = null)
{
	return app_path($path);
}

/**
 * Application base Path
 * return laravel\
 *
 * @return string
 */
function zbase_base_path($path = null)
{
	return base_path($path);
}

/**
 * Return the Public Path
 * return laravel\public
 *
 * @return string
 */
function zbase_public_path($path = null)
{
	$pubPath = env('ZBASE_PUBLIC_PATH');
	if(empty($pubPath))
	{
		return public_path($path);
	}
	return $pubPath . $path;
}

/**
 * Return the Storage Path
 * return laravel\storage
 *
 * @return string
 */
function zbase_storage_path($path = null)
{
	return storage_path($path);
}

/**
 * Check if directory exists, else create it
 * @param string $path
 * @param boolean $create
 * @return string|false
 */
function zbase_directory_check($path, $create = false)
{
	if(!is_dir($path))
	{
		if($create)
		{
			mkdir($path, 0777, true);
			return true;
		}
		return false;
	}
	return false;
}

/**
 * Create a file name from file using the $file extension
 * @param string $file The file path
 * @param string $fileName the new file naame without the extension
 * @return string
 */
function zbase_file_name_from_file($file, $fileName, $isUpload = false)
{
	if($isUpload)
	{
		$file = explode('.', $file);
		if(!empty($file[1]))
		{
			return $fileName . '.' . $file[1];
		}
	}
	if(file_exists($file))
	{
		$f = new \SplFileInfo($file);
		return $fileName . '.' . $f->getExtension();
	}
	return false;
}

/**
 * Upload a file
 * @param string $index The form Index
 * @param string $folder the Folder to save the new file
 * @param string $newFilename the new filename (just filename no folder)
 * @param string $encodingFormat The Format the file to be encoded. jpg, png
 * @param array $size The Size to encode [$width, $height], [$width, null]
 * @return string The Path to the new file
 */
function zbase_file_upload_image($index, $folder, $newFilename, $encodingFormat = 'jpg', $size = [])
{
	$newFile = $folder . str_replace(array('.png', '.jpg', '.gif', '.bmp', '.jpeg'), '.' . $encodingFormat, $newFilename);
	zbase_directory_check($folder, true);
	$im = \Image::make($_FILES[$index]['tmp_name']);
	if(!empty($size))
	{
		$im->resize($size[0], $size[1], function ($constraint) {
			$constraint->aspectRatio();
		});
	}
	$im->encode($encodingFormat, 100)->save($newFile);
	return $newFile;
}
