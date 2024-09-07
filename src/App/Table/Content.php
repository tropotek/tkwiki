<?php
namespace App\Table;

use Bs\Table;
use Tk\Alert;
use Tk\Exception;
use Tk\Form\Field\Input;
use Tk\Uri;
use Tt\Table\Cell;

class Content extends Table
{

    protected ?\App\Db\Page $wPage = null;

    public function init(): static
    {
        if (!$this->wPage) {
            throw new Exception("Wiki page not found");
        }

        $this->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(\App\Db\Content $obj, Cell $cell) {
                $revUrl  = Uri::create()->set('r', $obj->contentId);
                $viewUrl = Uri::create('/view')->set('contentId', $obj->contentId);
                return <<<HTML
                    <a class="btn btn-outline-secondary" href="$revUrl" title="Revert" data-confirm="Are you sure you want to revert the content to revision {$obj->contentId}?"><i class="fa fa-fw fa-share"></i></a>
                    <a class="btn btn-outline-secondary" href="$viewUrl" title="View"><i class="fa fa-fw fa-eye"></i></a>
                HTML;
            });

        $this->appendCell('contentId')
            ->setHeader('Revision')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Content $obj, Cell $cell) {
                if ($this->wPage->getContent()?->contentId == $obj->contentId) {
                    return sprintf('<strong title="Current">%s</strong>', $obj->contentId);
                }
                return $obj->contentId;
            });

        $this->appendCell('created')
            ->addHeaderCss('max-width')
            ->addCss('text-nowrap')
            ->addOnValue('\Tt\Table\Type\DateTime::onValue');

        $this->appendCell('userId')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Content $obj, Cell $cell) {
                return $obj->getUser()->getName();
            });

        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: name');

        return $this;
    }

    public function execute(): static
    {
        if (isset($_GET['r'])) {
            $this->doRevert(intval($_GET['r']));
        }

        parent::execute();
        return $this;
    }

    public function doRevert(int $revId): void
    {
        $revision = \App\Db\Content::find($revId);
        if (!$revision) {
            Alert::addWarning("Failed to revert to revision ID $revId");
            $this->wPage->getUrl()->redirect();
        }
        $content = \App\Db\Content::cloneContent($revision);
        $content->save();

        Alert::addSuccess('Page reverted to version ' . $revision->contentId . ' [' . $revision->created->format(\Tk\Date::FORMAT_SHORT_DATETIME) . ']');
        $this->wPage->getUrl()->redirect();
    }

    public function setWikiPage(\App\Db\Page $page)
    {
        $this->wPage = $page;
    }
}