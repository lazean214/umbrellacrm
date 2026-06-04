<?php

use App\Models\User;
use App\Services\EmailTemplateParser;

test('it parses legacy token strings', function () {
    $content = 'Hello [user.name], welcome to [company.name].';

    $user = new User([
        'name' => 'Alex User',
        'email' => 'alex@example.com',
    ]);

    $parsed = EmailTemplateParser::parse($content, null, null, null, $user);

    expect($parsed)->toContain('Alex User');
});

test('legacy html mode keeps html tags while replacing tokens', function () {
    $content = '<h2>Hi [user.name]</h2>';

    $user = new User([
        'name' => 'Alex User',
        'email' => 'alex@example.com',
    ]);

    $parsed = EmailTemplateParser::parse($content, null, null, null, $user, true);

    expect($parsed)->toContain('<h2>Hi Alex User</h2>');
});

test('legacy plain text mode renders basic markup to html', function () {
    $content = "Hello [user.name]\n\n**Important**";

    $user = new User([
        'name' => 'Alex User',
        'email' => 'alex@example.com',
    ]);

    $parsed = EmailTemplateParser::parse($content, null, null, null, $user, false);

    expect($parsed)->toContain('Alex User');
    expect($parsed)->toContain('<strong>Important</strong>');
});

test('it renders builder sections into html', function () {
    $sections = [
        [
            'id' => 'a',
            'type' => 'text',
            'content' => 'Thanks for your interest.',
        ],
        [
            'id' => 'b',
            'type' => 'button',
            'label' => 'Open Offer',
            'url' => 'https://example.com/offer',
        ],
    ];

    $html = EmailTemplateParser::parse($sections);

    expect($html)->toContain('Thanks for your interest.');
    expect($html)->toContain('https://example.com/offer');
});
