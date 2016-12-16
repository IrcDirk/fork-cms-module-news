<?php

namespace Backend\Modules\News\Installer;

use Backend\Core\Installer\ModuleInstaller;

/**
 * Installer for the News module
 *
 * @author Frederik Heyninck <frederik@figure8.be>
 */
class Installer extends ModuleInstaller
{
    public function install()
    {
        // import the sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // install the module in the database
        $this->addModule('News');

        // install the locale, this is set here beceause we need the module for this
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        $this->setModuleRights(1, 'News');

        $this->setActionRights(1, 'News', 'Add');
        $this->setActionRights(1, 'News', 'AddCategory');
        $this->setActionRights(1, 'News', 'AddImages');
        $this->setActionRights(1, 'News', 'Categories');
        $this->setActionRights(1, 'News', 'Delete');
        $this->setActionRights(1, 'News', 'DeleteCategory');
        $this->setActionRights(1, 'News', 'DeleteImage');
        $this->setActionRights(1, 'News', 'Edit');
        $this->setActionRights(1, 'News', 'EditCategory');
        $this->setActionRights(1, 'News', 'Index');

        $this->setActionRights(1, 'News', 'Sequence');
        $this->setActionRights(1, 'News', 'SequenceCategories');
        $this->setActionRights(1, 'News', 'SequenceImages');
        $this->setActionRights(1, 'News', 'UploadImages');
        $this->setActionRights(1, 'News', 'EditImage');
        $this->setActionRights(1, 'News', 'GetAllTags');

        $this->setActionRights(1, 'News', 'Settings');
        $this->setActionRights(1, 'News', 'GenerateUrl');
        $this->setActionRights(1, 'News', 'UploadImage');

        $this->makeSearchable('News');

        // add extra's
        $subnameID = $this->insertExtra('News', 'block', 'News', null, null, 'N', 1000);
        $this->insertExtra('News', 'block', 'NewsPostDetail', 'Detail', null, 'N', 1001);
        $this->insertExtra('News', 'widget', 'Recent', 'RecentNews', null, 'N', 1001);

        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $navigationModulesId = $this->setNavigation($navigationModulesId, 'News');
        $this->setNavigation($navigationModulesId, 'News', 'news/index', array('news/add','news/edit', 'news/index', 'news/add_images', 'news/edit_image'), 1);
        $this->setNavigation($navigationModulesId, 'Categories', 'news/categories', array('news/add_category','news/edit_category', 'news/categories'), 2);

         // settings navigation
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, 'News', 'news/settings');
    }
}
