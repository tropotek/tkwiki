<?php
namespace App\Ui;

use Bs\Uri;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\ConfigTrait;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 *
 * @note This file uses the mpdf lib
 * @link https://mpdf.github.io/
 */
class Pdf extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use ConfigTrait;

    /**
     * @var \Mpdf\Mpdf
     */
    protected $mpdf = null;

    /**
     * @var string
     */
    protected $watermark = '';

    /**
     * @var bool
     */
    protected $rendered = false;

    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $title = '';


    /**
     * HtmlInvoice constructor.
     * @param string $html
     * @param string $title
     * @param string $watermark
     * @throws \Exception
     */
    public function __construct($html, $title = 'PDF DOCUMENT', $watermark = '')
    {
        $this->html = $html;
        $this->title = $title;
        $this->watermark = $watermark;

        $this->initPdf();
    }

    /**
     * @param string $html
     * @param string $title
     * @param string $watermark
     * @return Pdf
     * @throws \Exception
     */
    public static function create($html, $title = 'PDF DOCUMENT', $watermark = '')
    {
        $obj = new self($html, $title, $watermark);
        return $obj;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @throws \Exception
     */
    protected function initPdf()
    {
        $html = $this->show()->toString();
        $tpl = \Tk\CurlyTemplate::create($html);
        $parsedHtml = $tpl->parse(array());

        $this->mpdf = new \Mpdf\Mpdf(array(
			'format' => 'A4-P',
            'orientation' => 'P',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 15,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
            'tempDir' => $this->getConfig()->getTempPath()
        ));
        $mpdf = $this->mpdf;
        //$mpdf->setBasePath($url);

        //$mpdf->shrink_tables_to_fit = 0;
        //$mpdf->useSubstitutions = true; // optional - just as an example
        //$mpdf->SetHeader($url . "\n\n" . 'Page {PAGENO}');  // optional - just as an example
        //$mpdf->CSSselectMedia='mpdf'; // assuming you used this in the document header
        //$mpdf->SetProtection(array('print'));

        $mpdf->SetTitle($this->getTitle());
        $mpdf->SetAuthor('FVAS EMS');

        if ($this->watermark) {
            $mpdf->SetWatermarkText($this->watermark);
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = 'DejaVuSansCondensed';
            $mpdf->watermarkTextAlpha = 0.08;
        }
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($parsedHtml);
    }

    /**
     * Output the pdf to the browser
     *
     * @param string $filename
     * @throws \Mpdf\MpdfException
     */
    public function output($filename = '')
    {
        $this->show();
        if (!$filename)
            $filename = \Tk\Uri::create()->basename() . '.pdf';

        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
        header('Pragma: no-cache'); // HTTP 1.0
        header('Expires: 0'); // Proxies
        $this->mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /**
     * Return the PDF as a string to attache to an email message
     *
     * @param string $filename
     * @return string
     * @throws \Mpdf\MpdfException
     */
    public function getPdfAttachment($filename = '')
    {
        if (!$filename)
            $filename = \Tk\Uri::create()->basename() . '.pdf';
        return $this->mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);
    }

    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return null|Template|Renderer
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->setTitleText($this->getTitle());
        if ($this->rendered) return $template;
        $this->rendered = true;

        $template->appendCssUrl(Uri::create('/html/admin/bower_components/bootstrap/dist/css/bootstrap.css'));


        $template->insertText('title', $this->getTitle());
        $template->appendHtml('content', $this->getHtml());

//        if ($this->getCoa()->getBackgroundUrl()) {
//            $template->setAttr('body', 'style', 'background-image: url('.$this->getCoa()->getBackgroundUrl().');background-image-resize: 4; background-image-resolution: from-image;');
//        }

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title></title>
</head>
<body class="" style="" var="body">
  <h1 var="title"></h1>
  <div var="content"></div>
</body>
</html>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}
