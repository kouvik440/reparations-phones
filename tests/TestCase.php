<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Testing\TestResponse::macro('assertPage', function (string $component) {
            return $this->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page->component($component)->etc());
        });
        \Illuminate\Testing\TestResponse::macro('assertPageProp', function (string $key, mixed $expected) {
            $actual = str_starts_with($key, 'auth.permissions.')
                ? ($this->inertiaProps('auth.permissions')[substr($key, strlen('auth.permissions.'))] ?? null)
                : $this->inertiaProps($key);
            if ($expected instanceof \Closure) {
                \PHPUnit\Framework\Assert::assertTrue($expected($actual), 'Unexpected Inertia prop: '.$key);
            } else {
                \PHPUnit\Framework\Assert::assertSame($expected, $actual, 'Unexpected Inertia prop: '.$key);
            }

            return $this;
        });
    }
}
