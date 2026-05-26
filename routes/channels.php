<?php

use Illuminate\Support\Facades\Broadcast; // wajib ditambah baris ini, bro!

Broadcast::channel('document.{id}', function ($user, $id) {
    // Mengembalikan data user agar bisa dibaca sebagai Live Cursor di frontend
    return [
        'id' => $user->id,
        'name' => $user->name,
        'color' => '#' . substr(md5($user->id), 0, 6) // generate warna acak per user
    ];
});