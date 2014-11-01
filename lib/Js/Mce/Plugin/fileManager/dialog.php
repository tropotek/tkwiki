<?php
include('prepend.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{#fileManager_dlg.title}</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/dialog.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>
    

<script type="text/javascript">

$(document).ready(function() {
  getFileList();
});
function setPwd(path)
{
    $('#pwd').text(path);
}
function setPath(value)
{
    document.getElementById('fid-selectedPath').value = value;
}
</script>
</head>
<body>
<form id="FileManager">
   <input type="hidden" name="selectedPath" id="fid-selectedPath" value="" />

  <div id="container">
    <div id="header">
      <div class="left path">Path: <span id="pwd" var="path">/</span></div>
      <div class="right">
        <ul class="menu">
          <li><a href="javascript:;" var="createFolder">Create Folder</a></li>
          <li><a href="javascript:;" var="upload">Upload</a></li>
          <li><a href="javascript:;" var="refresh">Refresh</a></li>
        </ul>
      </div>
      <div class="clear"></div>
    </div>
    
    <div id="wrapper">
      <div id="content">

      <div id="content2"><img src="img/waiting.gif" style="margin: auto;width: 16px;display: block;"/></div>
        
        
        
      </div>
    </div>
    
    <div id="navigation">
      <div class="navWrap">
        
        <h3>Preview</h3>
        <div class="filepreview">
          <div id="filename">&nbsp;</div>
            <div class="previewwrap">
                <iframe id="preview" src="javascript:''" frameborder="0" marginwidth="5" marginheight="0" width="190" height="150" ></iframe>
            </div>
            
            <p>Justify:
              <select name="align">
                            <option value="">-- Not set --</option>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                            <option value="middle">Middle</option>
                            <option value="baseline">Baseline</option>
                            <option value="text-top">Text top</option>
                            <option value="text-bottom">Text bottom</option>
                        </select>
            </p>
            
            <p>
              <label for="fid-createLink" title="Create a link to the selected file.">Create Link:</label>
              <input type="checkbox" name="createLink" id="fid-createLink" />
            </p>
    
            <div class="actions">
              <div style="margin: 5px auto;width: 100px;">
                <input type="button" id="insert" name="insert" value="Insert" onclick="FileManagerDialog.insert();" />
              </div>
              <div class="clear"></div>
            </div>
        </div>
        
      </div>
    </div>
  
      <div class="clear"></div>
      <div class="mceActionPanel">
        <div style="text-align: right;">
          <p><a href="http://www.tropotek.com.au/" title="Developed By Tropotek." target="_blank">www.tropotek.com.au</a></p>
        </div>
      </div>
    
  </div>

  
</form>
</body>
</html>

<?php
$html = ob_get_clean();
$template = Dom_Template::load($html);

$domForm = $template->getForm('FileManager');
$domForm->setAction($_SERVER['PHP_SELF']);
$hiddenEl = $domForm->getFormElement('selectedPath');
$hiddenEl->setValue($selectedPath);


$url = new Tk_Type_Url($mceHtdoc . '/plugins/fileManager/mkdir.php');
$createFolderJs = "FileManagerDialog.mkdir('{$url->toString()}');";
$template->setAttr('createFolder', 'onclick', $createFolderJs);

$url = new Tk_Type_Url($mceHtdoc . '/plugins/fileManager/upload.php');
$uploadJs = "FileManagerDialog.upload('{$url->toString()}');";
$template->setAttr('upload', 'onclick', $uploadJs);

$onclick = sprintf("setWaiting();getFileList(document.forms[0].selectedPath.value);");
$template->setAttr('refresh', 'onclick', $onclick);

$template->insertText('path', $selectedPath);

echo $template->getDocument()->saveHTML();

