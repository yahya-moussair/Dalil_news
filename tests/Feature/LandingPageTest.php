<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the public landing page renders the welcome screen', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('welcome'));
});
