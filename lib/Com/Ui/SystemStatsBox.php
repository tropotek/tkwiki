<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 * An admin content box. Put text and stats within these box's on the admin home page
 *
 * @package Com
 */
class Com_Ui_SystemStatsBox extends Com_Ui_AdminBox
{

    /**
     * Show
     *
     */
    function show()
    {
        $template = $this->getTemplate();

        $ver = file_get_contents($this->getConfig()->getSitePath() . '/VERSION');
        $totalBytes = Tk_Type_Path::diskSpace($this->getConfig()->getSitePath());

        $html = sprintf('
<table>
  <tbody>
    <tr>
      <td class="name">Hostname</td>
      <td>%s</td>
    </tr>
    <tr>
      <td class="name">Site Version</td>
      <td>%s</td>
    </tr>
    <tr>
      <td class="name">Operating system</td>
      <td>%s</td>
    </tr>
    <tr>
      <td class="name">Hard Drive Usage</td>
      <td>%s</td>
    </tr>
  </tbody>
</table>', $_SERVER['HTTP_HOST'], $ver, PHP_OS, Tk_Type_Path::bytes2String($totalBytes));

        $template->insertText('title', 'System Info');
        $template->insertHtml('content', $html);

    }

}
?>