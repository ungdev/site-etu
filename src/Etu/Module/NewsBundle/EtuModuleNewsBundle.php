<?php

namespace Etu\Module\NewsBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;

class EtuModuleNewsBundle extends Module
{

    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getAdminMenuBuilder()
            ->getBlock('base.admin_menu.title')
            ->add('news.admin.menu_item')
            ->setIcon('briefcase.png')
            ->setUrl($this->getRouter()->generate('news_index'))
            ->setPosition(7)
            ->setRole('ROLE_NEWS_ADMIN')
            ->end();

        $this->getSidebarBuilder()
            ->getBlock('base.sidebar.services.title')
            ->add('news.sidebar.service')
            ->setPosition(100)
            ->setIcon('briefcase.png')
            ->setUrl($this->router->generate('news_index'))
            ->setRole('ROLE_NEWS_READ')
            ->end();
    }

    /**
     * Module identifier (to be required by other modules)
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'news';
    }

    /**
     * Module title (describe shortly its aim)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'News';
    }

    /**
     * Module author
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Arnaud Dufour';
    }

    /**
     * Module description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module to handle news';
    }

    /**
     * @return array
     */
    public function getAvailablePermissions()
    {
        return [
            new OrgaPermission('news', 'Peut modifier les news de l\'association'),
        ];
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers
     *
     * @return array
     */
    public function getRequirements()
    {
        return array(
            // Insert your requirements here
        );
    }
}
