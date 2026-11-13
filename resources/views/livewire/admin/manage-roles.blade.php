<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/*
 * Admins change people's roles (member, coach or admin).
 * Uses the assign-roles gate from AppServiceProvider. role isn't fillable on
 * User (so nobody can sneak role=admin into a form), so it's set directly here.
 */
new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('assign-roles');
    }

    // go back to page 1 when the search changes
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate(10);
    }

    public function changeRole(int $userId, string $role): void
    {
        Gate::authorize('assign-roles');

        validator(['role' => $role], ['role' => 'required|in:member,coach,admin'])->validate();

        // stops an admin accidentally locking themselves out of this page
        if ($userId === Auth::id()) {
            $this->addError('role', "You can't change your own role.");

            return;
        }

        $user = User::findOrFail($userId);
        $user->role = $role;
        $user->save();

        $this->status = "{$user->name} is now a {$role}.";
    }
}; ?>

<section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" aria-labelledby="roles-heading">
    <h2 id="roles-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Roles</h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Members manage their own posts. Coaches can also edit or delete anyone's comments and hide recipes.
        Admins can do everything, including this page.
    </p>

    <div class="mt-4 max-w-sm">
        <x-input-label for="user-search" value="Search by name or email" />
        <x-text-input id="user-search" type="search" wire:model.live.debounce.400ms="search" class="mt-1 block w-full" />
    </div>

    {{-- confirms the change for everyone, including screen reader users --}}
    <p role="status" class="mt-3 text-sm font-medium text-green-700 dark:text-green-400">{{ $status }}</p>
    <x-input-error :messages="$errors->get('role')" class="mt-2" />

    <div class="mt-2 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <caption class="sr-only">Users and their roles</caption>
            <thead class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="py-2 pe-4 font-medium">Name</th>
                    <th scope="col" class="py-2 pe-4 font-medium">Email</th>
                    <th scope="col" class="py-2 font-medium">Role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="py-2 pe-4"><x-user-link :user="$user" /></td>
                        <td class="py-2 pe-4 text-gray-700 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="py-2">
                            @if ($user->id === auth()->id())
                                <span class="text-gray-700 dark:text-gray-300">{{ ucfirst($user->role) }} (you)</span>
                            @else
                                <label for="role-{{ $user->id }}" class="sr-only">Role for {{ $user->name }}</label>
                                <select id="role-{{ $user->id }}" wire:change="changeRole({{ $user->id }}, $event.target.value)"
                                    class="rounded-md border-gray-300 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    @foreach (['member', 'coach', 'admin'] as $role)
                                        <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->users->links() }}
    </div>
</section>
