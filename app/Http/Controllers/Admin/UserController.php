<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => ['required', 'confirmed', Password::defaults()],
            'role'      => 'required|in:user,admin,super_admin',
            'is_active' => 'nullable',
        ]);

        // Only super_admin can create super_admin
        if ($validated['role'] === 'super_admin' && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Only super admins can create super admin accounts.');
        }

        // Create user with explicit assignment
        $user            = new User();
        $user->name      = $validated['name'];
        $user->email     = $validated['email'];
        $user->password  = Hash::make($validated['password']);
        $user->role      = $validated['role'];
        $user->is_active = $request->boolean('is_active', true);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => ['nullable', 'confirmed', Password::defaults()],
            'role'      => 'required|in:user,admin,super_admin',
            'is_active' => 'nullable',
        ]);

        // Prevent self-demotion for super_admin
        if ($user->id === auth()->id() && $user->role === 'super_admin' && $validated['role'] !== 'super_admin') {
            return back()->with('error', 'You cannot demote yourself from super admin.');
        }

        // Only super_admin can set super_admin role
        if ($validated['role'] === 'super_admin' && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Only super admins can assign super admin role.');
        }

        // Prevent deactivating own account
        if ($user->id === auth()->id() && ! $request->boolean('is_active')) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Update user with explicit assignment
        $user->name      = $validated['name'];
        $user->email     = $validated['email'];
        $user->role      = $validated['role'];
        $user->is_active = $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deletion of super_admin by non-super_admin
        if ($user->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'Only super admins can delete super admin accounts.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
