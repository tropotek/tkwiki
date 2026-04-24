<?php
namespace App\Table;

use App\Db\User;
use Bs\Mvc\Table;
use Bs\Registry;
use Tk\Alert;
use Tk\Db;
use Tk\Exception;
use Tk\Form\Field\Input;
use Tk\Table\Action\Delete;
use Tk\Table\Cell\RowSelect;
use Tk\Uri;
use Tk\Table\Cell;

class Content extends Table
{

    protected ?\App\Db\Page $wPage = null;

    public function init(): static
    {
        if (!$this->wPage) {
            throw new Exception("Wiki page not found");
        }

        if (User::getAuthUser()->isAdmin() || User::getAuthUser()->userId == $this->wPage->userId) {
            $rowSelect = RowSelect::create('id', 'contentId');
            $rowSelect->addOnHtml(function(\App\Db\Content $obj, Cell $cell) {
                if ($this->wPage->contentId == $obj->contentId) {
                    return '';
                }
                return null;
            });
            $this->appendCell($rowSelect);
        }

        $this->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnHtml(function(\App\Db\Content $obj, Cell $cell) {
                $revUrl  = Uri::create()->set('r', $obj->contentId);
                $viewUrl = Uri::create('/view')->set('contentId', $obj->contentId);
                return <<<HTML
                    <a class="btn btn-outline-secondary" href="$revUrl" title="Revert" data-confirm="Are you sure you want to revert the content to revision {$obj->contentId}?"><i class="fa fa-fw fa-share"></i></a>
                    <a class="btn btn-outline-secondary" href="$viewUrl" title="View"><i class="fa fa-fw fa-eye"></i></a>
                HTML;
            });

        $this->appendCell('contentId')
            ->setHeader('Revision')
            ->addCss('text-nowrap max-width')
            ->addOnHtml(function(\App\Db\Content $obj, Cell $cell) {
                if ($this->wPage->contentId == $obj->contentId) {
                    return sprintf('<strong title="Current">%s</strong>', $obj->contentId);
                }
                return $obj->contentId;
            });

        $this->appendCell('userId')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Content $obj, Cell $cell) {
                return $obj->getUser()->nameShort;
            });

        $this->appendCell('created')
            ->addHeaderCss('text-end')
            ->addCss('text-nowrap text-end')
            ->addOnValue('\Tk\Table\Type\Date::getLongDateTime');

        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: name');

        $this->table->appendAction(Delete::create()
            ->addOnExecute(function(Delete $action) use ($rowSelect) {
                if (!(User::getAuthUser()->isAdmin() || User::getAuthUser()->userId == $this->wPage->userId)) {
                    return;
                }
                $selected = $rowSelect->getSelected();
                foreach ($selected as $content_id) {
                    if ($this->wPage->contentId == $content_id) return;
                    Db::delete('content', compact('content_id'));
                }
            }));

        return $this;
    }

    public function execute(?callable $onInit = null): static
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

        Alert::addSuccess('Page reverted to version ' . $revision->contentId . ' [' . $revision->created->format(\Tk\Date::FORMAT_LONG_DATE) . ']');
        $this->wPage->getUrl()->redirect();
    }

    public function setWikiPage(\App\Db\Page $page): void
    {
        $this->wPage = $page;
    }
}