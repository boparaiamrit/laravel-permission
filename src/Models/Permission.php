<?php

namespace Boparaiamrit\Permissions\Models;


use Boparaiamrit\Permissions\Contracts\Permission as PermissionContract;
use Boparaiamrit\Permissions\Exceptions\PermissionDoesNotExist;
use Boparaiamrit\Permissions\Traits\RefreshesPermissionCache;
use Jenssegers\Mongodb\Eloquent\Model;

class Permission extends Model implements PermissionContract
{
	use RefreshesPermissionCache;
	
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	public $guarded = ['_id'];
	
	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
		
		$this->setTable(config('permissions.collections.permissions'));
	}
	
	/**
	 * A permission can be applied to roles.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(
			config('permissions.models.role'),
			config('permissions.collections.role_has_permissions')
		);
	}
	
	/**
	 * A permission can be applied to users.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users()
	{
		return $this->belongsToMany(
			config('auth.model') ?: config('auth.providers.users.model'),
			config('permissions.collections.user_has_permissions')
		);
	}
	
	/**
	 * Find a permission by its name.
	 *
	 * @param string $name
	 *
	 * @throws PermissionDoesNotExist
	 */
	public static function findByName($name)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$permission = static::where('name', $name)->first();
		
		if (!$permission) {
			throw new PermissionDoesNotExist();
		}
		
		return $permission;
	}
}
