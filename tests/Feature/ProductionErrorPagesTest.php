<?php

test('missing pages use the branded Khmer error screen', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('រកមិនឃើញទំព័រ')
        ->assertSee('BizHR');
});

test('forbidden error view renders safely', function () {
    $html = view('errors.403')->render();

    expect($html)
        ->toContain('403')
        ->toContain('អ្នកមិនមានសិទ្ធិ');
});

test('expired-session and server-error views render safely', function () {
    expect(view('errors.419')->render())->toContain('419');
    expect(view('errors.500')->render())->toContain('500');
});
