<?php

return [
    // Navigasi & Label Umum
    'navigation' => [
        'group' => 'IAM Management',
        'title' => 'Departements',
        'plural' => 'Departements',
        'description' => 'Kelola Departements dalam sistem dengan efisien.',
    ],

    // Kolom/Form Field
    'fields' => [
        'id' => 'ID',
        'unit_name' => 'Nama Departement',
        'description' => 'Deskripsi',
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'users' => 'Pengguna',
        'user_id' => 'Pengguna',
        'position' => 'Jabatan',
    ],

    // Bagian Formulir
    'form' => [
        'unit' => [
            'title' => 'Informasi Departement',
            'description' => 'Isi detail departement dengan benar.',
            'name_placeholder' => 'Masukkan nama departement',
            'description_placeholder' => 'Tambahkan deskripsi singkat tentang departement ini',
            'helper_text' => 'Nama departement harus unik dan maksimal 100 karakter.',
        ],
        'users' => [
            'title' => 'Pengguna dalam Departement',
            'description' => 'Tambahkan pengguna ke departement ini.',
            'search_placeholder' => 'Cari pengguna...',
            'add_button' => 'Tambahkan Pengguna',
            'remove_button' => 'Hapus Pengguna',
        ],
    ],

    'actions' => [
        'attach' => 'Kaitkan Pengguna',
        'add' => 'Tambah Departement',
    ],
];
