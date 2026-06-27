<?php

use Pest\Plugins\Concerns\HandlesArguments;
use PHPUnit\Framework\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test function is bound to a PHPUnit TestCase
| instance. This allows you to use all of the PHPUnit assertions as well as
| some custom Pest assertions to get the most out of your test suite.
|
*/

uses(TestCase::class)->in('./');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Most of Pest's functions are globally available. However, if you choose
| to define custom expectations, you can do so here. This is a great place
| to define your expectations for your application.
|
*/

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
|
| Here you may register hooks to run before and after each test.
| Pest supports "beforeEach", "afterEach", "beforeAll", and "afterAll" hooks.
|
*/

beforeEach(function () {
    // Refresh database before each test
    $this->refreshDatabase();
});

/*
|--------------------------------------------------------------------------
| Plugins
|--------------------------------------------------------------------------
|
| Here you may register plugins for additional functionality. Pest's plugin
| system allows you to extend Pest with custom functionality.
|
*/
