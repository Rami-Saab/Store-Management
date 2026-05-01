<?php // Name : Rodain Gouzlan Id:

namespace App\Traits;

use App\Models\Store;
use App\Models\User;
use App\Services\Access\StoreScopeService;
use Illuminate\Support\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Gate;

trait StoreAuthorization
{
    protected function isAdmin(?User $user): bool
    {
        return (bool) $user && $user->hasRole('admin');
    }

    protected function isEmployee(?User $user): bool
    {
        return (bool) $user && ($user->hasRole('store_employee') || $user->hasRole('employee'));
    }

    protected function isStoreManager(?User $user): bool
    {
        return (bool) $user && $user->hasRole('store_manager');
    }

    protected function hasStorePermission(?User $user, string $permission): bool
    {
        return (bool) $user && $user->hasPermission($permission);
    }

    protected function requireStorePermission(?User $user, string $permission, string $message): void
    {
        if (! $this->hasStorePermission($user, $permission)) {
            $this->denyStoreModuleAccess($message);
        }
    }

    protected function abortUnlessStoreModuleUser(?User $user): void
    {
        $permissionKeys = array_keys((array) config('store_permissions.permissions', []));
        $hasAnyPermission = $user
            ? $user->roles()
                ->whereHas('permissions', fn ($query) => $query->whereIn('slug', $permissionKeys))
                ->exists()
            : false;

        if ($this->isAdmin($user) || $hasAnyPermission) {
            return;
        }

        $this->denyStoreModuleAccess('You do not have permission to access this page.');
    }

    protected function denyStoreModuleAccess(string $message = 'You do not have permission to access this page.'): void
    {
        if ($this->shouldReturnJson()) {
            throw new HttpResponseException(
                response()->json(['message' => $message], 403)
            );
        }

        abort(403, $message);
    }

    protected function shouldReturnJson(): bool
    {
        $request = request();
        if (! $request) {
            return false;
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return true;
        }

        $path = (string) $request->path();
        if ($path !== '' && str_starts_with($path, 'api/')) {
            return true;
        }

        return false;
    }

    protected function canAccessStore(Store $store, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (bool) Gate::forUser($user)->allows('store-scope', $store);
    }

    protected function allowedStores(?User $user): Collection
    {
        return app(StoreScopeService::class)->allowedStores($user);
    }

    protected function isStoreStaff(User $user): bool
    {
        return $user->hasRole('store_manager')
            || $user->hasRole('store_employee')
            || $user->hasRole('employee');
    }
}