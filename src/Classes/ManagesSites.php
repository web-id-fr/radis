<?php

namespace WebId\Radis\Classes;

use Laravel\Forge\Actions\ManagesSites as ManagesSitesOriginal;

trait ManagesSites
{
    use ManagesSitesOriginal;

    /**
     * Get the collection of sites.
     *
     * @param  int  $serverId
     * @return Site[]
     */
    public function sites($serverId)
    {
        return $this->transformCollection(
            $this->get("servers/$serverId/sites")['sites'],
            Site::class,
            ['server_id' => $serverId]
        );
    }

    /**
     * Get a site instance.
     *
     * @param  int  $serverId
     * @param  int  $siteId
     * @return Site
     */
    public function site($serverId, $siteId)
    {
        return new Site(
            $this->get("servers/$serverId/sites/$siteId")['site'] + ['server_id' => $serverId],
            $this
        );
    }

    /**
     * Create a new site.
     *
     * @param  int  $serverId
     * @param  array  $data
     * @param  bool  $wait
     * @return Site
     */
    public function createSite($serverId, array $data, $wait = true)
    {
        $site = $this->post("servers/$serverId/sites", $data)['site'];

        if ($wait) {
            return $this->retry($this->getTimeout(), function () use ($serverId, $site) {
                $site = $this->site($serverId, $site['id']);

                return $site->status == 'installed' ? $site : null;
            });
        }

        return new Site($site + ['server_id' => $serverId], $this);
    }

    /**
     * Update the given site.
     *
     * @param  int  $serverId
     * @param  int  $siteId
     * @param  array  $data
     * @return Site
     */
    public function updateSite($serverId, $siteId, array $data)
    {
        return new Site(
            $this->put("servers/$serverId/sites/$siteId", $data)['site']
            + ['server_id' => $serverId],
            $this
        );
    }

    /**
     * Add Site Aliases.
     *
     * @param  int  $serverId
     * @param  int  $siteId
     * @param  array  $aliases
     * @return Site
     */
    public function addSiteAliases($serverId, $siteId, array $aliases)
    {
        return new Site(
            $this->put("servers/$serverId/sites/$siteId/aliases", compact('aliases'))['site']
            + ['server_id' => $serverId],
            $this
        );
    }

    /**
     * Change the given site's PHP version
     *
     * @param  int  $serverId
     * @param  int  $siteId
     * @param  string  $version
     * @return void
     */
    public function changeSitePHPVersion($serverId, $siteId, $version)
    {
        $this->put("servers/$serverId/sites/$siteId/php", ['version' => $version]);
    }
}
