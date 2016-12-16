<?php

namespace Backend\Modules\News\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model;
use Backend\Modules\News\Engine\Images as BackendNewsImagesModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class DeleteImage extends ActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendNewsImagesModel::exists($this->id)) {
            parent::execute();
            $this->record = (array) BackendNewsImagesModel::get($this->id);
            Model::deleteThumbnails(FRONTEND_FILES_PATH . '/' . $this->getModule() . '/images', $this->record['filename']);

            BackendNewsImagesModel::delete($this->id);

            Model::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                Model::createURLForAction('Edit') . '&report=deleted&id=' . $this->record['news_post_id'] . '#tabImages'
            );
        } else {
            $this->redirect(Model::createURLForAction('Edit') . '&error=non-existing');
        }
    }
}
