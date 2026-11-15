<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Pagination\Paginator;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // components with WithPagination swap in Livewire's own pagination view and then
        // swap it back, but not if the component throws (some tests do that on purpose).
        // All the tests run in one PHP process so I reset it here, otherwise pages tested
        // afterwards get Livewire's buttons instead of normal page links.
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');
    }
}
