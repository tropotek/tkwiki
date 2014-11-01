<?php
include('prepend.php');

ini_set('max_execution_time', 600);   // 10 min timeout for uploads
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <title>{#jdkmanager_dlg.title}</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/dialog.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
    
    
</head>
<body>

<form id="FileManager" method="post" enctype="multipart/form-data">
  <input type="hidden" name="selectedPath" id="fid-selectedPath" value="" />

  <p>&#160;</p>
  <p var="msg" class="error" choice="msg"></p>
  <label for="fid-dname">File:</label>
  <input type="file" name="userfile" />
  <p>Note: The maximum upload size is: <span var="max"/></p>
  <p>&#160;</p>
    
  <div class="mceActionPanel">
    <div style="float: left">
      <input type="submit" id="insert" name="process" value="Upload" />
    </div>
    <div style="float: right">
      <input type="button" id="cancel" name="cancel" value="Cancel" onclick="tinyMCEPopup.close();" />
    </div>
    <div class="clear"></div>
  </div>
  
  
</form>

<script type="text/javascript" choice="close">
//<!--
    FileManagerDialog.reload();
//-->
</script>
</body>
</html>

<?php
$html = ob_get_clean();
$template = Dom_Template::load($html);

$domForm = $template->getForm('FileManager');
$domForm->setAction($_SERVER['PHP_SELF']);
$hiddenEl = $domForm->getFormElement('selectedPath');
$hiddenEl->setValue($selectedPath);

// Events
if ($request->exists('process')) {
    
    $newFile = $currentPath.'/'.fixFile($_FILES['userfile']['name']);
    if (Tk_Type_Path::getFileExtension($newFile) == 'php') {
        $msg = "PHP files are not allowed to be uploaded on this site. Try making it a txt file.";
    } else if ($_FILES['userfile']['size'] > Tk_Type_Path::string2Bytes(ini_get("upload_max_filesize"))) {
       $msg = "Files must be smaller than " . ini_get("upload_max_filesize");
    } else if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newFile)) {
       chmod($newFile, 0644);
       $msg = "File is valid, and was successfully uploaded.";
       $template->setChoice('close');
    } else {
       $msg = "Invalid file, try again.";
    }
    
    if ($msg != null) {
        $template->insertText('msg', $msg);
        $template->setChoice('msg');
    }
}

$template->insertText('max', ini_get("upload_max_filesize"));

echo $template->getDocument()->saveHTML();

/**
 * fixFile
 *
 * @param string $file
 * @return string
 */
function fixFile($file)
{
    $file = preg_replace('/[^a-z0-9_\.-]/i', '_', $file);
    return $file;
}
