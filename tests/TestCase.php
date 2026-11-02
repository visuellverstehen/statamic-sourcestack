<?php

namespace Tests;

use Statamic\Testing\AddonTestCase;
use VV\SourceStack\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
