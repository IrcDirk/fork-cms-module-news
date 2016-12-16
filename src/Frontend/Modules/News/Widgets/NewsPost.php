<?php

namespace Frontend\Modules\News\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\News\Engine\Model as FrontendNewsModel;

class NewsPost extends FrontendBaseWidget
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
        if (isset($this->data['id'])) {
            $this->tpl->assign('widgetNewsNewsPost', FrontendNewsModel::getById($this->data['id']));
        }
    }
}
