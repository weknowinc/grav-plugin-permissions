<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

/**
 * Class PermissionsPlugin
 * @package Grav\Plugin
 */
class PermissionsPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onTwigSiteVariables()
    {
        if ($this->isAdmin()) {
            $this->grav['locator']->addPath('blueprints', '', __DIR__ . DS . 'blueprints');
        }
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 1000]
        ]);
    }

    /**
     * Check if site/page is private and user have correct role assigned
     */
    public function onPageInitialized()
    {
        // Validate user is logged in
        if (!$this->grav['user']['authenticated']) {
            return;
        }

        // Get topParent if any or current page
        $page = $this->grav['page']->topParent()->isPage()?$this->grav['page']->topParent():$this->grav['page'];
        $header = $page->header();

        if (!property_exists($header, 'access')){
            return;
        }

        if (!array_key_exists('view', $header->access)){
            return;
        }

        // Validate page access vs user access groups intersection
        $access = $header->access['view'];
        $groups = $this->grav['user']->groups;
        if (array_intersect($access, $groups)) {
            return;
        }

        $this->grav['page']->modifyHeader('access', array('site.login' => false));
    }

}
