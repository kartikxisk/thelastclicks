<?php

it('crew index permanently redirects to about', function () {
    $this->get('/crew')->assertRedirect('/about')->assertStatus(301);
});

it('crew detail permanently redirects to about', function () {
    $this->get('/crew/anyone')->assertRedirect('/about')->assertStatus(301);
});
