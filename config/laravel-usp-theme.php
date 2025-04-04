<?php

// Define the admin submenu first
$admin = [
    [
        'text' => '<i class="fas fa-users"></i> Listar Usuários', // Add List Users link
        'url'  => 'users', // Relative to admin prefix
        'route'=> 'admin.users.index', // Use route name
    ],
    [
        'type' => 'divider', // Optional Divider
    ],
    [
        'text' => '<i class="fas fa-user-plus"></i> Criar Usuário USP',
        'url'  => 'users/create/usp',
        'route'=> 'admin.users.create.usp',
    ],
    [
        'text' => '<i class="fas fa-user-edit"></i> Criar Usuário Manual',
        'url'  => 'users/create/manual',
        'route'=> 'admin.users.create.manual',
    ],
    // Add more admin links here later
];

// Example submenu (keep as is if needed)
$submenu2 = [
    [
        'text' => 'SubItem 1 (Exemplo)',
        'url' => '/subitem1-exemplo',
    ],
    [
        'text' => 'SubItem 2 (Exemplo)',
        'url' => '/subitem2-exemplo',
    ],
];

// Main Menu Configuration
$menu = [
    [
        'text' => '<i class="fas fa-home"></i> Início',
        'url'  => '/',
        'route' => 'welcome',
    ],
    [
        'text' => '<i class="fas fa-tachometer-alt"></i> Painel',
        'url'  => '/dashboard',
        'route' => 'dashboard',
        'can'  => 'user',
    ],
    [
        'text'    => '<i class="fas fa-cogs"></i> Exemplo Dropdown',
        'submenu' => $submenu2,
    ],
    [
        'text'    => '<i class="fas fa-user-shield"></i> Área Administrativa',
        'submenu' => $admin, // Use the $admin array as the submenu
        'can'     => 'admin', // Restrict visibility
        // Remove direct url/route if it's now a dropdown parent
    ],
];

// Right Menu Configuration (remains the same as previous step)
$right_menu = [
    [
        'text'  => '<i class="fas fa-user"></i> Perfil',
        'url'   => '/profile',
        'route' => 'profile.edit',
        'can'   => 'user',
        'align' => 'right',
    ],
    [
        'text'  => '<i class="fas fa-sign-out-alt"></i> Sair',
        'url'   => '#',
        'id'    => 'usp-theme-logout-link',
        'can'   => 'user',
        'align' => 'right',
    ],
];

// Rest of the config remains the same
return [
    'title'          => config('app.name', 'Laravel USP Theme'),
    'skin'           => env('USP_THEME_SKIN', 'uspdev'),
    'session_key'    => 'laravel-usp-theme',
    'app_url'        => config('app.url', '/'),
    'logout_method'  => 'POST',
    'logout_url'     => 'logout',
    'login_url'      => 'login',
    'menu'           => $menu,
    'right_menu'     => $right_menu,
    'mensagensFlash' => true, // Keep enabled for flash messages
    'container'      => 'container-fluid',
];