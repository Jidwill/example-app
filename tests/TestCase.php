<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Pagination\Paginator;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Livewire components that use WithPagination swap in Livewire's own pagination view
        // and swap it back afterwards - but not if the component throws an exception (some tests
        // do that on purpose). All tests run in one PHP process, so reset it here, otherwise
        // normal pages tested afterwards get Livewire's buttons instead of page links.
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');
    }
}
