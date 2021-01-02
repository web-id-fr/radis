<?php

namespace WebId\Radis\Tests;

use Orchestra\Testbench\TestCase;

class ConfigTest extends TestCase
{
    const RADIS_CONFIG_PATH = __DIR__.'/../config/radis.php';

    private array $radisConfig;

    /** @before */
    public function loadConfig()
    {
        $this->assertFileExists(self::RADIS_CONFIG_PATH);

        $this->radisConfig = require(self::RADIS_CONFIG_PATH);
    }

    /** @test */
    public function it_should_be_a_valid_config_file()
    {
        $this->assertIsArray($this->radisConfig);
    }
}
