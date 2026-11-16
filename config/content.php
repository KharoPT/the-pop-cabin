<?php
/**
 * Editable site content: change the text and links here, no template editing needed.
 * Anything left empty ('') is either hidden or shown as an inactive placeholder.
 * The wording below is PLACEHOLDER copy: replace it with the client's own.
 */
return [
    // ---- Home hero ----
    'hero_description' => 'Funko Pop collectibles from The Pop Cabin. Browse the collection below.',

    // ---- Call-to-action section at the bottom of the home page ----
    'cta_text' => 'Browse the collection and pick your Pop.',

    // ---- Social links (footer + home). Empty = icon shows but is not clickable yet ----
    'facebook_url'  => '',
    'tiktok_url'    => '',
    'instagram_url' => '',

    // ---- Footer ----
    'trademark_line' => '',   // optional second line under the copyright, e.g. a trademark notice

    // ---- Home: "Meet the team" (4 cards). 'photo' = image URL or path under the site root ----
    'team_caption' => 'Students from UDD that made it possible',
    'team' => [
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
    ],

    // ---- About Us ----
    'about_images'   => [],   // up to 4 image URLs/paths; empty slots show a wood placeholder
    'about_subtitle' => 'The Pop Cabin',
    'about_text'     => [
        'The Pop Cabin is a Funko Pop collectibles shop.',
        'Browse the collection, find the Pop you want, and get in touch with us if you have any questions.',
    ],

    // ---- About Us: "Where to Find Us" ----
    'map_embed_url'  => '',   // a Google Maps "Embed a map" URL (src of the iframe)
    'address_lines'  => [],   // e.g. ['Street address', 'City, Province']

    // ---- Contact Us ----
    'contact_intro' => 'Have a question, a request or some feedback? Send us a message using the form below.',
];
