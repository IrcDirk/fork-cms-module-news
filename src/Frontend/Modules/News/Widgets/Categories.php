<?php

namespace Frontend\Modules\News\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\News\Engine\Model as FrontendNewsModel;
use Frontend\Modules\News\Engine\Categories as FrontendNewsCategoriesModel;

class Categories extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        $this->tpl->assign('widgetNewsCategories', FrontendNewsCategoriesModel::getAll(array('parent_id' => 0)));
    }
}
