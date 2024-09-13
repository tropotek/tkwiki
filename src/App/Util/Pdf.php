<?php
namespace App\Util;

use Bs\Traits\SystemTrait;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use JetBrains\PhpStorm\NoReturn;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Tk\CurlyTemplate;

/**
 * @note This file uses the mpdf lib
 * @link https://mpdf.github.io/
 */
class Pdf extends Renderer implements DisplayInterface
{
    use SystemTrait;

    protected ?Mpdf  $mpdf      = null;
    protected string $watermark = '';
    protected bool   $rendered  = false;
    protected string $html      = '';
    protected string $title     = '';


    public function __construct(string $html, string $title = 'PDF DOCUMENT', string $watermark = '')
    {
        $this->html = $html;
        $this->title = $title;
        $this->watermark = $watermark;

        $this->initPdf();
    }

    public static function create(string $html, string $title = 'PDF DOCUMENT', string $watermark = ''): static
    {
        return new self($html, $title, $watermark);
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    protected function initPdf(array $data = []): void
    {
        $html = $this->show()->toString();
        $tpl = CurlyTemplate::create($html);
        $parsedHtml = $tpl->parse($data);

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
     */
    #[NoReturn] public function output(string $filename = ''): void
    {
        $this->show();
        if (!$filename)
            $filename = \Tk\Uri::create()->basename() . '.pdf';

        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
        header('Pragma: no-cache'); // HTTP 1.0
        header('Expires: 0'); // Proxies
        $this->mpdf->Output($filename, Destination::INLINE);
        exit;
    }

    /**
     * Return the PDF as a string to attach to an email message
     */
    public function getPdfAttachment(string $filename = ''): string
    {
        if (!$filename)
            $filename = \Tk\Uri::create()->basename() . '.pdf';
        return $this->mpdf->Output($filename, Destination::STRING_RETURN);
    }

    public function show(): ?Template
    {
        $template = $this->getTemplate();
        $template->setTitleText($this->getTitle());
        if ($this->rendered) return $template;
        $this->rendered = true;

        $template->appendText('title', $this->getTitle());
        $template->appendHtml('content', $this->getHtml());

        $this->getFactory()->getTemplateModifier()->execute($template->getDocument());
        return $template;
    }

    public function __makeTemplate(): ?Template
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body class="" style="" var="body">
  <h1 var="title"></h1>
  <div var="content"></div>
</body>
</html>
HTML;

        return $this->loadTemplate($html);
    }

}
