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
    'facebook_url'  => 'https://www.facebook.com/thepopcabin12',
    'tiktok_url'    => 'https://www.tiktok.com/@thepopcabin',
    'instagram_url' => 'https://www.instagram.com/thepopcabin12/',

    // ---- Footer ----
    'trademark_line' => '',   // optional second line under the copyright, e.g. a trademark notice

    // ---- Home: "Meet the team" (4 cards). 'photo' = image URL or path under the site root ----
    'team_caption' => 'Students from UDD SITE that made it possible',
    'team' => [
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
        ['name' => 'Team member', 'role' => 'Role', 'photo' => ''],
    ],

    // ---- About Us ----
    'about_images'   => ['assets\img\about-abalos.jpg','assets\img\about-caburnay.jpg', 'assets\img\about-deguzman.jpg', 'assets\img\about-estrada.jpg'],   // up to 4 image URLs/paths; empty slots show a wood placeholder
    'about_subtitle' => 'The Pop Cabin',
    'about_text'     => [
        'The Pop Cabin is a Funko Pop collectibles shop.',
        'Browse the collection, find the Pop you want, and get in touch with us if you have any questions.',
    ],

    // ---- About Us: "Where to Find Us" ----
    'map_embed_url'  => 'https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1612.290635897228!2d120.26768193017405!3d16.029385505804125!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1794958763279!5m2!1sen!2sph',   
    'address_lines'  => [],   // e.g. ['Street address', 'City, Province']

    // ---- Contact Us ----
    'contact_intro' => 'Have a question, a request or some feedback? Send us a message using the form below.',
];
