<?php // Name : Rodain Gouzlan Id:

namespace App\Policies;

use App\Exceptions\StoreDeletionBlockedException;
use App\Models\Store;
use App\Models\User;
use App\Services\Access\StoreScopeService;
use App\Services\Store\StoreCrudService;
use Illuminate\Auth\Access\Response;

class StorePolicy
{
    public function __construct(
        private StoreScopeService $scopeService,
        private StoreCrudService $storeCrudService,
    ) {
    }

    public function viewAny(?User $user)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user) || $this->hasPermission($user, 'view_store') || $this->hasPermission($user, 'view_store_details')) {
            return true;
        }

        return Response::deny('You do not have permission to view branches.');
    }

    public function view(?User $user, Store $store)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $this->scopeService->canAccessStore($user, $store)) {
            return Response::deny($this->scopeMessage($user, 'view'));
        }

        if ($this->hasPermission($user, 'view_store') || $this->hasPermission($user, 'view_store_details')) {
            return true;
        }

        return Response::deny('You do not have permission to view branches.');
    }

    public function create(?User $user)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user) || $this->hasPermission($user, 'create_store')) {
            return true;
        }

        return Response::deny('You do not have permission to create branches.');
    }

    public function update(?User $user, Store $store)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $this->hasPermission($user, 'edit_store')) {
            return Response::deny('You do not have permission to edit branches.');
        }

        if (! $this->scopeService->canAccessStore($user, $store)) {
            return Response::deny($this->scopeMessage($user, 'edit'));
        }

        return true;
    }

    public function delete(?User $user, Store $store)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $this->hasPermission($user, 'delete_store')) {
            return Response::deny('You do not have permission to delete branches.');
        }

        if (! $this->scopeService->canAccessStore($user, $store)) {
            return Response::deny($this->scopeMessage($user, 'delete'));
        }

        try {
            $this->storeCrudService->assertDeletable($store);
        } catch (StoreDeletionBlockedException $exception) {
            return Response::deny($exception->getMessage());
        }

        return true;
    }

    public function manageProducts(?User $user, Store $store)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $this->hasPermission($user, 'manage_store_products')) {
            return Response::deny('You do not have permission to manage products for this branch.');
        }

        if (! $this->scopeService->canAccessStore($user, $store)) {
            return Response::deny($this->scopeMessage($user, 'manage products for'));
        }

        return true;
    }

    public function manageStaff(?User $user, Store $store)
    {
        if (! $user) {
            return Response::deny('You must be logged in.');
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        return Response::deny('Only system administrators can manage staff assignments.');
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    private function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    private function isStoreUser(User $user): bool
    {
        return $user->hasRole('store_manager') || $user->hasRole('store_employee');
    }

    private function scopeMessage(User $user, string $action): string
    {
        if ($this->isStoreUser($user)) {
            return "You can only {$action} your assigned store.";
        }

        return "You can only {$action} authorized branches.";
    }
}