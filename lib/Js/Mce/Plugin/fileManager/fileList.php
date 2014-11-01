<?php
include('prepend.php');
?>
<div>
    <div id="dirinfo" class="pagenav">Folders: <span var="numFolders"/>, Files: <span var="numFiles"/>, Total file size: <span var="totalSize"/>, Premissions: <span var="permissions"/></div>
    <div id="progress" class="pagenav" var="progress"></div>
    <div class="filemanagertop">
      <ul class="menu">
        <li id="selectall"><a href="javascript:selectAll();">Select All</a> | </li>
        <li id="unselectall"><a href="javascript:clearAll();">Unselect All</a> &nbsp; &nbsp; </li>
        <li class="withSelected">With Selected: </li>
        <li id="delete"><a href="javascript:;"
            onclick="if (hasSelection() &amp;&amp; confirm('Are you sure you want to delete the selected files?')) {getDeleteFileList(document.forms[0].selectedPath.value);}">Delete</a></li>
        <!--  <li id="rename"><a href="javascript:;">Rename</a> | </li> -->
      </ul>
      <div class="clear"></div>
    </div>
    
    <div id="fileList">
        
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>&#160;</th>
              <th><a href="javascript:;" class="sort">Filename</a></th>
              <th><a href="javascript:;" class="sort">Size</a></th>
              <!-- <th><a href="javascript:;" class="sort">Type</a></th> -->
              <th><a href="javascript:;" class="sort">Modified</a></th>
            </tr>
          </thead>
          <tbody>
            <tr var="hFolder"></tr>
            <tr var="hFile"></tr>
            <tr repeat="row" var="row">
              <td class="check"><input type="checkbox" name="fileSelect" value="" var="fileSelect" /></td>
              <td var="file"><a href="javascript:;" var="fileUrl"></a></td>
              <td var="size">&#160;</td>
              <!-- <td var="type">folder</td> -->
              <td var="modified">2007-05-15 13:42</td>
            </tr>
          </tbody>
        </table>
        
      <p>&#160;</p>
    </div>
</div>
<?php
$html = ob_get_clean();
$template = Dom_Template::load($html);

// Events
if ($request->exists('dl')) {
    $fileList = getSelectedFiles();
    //vd($fileList);
    $success = true;
    foreach ($fileList as $i => $file) {
        $b = deleteFile(cleanPath($currentPath . '/' . $file));
        $success = $b && $success;
    }
    if ($success) {
        $template->insertText('progress', count($fileList) . ' File(s) Deleted');
    } else {
        $template->insertText('progress', 'Errors were encounterd deleting the selected file(s). Check file permissions.');
    }
}

// show()
$numFolders = 0;
$numFiles = 0;
$totalSize = 0;
$files = scandir($currentPath, 0);
$idx = 0;
foreach ($files as $i => $file) {
    if ($file == '.' || ($file == '..' && ($selectedPath == '' || $selectedPath == '/'))) {
        continue;
    }
    
    $repeat = $template->getRepeat('row');
    $repeat->setAttr('fileSelect', 'value', $file);
    $repeat->setAttr('fileSelect', 'name', 'fileSelect_'.$idx);
    
    $fileStr = $file;
    if (strlen($fileStr) > 30) {
        $fileStr = substr($fileStr, 0, 30) . '...';
    }
    $repeat->insertText('fileUrl', $fileStr);
    $repeat->insertText('modified', date("Y-m-d H:i:s", filemtime($currentPath . '/' . $file)));
    
    if (is_file($currentPath.'/'.$file)) {
        $url = new Tk_Type_Url(cleanPath($fileHtdoc . $selectedPath . '/' . $file));
        $ext = $url->getExtension();
        $onclick = '';
        
        $onclick = sprintf("initPreview('%s');", $url->toString());
        $onclick .= "toggle(document.forms[0].fileSelect_$idx);";
        
        $repeat->setAttr('fileUrl', 'onclick', $onclick);
        $repeat->setAttr('download', 'href', $url->toString());
        
        $size = filesize(cleanPath($currentPath.'/'.$file));
        $totalSize += $size;
        
        $repeat->insertText('size', Tk_Type_Path::bytes2String($size));
        $repeat->insertText('type', $url->getExtension());
        $repeat->setAttr('file', 'class', 'file ext_' . $ext);
        $repeat->appendRepeat('hFile');
        $numFiles++;
    } else if (is_dir(cleanPath($currentPath.'/'.$file))) {
        $url = $request->getRequestUri();
        if ($file == '..') {
            $path = dirname($selectedPath);
        } else {
            $path =  $selectedPath . '/' . $file;
        }
        $path = cleanPath($path);
        //$onclick = sprintf("setWaiting(); document.forms[0].selectedPath.value = '%s'; getFileList('%s');", $path, $path);
        $onclick = sprintf("setWaiting(); setPath('%s'); getFileList('%s');", $path, $path);
        $repeat->setAttr('fileUrl', 'onclick', $onclick);
        
        $repeat->setAttr('file', 'class', 'dir');
        $repeat->insertText('folder', $url->getExtension());
        $repeat->setAttr('file', 'onclick', "setPwd('" . $path ."');setWaiting();");
        $repeat->appendRepeat('hFolder');
        $numFolders++;
    }
    $idx++;
}

if ($selectedPath == '') {
    $selectedPath = '/';
}
$template->insertText('pwd', $selectedPath);

// folder Data
$template->insertText('numFolders', $numFolders);
$template->insertText('numFiles', $numFiles);
$template->insertText('totalSize', Tk_Type_Path::bytes2String($totalSize));

$p = "RO";
if(is_writable($currentPath)) {
  $p = "RW";
}
$template->insertText('permissions', $p);

echo $template->getDocument()->saveHTML();

/********************* PHP FUNCTIONS ******************************/
function getSelectedFiles()
{
    global $request;
    $files = array();
    foreach ($request->getParameterValues('fileSelect') as $name) {
        if ($name == '.' || $name == '..') {
            continue;
        }
        $files[] = str_replace(array('/',"\\"), array('\\', ''), $name);
    }
    return $files;
}
function deleteFile($_target )
{
    //file?
    if( is_file($_target) ) {
        if( is_writable($_target) ) {
            if( @unlink($_target) ) {
                return true;
            }
        }
        return false;
    }
    //dir?
    if( is_dir($_target) ) {
        if( is_writeable($_target) ) {
            foreach( new DirectoryIterator($_target) as $_res ) {
                if( $_res->isDot() ) {
                    unset($_res);
                    continue;
                }
                if( $_res->isFile() ) {
                    deleteFile( $_res->getPathName() );
                } elseif( $_res->isDir() ) {
                    deleteFile( $_res->getRealPath() );
                }
                unset($_res);
            }
            if( @rmdir($_target) ) {
                return true;
            }
        }
        return false;
    }
}
