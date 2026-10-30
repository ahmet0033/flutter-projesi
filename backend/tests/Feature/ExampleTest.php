<?php

test('the root redirects to the admin dashboard', function () {
    $this->get('/')->assertRedirect('/admin');
});
