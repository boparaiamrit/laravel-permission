<?php

namespace Boparaiamrit\Permissions\Models;


use Boparaiamrit\Permissions\Contracts\Role as RoleContract;
use Boparaiamrit\Permissions\Exceptions\RoleDoesNotExist;
use Boparaiamrit\Permissions\Traits\HasPermissions;
use Boparaiamrit\Permissions\Traits\RefreshesPermissionCache;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Query\Builder;

/**
 * @property string name
 * @property string label
 * @property bool   default
 *
 * @method static Model firstOrNew($attributes)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Role extends Model implements RoleContract
{
    use HasPermissions;
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

        $this->setTable(config('permissions.collections.roles'));
    }

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            config('permissions.models.permission'),
            config('permissions.collections.role_has_permissions'),
            'role_ids',
            'permission_ids'
        );
    }

    /**
     * A role may be assigned to various users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            config('auth.model') ?: config('auth.providers.users.model'),
            config('permissions.collections.user_has_roles'),
            'role_ids',
            'user_ids'
        );
    }

    /**
     * Find a role by its name.
     *
     * @param string $name
     *
     * @throws RoleDoesNotExist
     *
     * @return Role
     */
    public static function findByName($name)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $role = static::where('name', $name)->first();

        if (!$role) {
            throw new RoleDoesNotExist();
        }

        return $role;
    }

    /**
     * Find a role by its name.
     *
     * @param string $id
     *
     * @throws RoleDoesNotExist
     *
     * @return Role
     */
    public static function findByID($id)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $role = static::where('_id', $id)->first();

        if (!$role) {
            throw new RoleDoesNotExist();
        }

        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }
}
