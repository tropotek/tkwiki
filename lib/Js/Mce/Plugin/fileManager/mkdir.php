<?php
include('prepend.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <title>Create Directory</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/dialog.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
    
    
</head>

<body>
<form id="FileManager">
  <input type="hidden" name="selectedPath" id="fid-selectedPath" value="" />
  <p>&#160;</p>
  <p var="msg" class="error"></p>
  <label for="fid-dname">New Directory Name:</label>
  <input type="text" name="dname" id="fid-dname" />
  <p>&#160;</p>
  
  <div class="mceActionPanel">
    <div style="float: left">
      <input type="submit" id="insert" name="process" value="Create" />
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
    
    if ($request->exists('dname') && preg_match('/^[a-zA-Z0-9\._-]{1,128}$/', $request->getParameter('dname'))) {
        if (!mkdir($currentPath.'/'.$request->getParameter('dname'), 0777)) {
            $template->insertText('msg', 'Error creating directory.');
        } else {
            $template->setChoice('close');
        }
    } else {
        $template->insertText('msg', 'Invalid folder name. Valid characters are `a-zA-Z0-9._-`');
    }
}

echo $template->getDocument()->saveHTML();

