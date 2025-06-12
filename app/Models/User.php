<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'role',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'role' => Role::class,
    ];
  }
  
  /**
   * Check if user has the specified role
   *
   * @param Role $role
   * @return bool
   */
  public function hasRole(Role $role): bool
  {
      return $this->role === $role;
  }
  
  /**
   * Get the role label for display
   *
   * @return string
   */
  public function getRoleLabel(): string
  {
      return $this->role->label();
  }
}
