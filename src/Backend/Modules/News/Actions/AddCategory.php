<?php

namespace Backend\Modules\News\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Form;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\News\Engine\Model as BackendNewsModel;
use Backend\Modules\News\Engine\Category as BackendNewsCategoryModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;

use Backend\Modules\SiteHelpers\Engine\Helper as SiteHelpersHelper;
use Common\Uri as CommonUri;

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class AddCategory extends ActionAdd
{
    /**
     * Execute the actions
     */
    public function execute()
    {
        parent::execute();

        $this->languages = SiteHelpersHelper::getActiveLanguages();

        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        $this->frm = new Form('add');

        // set hidden values
        $rbtHiddenValues[] = array('label' => Language::lbl('Hidden', $this->URL->getModule()), 'value' => 'Y');
        $rbtHiddenValues[] = array('label' => Language::lbl('Published'), 'value' => 'N');

        $this->frm->addRadiobutton('hidden', $rbtHiddenValues, 'N');

        $categories = BackendNewsCategoryModel::getForDropdown();
        $this->frm->addDropdown('parent_id', $categories)->setDefaultElement('',0);

        foreach($this->languages as &$language)
        {
            $field = $this->frm->addText('name_'. $language['abbreviation'], isset($this->record['content'][$language['abbreviation']]['name']) ? $this->record['content'][$language['abbreviation']]['name'] : '', null, 'form-control title', 'form-control danger title');
            $language['name_field'] = $field->parse();

            $field = $this->frm->addEditor('description_'. $language['abbreviation'], isset($this->record['content'][$language['abbreviation']]['description']) ? $this->record['content'][$language['abbreviation']]['description'] : '');
            $language['description_field'] = $field->parse();
        }
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();

        $this->tpl->assign('languages', $this->languages);
        $this->tpl->assign('allow_subcategories', $this->get('fork.settings')->get($this->getModule(), 'allow_subcategories', false));
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            // validation
            $fields = $this->frm->getFields();


            foreach($this->languages as $key => $language)
            {
                 $field = $this->frm->getField('name_'. $this->languages[$key]['abbreviation'])->isFilled(Language::getError('FieldIsRequired'));
                 $this->languages [$key]['name_errors'] = $this->frm->getField('name_'. $this->languages[$key]['abbreviation'])->getErrors();
            }

            if ($this->frm->isCorrect()) {
                // build the item
                $item['hidden'] = $fields['hidden']->getValue();
                $item['sequence'] = BackendNewsCategoryModel::getMaximumSequence() + 1;
                $item['parent_id']  = $fields['parent_id']->getValue();
                $item['id'] = BackendNewsCategoryModel::insert($item);

                if($item['parent_id'] == 0){
                    $item['path'] = '/' . $item['id'] . '/';
                    BackendNewsCategoryModel::update($item);
                } else {
                    $category = BackendNewsCategoryModel::get($item['parent_id']);
                    $item['path'] =  $category['path'] . $item['id'] . '/';
                    BackendNewsCategoryModel::update($item);
                }

                $content = array();

                foreach($this->languages as $language)
                {
                    $specific['category_id'] = $item['id'];

                    $specific['language'] = $language['abbreviation'];
                    $specific['name'] = $this->frm->getField('name_'. $language['abbreviation'])->getValue();
                    $specific['url'] =  BackendNewsCategoryModel::getURL(CommonUri::getUrl($specific['name']), $language['abbreviation']);
                    $specific['description'] = ($this->frm->getField('description_'. $language['abbreviation'])->isFilled()) ? $this->frm->getField('description_'. $language['abbreviation'])->getValue() : null;
                    $content[$language['abbreviation']] = $specific;
                }

                // insert it
               BackendNewsCategoryModel::insertContent($content);

                Model::triggerEvent(
                    $this->getModule(), 'after_category_add', $item
                );
                $this->redirect(
                    Model::createURLForAction('EditCategory') . '&report=added&id=' . $item['id']
                );
            }
        }
    }
}
