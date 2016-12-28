<?php

namespace Boparaiamrit\Permissions\Traits;


use Boparaiamrit\Permissions\Contracts\Permission;
use Jenssegers\Mongodb\Relations\BelongsToMany;

trait HasPermissions
{
	/**
	 * Grant the given permission(s) to a role.
	 *
	 * @param string|array|Permission|\Illuminate\Support\Collection $permissions
	 *
	 * @return HasPermissions
	 */
	public function givePermissionTo(...$permissions)
	{
		$permissions = collect($permissions)
			->flatten()
			->map(function ($permission) {
				return $this->getStoredPermission($permission);
			})
			->all();
		
		
		/** @var BelongsToMany $PermissionBTM */
		$PermissionBTM = $this->permissions();
		$PermissionBTM->saveMany($permissions);
		
		$this->forgetCachedPermissions();
		
		return $this;
	}
	
	/**
	 * Remove all current permissions and set the given ones.
	 *
	 * @param array ...$permissions
	 *
	 * @return $this
	 */
	public function syncPermissions(...$permissions)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->permissions()->detach();
		
		return $this->givePermissionTo($permissions);
	}
	
	/**
	 * Revoke the given permission.
	 *
	 * @param $permission
	 *
	 * @return HasPermissions
	 */
	public function revokePermissionTo($permission)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->permissions()->detach($this->getStoredPermission($permission));
		
		$this->forgetCachedPermissions();
		
		return $this;
	}
	
	/**
	 * @param string|array|Permission|\Illuminate\Support\Collection $permissions
	 *
	 * @return Permission
	 */
	protected function getStoredPermission($permissions)
	{
		if (is_string($permissions)) {
			return app(Permission::class)->findByName($permissions);
		}
		
		if (is_array($permissions)) {
			return app(Permission::class)->whereIn('name', $permissions)->get();
		}
		
		return $permissions;
	}
}
