<?php

use Tests\TestCase;

test('application returns a successful response', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
});
