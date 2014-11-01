<?php
$htdocRoot = dirname(dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['PHP_SELF'])))))));
$sitePath  = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
$libPath = $sitePath . '/lib';
include $libPath . '/Tk/Tk.php';
Tk::init($sitePath, $libPath, 'Com/_prepend.php', $htdocRoot);

// Start Output buffer
ob_start();

//Tk::loadConfig('js.tinymce');
Tk_Type_Url::$pathPrefix = $htdocRoot;
Tk_Type_Path::$pathPrefix = $sitePath;
$request = Tk_Request::getInstance();

ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

  <title>Find Wiki Page</title>
  <link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
  <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
  <script type="text/javascript" src="js/dialog.js"></script>
    
<style type="text/css">
th {
  text-align: center;
  padding: 1px 20px;
}
td {
  padding: 1px 4px;
}
td.modified {
  white-space: nowrap;
  text-align: right;
}

.Com_Ui_Pager {
  margin: 0px 0px;
  padding: 2px 10px 2px 10px;
  font-size: 90%;
  float: right;
}
  .Com_Ui_Pager ul {
    list-style-type: none;
  }
  .Com_Ui_Pager li {
    float: left;
    display: inline;
    margin: 0 5px 0 0;
    display: block;
  }
  .Com_Ui_Pager li.selected a {
    font-weight: bold;
    text-decoration: none;
  }
  .Com_Ui_Pager li.off a {
    color: #000;
    text-decoration: none;
  }
  
.pageList{
  overflow: auto;
  height: 320px;
  border: 1px solid #333;
}
</style>
</head>
<body>
  
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <th>Title</th>
      <th>Modified</th>
    </tr>
  </table>
  <div class="pageList">
    <table border="0" cellpadding="0" cellspacing="0">
      <tr repeat="row">
        <td width="100%"><a href="javascript:;" var="title"></a></td>
        <td class="modified" var="modified"></td>
      </tr>
    </table>
  </div>
  
  <div class="mceActionPanel">
    <div var="Com_Ui_Pager" class="clearfix"></div>
    <div style="float: left;">
      <input type="button" id="cancel" name="cancel" value="Cancel" onclick="tinyMCEPopup.close();" />
    </div>
  </div>
  

</body>
</html>
<?php
$html = ob_get_clean();
$template = Dom_Template::load($html);

$tool = Tk_Db_Tool::createFromRequest('0', 'title', 20);
$pageList = Wik_Db_PageLoader::findAll($tool);

$pager = Com_Ui_Pager::createFromTool($tool);
$pager->show($pager->getTemplate());
$template->insertTemplate($pager->getInsertVar(), $pager->getTemplate());

/* @var $page Ext_Db_Page */
foreach ($pageList as $page) {
    $repeat = $template->getRepeat('row');
    $repeat->insertText('title', substr($page->getTitle(), 0, 35));
    $title = addslashes($page->getTitle());
    $repeat->setAttr('title', 'href', "javascript:WikiFindPageDialog.insert('{$page->getName()}', '$title');");
    $repeat->setAttr('title', 'title', $page->getTitle());
    $repeat->insertText('modified', $page->getModified()->toString(Tk_Type_Date::F_LONG_DATETIME));
    $repeat->appendRepeat();
}

echo $template->getDocument()->saveHTML();

?>