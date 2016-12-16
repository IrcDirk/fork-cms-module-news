<?php

namespace Frontend\Modules\News\Actions;

use Frontend\Core\Engine\Base\Block;
use Frontend\Core\Engine\Model;
use Frontend\Core\Engine\Navigation;
use Frontend\Modules\News\Engine\Model as FrontendNewsModel;
use Frontend\Modules\News\Engine\Categories as FrontendNewsCategoriesModel;

/**
 * This is the index-action (default), it will display the overview of News posts
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Detail extends Block
{
    /**
     * The record
     *
     * @var    array
     */
    private $record;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->tpl->assignGlobal('hideContentTitle', true);
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    /**
     * Get the data
     */
    private function getData()
    {
        $parameter = $this->URL->getParameter(1);

        if (empty($parameter)) {
            $this->redirect(Navigation::getURL(404));
        }

        // load revision
        if ($this->URL->getParameter('draft', 'bool')) {
            // get data
            $this->record = FrontendNewsModel::getDraft($parameter);

            // add no-index, so the draft won't get accidentally indexed
            $this->header->addMetaData(array('name' => 'robots', 'content' => 'noindex, nofollow'), true);
        } else {
            // get by URL
             $this->record = FrontendNewsModel::get($parameter);
        }

        if (empty($this->record)) {
            $this->redirect(Navigation::getURL(404));
        }
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        if ($this->get('fork.settings')->get('News', 'use_image_as_og_image') && $this->record['image']) {
            $this->header->addOpenGraphImage(FRONTEND_FILES_URL . '/News/image/1200x630/' . $this->record['image']);
        }

        // build Facebook  OpenGraph data
        $this->header->addOpenGraphData('title', $this->record['name'], true);
        $this->header->addOpenGraphData(
            'url',
            SITE_URL . $this->record['full_url'],
            true
        );
        $this->header->addOpenGraphData(
            'site_name',
            $this->get('fork.settings')->get('Core', 'site_title_' . FRONTEND_LANGUAGE, SITE_DEFAULT_TITLE),
            true
        );
        $this->header->addOpenGraphData('description', $this->record['seo_description'], true);

        // add into breadcrumb
        $this->breadcrumb->addElement($this->record['name']);

        // set meta
        $this->header->setPageTitle($this->record['seo_title'], ($this->record['seo_title_overwrite'] == 'Y'));
        $this->header->addMetaDescription(
            $this->record['seo_description'],
            ($this->record['seo_description_overwrite'] == 'Y')
        );

        $navigation = FrontendNewsModel::getNavigation($this->record['id']);
        $this->tpl->assign('navigation', $navigation);


        // assign item
        $this->tpl->assign('item', $this->record);
    }

    /**
     * @return mixed
     */
    private function getLastParameter()
    {
        $numberOfParameters = count($this->URL->getParameters());
        return $this->URL->getParameter($numberOfParameters - 1);
    }
}
