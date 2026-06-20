<?php

it('GET /contact renders the form', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('Tell us about it')
        ->assertSee('name="email"', false)
        ->assertSee('name="_token"', false);
});
