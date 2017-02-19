<?php
namespace Grav\Plugin;

use Grav\Common\Page\Collection;
use Grav\Common\Plugin;
use Grav\Common\Taxonomy;
use Grav\Common\Uri;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class RandomizerPlugin
 * @package Grav\Plugin
 */
class RandomizerPlugin extends Plugin
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
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
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
        /** @var Uri $uri */
        $uri = $this->grav['uri'];

        $route = $this->config->get('plugins.randomizer.route');

        if ($route && $route == $uri->path()) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0]
            ]);
        }
    }

    /**
     * Send user to a random page
     */
    public function onPageInitialized()
    {
        /** @var Taxonomy $taxonomy_map */
        $taxonomy_map = $this->grav['taxonomy'];

        $filters = (array) $this->config->get('plugins.randomizer.filters');
        $operator = $this->config->get('plugins.randomizer.filter_combinator', 'and');

        if (count($filters)) {
            $collection = new Collection();
            $collection->append($taxonomy_map->findTaxonomy($filters, $operator)->toArray());
            if (count($collection)) {
                unset($this->grav['page']);
                $this->grav['page'] = $collection->random()->current();
            }
        }
    }
}
