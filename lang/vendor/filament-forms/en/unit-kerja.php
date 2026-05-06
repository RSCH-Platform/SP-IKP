<?php

return [
    // Navigation & General Labels
    'navigation' => [
        'group' => 'IAM Management',
        'title' => 'Departements',
        'plural' => 'Departements',
        'description' => 'Manage Departements in the system efficiently.',
    ],

    // Columns/Field Labels
    'fields' => [
        'id' => 'ID',
        'unit_name' => 'Departement Name',
        'description' => 'Description',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'users' => 'Users',
        'user_id' => 'User',
        'position' => 'Position',
    ],

    // Form Sections
    'form' => [
        'unit' => [
            'title' => 'Departement Information',
            'description' => 'Fill in the Departement details correctly.',
            'name_placeholder' => 'Enter Departement name',
            'description_placeholder' => 'Add a short description of this Departement',
            'helper_text' => 'The unit name must be unique and up to 100 characters.',
        ],
        'users' => [
            'title' => 'Users in Departement',
            'description' => 'Add users to this Departement.',
            'search_placeholder' => 'Search users...',
            'add_button' => 'Add User',
            'remove_button' => 'Remove User',
        ],
    ],

    'actions' => [
        'attach' => 'Attach User',
        'add' => 'Add Departement',
    ],
];
