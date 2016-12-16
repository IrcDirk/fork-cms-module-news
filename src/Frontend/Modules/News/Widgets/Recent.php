<?php

namespace Frontend\Modules\News\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\News\Engine\Model as FrontendNewsModel;

class Recent extends FrontendBaseWidget
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
        $this->tpl->assign('widgetNewsRecent', FrontendNewsModel::getAll($this->get('fork.settings')->get('News', 'overview_num_items_recent', 3)));
    }
}
