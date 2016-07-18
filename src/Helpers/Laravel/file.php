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
 * Return the Path to zbase package
 * @return string
 */
function zbase_path()
{
	return __DIR__ . '/../../../';
}

/**
 * Path to zbase library
 * @return string
 */
function zbase_path_library($path)
{
	return zbase_path() . 'library/' . $path;
}

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
 * Return TMP Folder
 * @param type $path
 */
function zbase_tmp_path($path = null)
{
	$tmpPath = zbase_storage_path() . '/tmp/' . $path;
	zbase_directory_check($tmpPath, true);
	return $tmpPath;
}

/**
 * Check if directory exists, else create it
 * @param string $path
 * @param boolean $create
 * @return string|false
 */
function zbase_directory_check($path, $create = false)
{
	if(empty($create))
	{
		return is_dir($path);
	}
	if(!is_dir($path))
	{
		if($create)
		{
			mkdir($path, 0777, true);
			return true;
		}
	}
	return false;
}

/**
 * Return all directories/folders from a given path
 * @param string $path Path to folder/directory
 * @return array|null
 */
function zbase_directories($path)
{
	if(zbase_directory_check($path))
	{
		return \File::directories($path);
	}
	return null;
}

/**
 * Return all files inside a directory/folder
 * @param string $path Path to folder/directory
 * @param boolean $recursive If to return all files recursively; default: false
 * @return array|null
 */
function zbase_directory_files($path, $recursive = false)
{
	if(zbase_directory_check($path))
	{
		if(!empty($recursive))
		{
			return \File::allFiles($path);
		}
		return \File::files($path);
	}
	return null;
}

/**
 * Copy directory to another directory
 * @param string $src
 * @param string $dst
 * @param array|mixed $options
 */
function zbase_directory_copy($src, $dst, $options = null)
{
	\File::copyDirectory($src, $dst, $options);
}

/**
 * Recursively remove folder
 * @param string $dir
 * @param boolean $preserve if not delete the folder, just clean up
 */
function zbase_directory_remove($dir, $preserve = false)
{
	\File::deleteDirectory($dir, $preserve);
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
		if(!empty($file[count($file) - 1]))
		{
			return $fileName . '.' . $file[count($file) - 1];
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
	/**
	 * Using angular flow.js
	 * https://github.com/flowjs/flow-php-server
	 *
	  "flowChunkNumber" => "1"
	  "flowChunkSize" => "1048576"
	  "flowCurrentChunkSize" => "83167"
	  "flowTotalSize" => "83167"
	  "flowIdentifier" => "83167-Avatar2jpg"
	  "flowFilename" => "Avatar2.jpg"
	  "flowRelativePath" => "Avatar2.jpg"
	  "flowTotalChunks" => "1"
	 *
	 */
	$tmpFolder = zbase_tmp_path();
	$newFile = $folder . str_replace(array('.png', '.jpg', '.gif', '.bmp', '.jpeg'), '.' . $encodingFormat, $newFilename);
	if(!empty(zbase_request_query_input('flowChunkNumber', false)))
	{
		if(\Flow\Basic::save($newFile, $tmpFolder))
		{
			$im = \Image::make($newFile);
		}
		else
		{
			// zbase()->json()->setVariable('files', $_FILES);
			// zbase()->json()->setVariable('success', false);
			// zbase()->json()->setVariable('get', $_GET);
			// zbase()->json()->setVariable('post', $_POST);
			return zbase_abort(204);
		}
	}
	if(!empty($_FILES[$index]['tmp_name']))
	{
		zbase_directory_check($folder, true);
		$im = \Image::make($_FILES[$index]['tmp_name']);
	}
	if(!empty($im))
	{
		if(!empty($size))
		{
			$im->resize($size[0], $size[1], function ($constraint) {
				$constraint->aspectRatio();
			});
		}
		$im->encode($encodingFormat, 100)->save($newFile);
		return $newFile;
	}
	return false;
}

/**
 * Return the file mime type
 * @param string $file The File to question
 * @return string
 */
function zbase_file_mime_type($file)
{
	if(zbase_file_exists($file))
	{
		return \Image::make($file)->mime();
	}
	return null;
}

/**
 * eturns the size of the image file in bytes or false if image instance is not created from a file.
 * @param string $file The File to question
 * @return boolean|integer
 */
function zbase_file_size($file)
{
	if(zbase_file_exists($file))
	{
		return \Image::make($file)->filesize();
	}
	return null;
}
