<?php
$htdocRoot = dirname(dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['PHP_SELF'])))))));
$sitePath  = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
$libPath = $sitePath . '/lib';
include $libPath . '/Tk/Tk.php';
Tk::init($sitePath, $libPath, 'Com/_prepend.php', $htdocRoot);

// Start Output buffer
ob_start();

Tk::loadConfig('js.tinymce');
$request = Tk_Request::getInstance();

// Setup manager file path
$fileRootPath = $sitePath . '/data/fileManager';
if (Tk_Session::exists('js.tinymce.fileManagerPath')) {
    $fileRootPath = Tk_Session::get('js.tinymce.fileManagerPath');
}
$fileHtdoc = str_replace($sitePath, '', $fileRootPath);

$mceLibPath = $sitePath . '/data/tinymce/jscripts/tiny_mce/';
if (Tk_Session::exists('js.tinymce.mcePath')) {
    $mceLibPath = Tk_Session::get('js.tinymce.mcePath');
}
$mceHtdoc = str_replace($sitePath, '', $mceLibPath);

if (!$fileRootPath) {
    throw new Exception("No user data directory found!.");
}
if (!is_dir($fileRootPath)) {
    if (!mkdir($fileRootPath, 0777, true)) {
        throw new Exception('Cannot create directory, check permissions: ' . $fileRootPath);
    }
}
// Setup vars
$selectedPath = '/';
if (Tk_Session::exists('js.tinymce.selectedPath')) {
   $selectedPath = Tk_Session::get('js.tinymce.selectedPath');
}
if ($request->getParameter('selectedPath')) {
    $selectedPath = $request->getParameter('selectedPath');
    Tk_Session::set('js.tinymce.selectedPath', $selectedPath);
}

// Ensure no one tries to hack the path
if (!is_dir($fileRootPath . $selectedPath)) {
    $selectedPath = '/';
}

$selectedPath = cleanPath($selectedPath);
$currentPath = cleanPath($fileRootPath . $selectedPath);

function cleanPath($path)
{
    if (strlen($path) > 1 && (substr($path, -1) == '/' || substr($path, -1) == '\\')) {
        $path = substr($path, 0, -1);
    }
    $path = str_replace('..', '', $path);
    $path = str_replace('//', '/', $path);
    return $path;
}