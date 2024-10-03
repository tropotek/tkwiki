<?php
namespace App\Table;

use Bs\Mvc\Table;
use Tk\Form\Field\Input;
use Tk\Table\Cell;

class SecretSelect extends Table
{

    public function init(): static
    {
        $this->appendCell('name')
            ->addHeaderCss('max-width')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return sprintf('<a href="javascript:;" class="wiki-insert"
                    data-secret-hash="%s" data-secret-name="%s" data-secret-url="%s" title="Insert secret link">%s</a>',
                    $obj->hash, $obj->name, $obj->url, $obj->name);
            });

        $this->appendCell('userId')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return $obj->getUser()?->nameShort ?? '';
            });

        $this->appendCell('permission')
            ->addOnValue(function(\App\Db\Secret $obj, Cell $cell) {
                return \App\Db\Secret::PERM_LIST[$obj->permission] ?? '';
            });

        // Add Filter Fields
        $this->getForm()->appendField(new Input('search'))
            ->setAttr('placeholder', 'Search: id, name');

        // init filter fields for actions to access to the filter values
        $this->initForm();

        return $this;

    }

}