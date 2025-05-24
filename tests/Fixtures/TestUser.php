<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $table = 'test_users';

    protected $fillable = [
        'name',
        'email',
        'is_active',
        'age',
        'balance',
        'bio',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'age' => 'integer',
        'balance' => 'decimal:2',
        'preferences' => 'array',
    ];

    // Example validation rules (if using a validation package)
    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:test_users,email',
        'age' => 'nullable|integer|min:0|max:150',
        'balance' => 'nullable|numeric|min:0',
    ];
}
