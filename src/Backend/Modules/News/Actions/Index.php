<?php

namespace Backend\Modules\News\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Authentication;
use Backend\Core\Engine\DataGridDB;
use Backend\Core\Language\Language;
use Backend\Core\Engine\Model;
use Backend\Modules\News\Engine\Model as BackendNewsModel;
use Backend\Core\Engine\Form;
use Backend\Modules\News\Engine\Category as BackendNewsCategoryModel;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;

/**
 * This is the index-action (default), it will display the overview of News posts
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Index extends ActionIndex
{

    private $filter = [];

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->setFilter();
        $this->loadForm();

        $this->loadDataGridNews();
        $this->loadDataGridNewsDrafts();
        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGridNews()
    {
        $query = 'SELECT i.id, c.name,  i.hidden, UNIX_TIMESTAMP(i.publish_on) as publish_on
         FROM news AS i
         INNER JOIN news_post_content as c  on i.id = c.news_post_id';

        if(isset($this->filter['categories'] ) && $this->filter['categories'] !== null && count($this->filter['categories']))
        {
            $query .= ' INNER JOIN news_linked_catgories AS cat ON i.id = cat.news_post_id';
        }

        $query .= ' WHERE 1';

        $parameters = array();
        $query .= ' AND c.language = ?';
        $parameters[] = Language::getWorkingLanguage();

        $query .= ' AND i.status = ?';
        $parameters[] = 'active';

        if($this->filter['value']){
            $query .= ' AND c.name LIKE ?';
            $parameters[] = '%' . $this->filter['value'] . '%';
        }

        if(isset($this->filter['categories'] ) && $this->filter['categories'] !== null && count($this->filter['categories']))
        {
            $query .= ' AND cat.category_id IN(' . implode(',', array_values($this->filter['categories'])) . ')';
        }

        $query .= 'GROUP BY i.id';

        $this->dataGridNews = new DataGridDB(
            $query,
            $parameters
        );

        // set column functions
        $this->dataGridNews->setColumnFunction(
            array(new BackendDataGridFunctions(), 'getLongDate'),
            array('[publish_on]'),
            'publish_on',
            true
        );

        // sorting columns
        $this->dataGridNews->setSortingColumns(array('publish_on', 'name'), 'publish_on');
        $this->dataGridNews->setSortParameter('desc');
        $this->dataGridNews->setURL($this->dataGridNews->getURL() . '&' . http_build_query($this->filter));


        $this->dataGridNews->setColumnAttributes(
            'name', array('class' => 'title')
        );

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGridNews->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGridNews->setColumnURL(
                'name', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGridNewsDrafts()
    {
        $query = 'SELECT i.id, c.name,  i.hidden, UNIX_TIMESTAMP(i.publish_on) as publish_on
         FROM news AS i
         INNER JOIN news_post_content as c  on i.id = c.news_post_id';

        if(isset($this->filter['categories'] ) && $this->filter['categories'] !== null && count($this->filter['categories']))
        {
            $query .= ' INNER JOIN news_linked_catgories AS cat ON i.id = cat.news_post_id';
        }

        $query .= ' WHERE 1';

        $parameters = array();
        $query .= ' AND c.language = ?';
        $parameters[] = Language::getWorkingLanguage();

        $query .= ' AND i.status = ?';
        $parameters[] = 'draft';



        if($this->filter['value']){
            $query .= ' AND c.name LIKE ?';
            $parameters[] = '%' . $this->filter['value'] . '%';
        }

        if(isset($this->filter['categories'] ) && $this->filter['categories'] !== null && count($this->filter['categories']))
        {
            $query .= ' AND cat.category_id IN(' . implode(',', array_values($this->filter['categories'])) . ')';
        }


        $query .= 'GROUP BY i.id';

        $this->dataGridNewsDrafts = new DataGridDB(
            $query,
            $parameters
        );

        // set column functions
        $this->dataGridNewsDrafts->setColumnFunction(
            array(new BackendDataGridFunctions(), 'getLongDate'),
            array('[publish_on]'),
            'publish_on',
            true
        );

        // sorting columns
        $this->dataGridNewsDrafts->setSortingColumns(array('publish_on', 'name'), 'publish_on');
        $this->dataGridNewsDrafts->setSortParameter('desc');

        $this->dataGridNewsDrafts->setURL($this->dataGridNewsDrafts->getURL() . '&' . http_build_query($this->filter));


        $this->dataGridNews->setColumnAttributes(
            'name', array('class' => 'title')
        );

        // check if this action is allowed
        if (Authentication::isAllowedAction('Edit')) {
            $this->dataGridNewsDrafts->addColumn(
                'edit', null, Language::lbl('Edit'),
                Model::createURLForAction('Edit') . '&amp;id=[id]',
                Language::lbl('Edit')
            );
            $this->dataGridNewsDrafts->setColumnURL(
                'name', Model::createURLForAction('Edit') . '&amp;id=[id]'
            );
        }
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        $this->frm = new Form('filter', Model::createURLForAction(), 'get');

        $categories = BackendNewsCategoryModel::getForMultiCheckbox();

        $this->frm->addText('value', $this->filter['value']);

        if(!empty($categories) && Authentication::isAllowedAction('AddCategory'))
        {
            $this->frm->addMultiCheckbox(
                'categories',
                $categories,
                '',
                'noFocus'
            );
        }

        // manually parse fields
        $this->frm->parse($this->tpl);
    }


    /**
     * Sets the filter based on the $_GET array.
     */
    private function setFilter()
    {
        $this->filter['categories'] = $this->getParameter('categories', 'array');
        $this->filter['value'] = $this->getParameter('value') == null ? '' : $this->getParameter('value');
    }


    /**
     * Parse the page
     */
    protected function parse()
    {
        // parse the dataGrid if there are results
        $this->tpl->assign('dataGridNews', (string) $this->dataGridNews->getContent());
        $this->tpl->assign('dataGridNewsDraft', (string) $this->dataGridNewsDrafts->getContent());
    }
}
