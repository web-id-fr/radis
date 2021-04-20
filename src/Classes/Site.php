<?php

namespace WebId\Radis\Classes;

use Laravel\Forge\Resources\Site as SiteOriginal;

class Site extends SiteOriginal {
    /**
     * Change the site's PHP version
     *
     * @param  string  $version
     * @return void
     */
    public function changePHPVersion($version)
    {
        $this->forge->changeSitePHPVersion($this->serverId, $this->id, $version);
    }
}
