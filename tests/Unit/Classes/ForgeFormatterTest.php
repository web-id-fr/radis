<?php

namespace WebId\Radis\Tests\Unit\Classes;

use WebId\Radis\Classes\ForgeFormatter;
use WebId\Radis\Tests\TestCase;

class ForgeFormatterTest extends TestCase
{
    /** @test */
    public function it_will_format_database_name()
    {
        $this->assertEquals('fakedb', ForgeFormatter::getFeatureDatabase('fake'));
    }

    /** @test */
    public function it_will_not_format_database_name_with_database_parameter()
    {
        $this->assertEquals('override', ForgeFormatter::getFeatureDatabase('fake', 'override'));
    }

    /** @test */
    public function it_will_format_database_user()
    {
        $this->assertEquals('fakedbuser', ForgeFormatter::getFeatureDatabaseUser('fake'));
    }

    /** @test */
    public function it_will_format_domain()
    {
        $this->assertEquals('fake.fake.ninja', ForgeFormatter::getFeatureDomain('fake'));
    }

    /** @test */
    public function it_will_format_password()
    {
        $this->assertEquals('passwordVerySecure', ForgeFormatter::getFeatureDatabasePassword());
    }
}
