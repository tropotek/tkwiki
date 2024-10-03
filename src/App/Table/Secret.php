<?php
namespace App\Table;

use App\Db\User;
use Bs\Mvc\Table;
use Tk\Form\Field\Checkbox;
use Tk\Form\Field\Input;
use Tk\Form\Field\Select;
use Tk\Uri;
use Tk\Db;
use Tk\Table\Action\Delete;
use Tk\Table\Cell;
use Tk\Table\Cell\RowSelect;

class Secret extends Table
{

    public function init(): static
    {

        $rowSelect = RowSelect::create('id', 'secretId');
        $this->appendCell($rowSelect);

        $this->appendCell('actions')
            ->addCss('text-nowrap text-center')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                $url = $obj->url;
                return <<<HTML
                    <a class="btn btn-sm btn-outline-primary" href="$url" title="Open in new tab" target="_blank"><i class="fa fa-globe"></i></a>
                HTML;
            });

        $this->appendCell('otp')
            ->addCss('text-nowrap text-center wk-secret')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                if (empty($obj->otp)) return '';
                $cell->setAttr('data-secret-hash', $obj->hash);
                return <<<HTML
                    <a href="javascript:;" class="btn btn-sm btn-outline-success cp-otp"><i class="fa fa-refresh"></i></a> <em class="otp-code">------</em>
                HTML;
            });

        $this->appendCell('name')
            ->addCss('text-nowrap')
            ->addHeaderCss('max-width')
            ->setSortable(true)
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                $url = Uri::create('/secretEdit', ['h' => $obj->hash]);
                return sprintf('<a href="%s">%s</a>', $url, $obj->name);
            });

        $this->appendCell('userId')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getUser()->nameShort;
            });

        $this->appendCell('permission')
            ->addCss('text-nowrap')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getPermissionLabel();
            });

        $this->appendCell('publish')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tk\Table\Type\Boolean::onValue');

        $this->appendCell('created')
            ->addCss('text-nowrap')
            ->setSortable(true)
            ->addOnValue('\Tk\Table\Type\DateFmt::onValue');


        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: name');

        $this->getForm()->appendField(new Select('permission', array_flip(\App\Db\Secret::PERM_LIST)))
            ->prependOption('-- Permission -- ', '')
            ->setStrict(true);

        $this->getForm()->appendField(new Checkbox('otp', ['otp' => 'y']));

        // init filter fields for actions to access to the filter values
        $this->initForm();

        // Add Table actions
        $this->appendAction(Delete::create($rowSelect))
            ->addOnDelete(function(Delete $action, array $selected) {
                foreach ($selected as $secret_id) {
                    $secret = \App\Db\Secret::find($secret_id);
                    if ($secret?->canEdit(User::getAuthUser())) {
                        Db::delete('secret', compact('secret_id'));
                    }
                }
            });

        return $this;
    }

}