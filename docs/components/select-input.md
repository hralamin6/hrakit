# Advanced Select Input Component (x-select-input)

## Overview
The `x-select-input` component is a beautiful, user-friendly wrapper around MaryUI's `x-choices` component that provides an enhanced select experience with search, multiple selection, and modern UX.

## Features
- ✅ **MaryUI-powered** - Built on top of MaryUI's robust choices component
- ✅ **Multiple selection** with visual chips/tags
- ✅ **Client-side searchable** dropdown with instant filtering
- ✅ **Keyboard navigation** support (Arrow keys, Enter, Escape)
- ✅ **Clear all** functionality
- ✅ **Icons** support for visual clarity
- ✅ **Fully accessible** (ARIA attributes)
- ✅ **Responsive** design
- ✅ **Livewire integration** - Works seamlessly with wire:model

## Basic Usage

### Single Selection
```blade
<x-select-input 
  label="Select Role" 
  wire:model="selectedRole" 
  :options="$roles" 
/>
```

### Multiple Selection with All Features
```blade
<x-select-input 
  label="Roles" 
  wire:model.defer="selectedRoles" 
  :options="$roles" 
  option-value="id"
  option-label="name"
  searchable
  multiple
  clearable
  placeholder="Choose roles..."
  hint="Select one or more roles for this user"
  icon="o-shield-check"
/>
```

## Component Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `label` | string | `null` | Field label displayed above the select |
| `hint` | string | `null` | Helper text displayed below the select |
| `placeholder` | string | Auto | Placeholder text when nothing is selected |
| `icon` | string | `null` | Heroicon name (e.g., `o-shield-check`, `o-user`) |
| `options` | array/Collection | `[]` | Array of options |
| `option-value` | string | `'id'` | Field name to use as option value |
| `option-label` | string | `'name'` | Field name to use as option label |
| `multiple` | boolean | `false` | Enable multiple selection |
| `searchable` | boolean | `true` | Enable search/filter functionality |
| `clearable` | boolean | `false` | Show clear button when items are selected |
| `disabled` | boolean | `false` | Disable the select field |
| `no-result-text` | string | 'No results found' | Message shown when search has no results |

## Options Format

The component accepts flexible option formats:

### Array of Arrays (Recommended)
```php
[
    ['id' => 1, 'name' => 'Admin'],
    ['id' => 2, 'name' => 'Editor'],
    ['id' => 3, 'name' => 'Viewer'],
]
```

### Eloquent Collection
```php
Role::all() // Will use 'id' and 'name' by default
```

### Custom Field Names
```php
[
    ['slug' => 'admin', 'title' => 'Administrator'],
    ['slug' => 'editor', 'title' => 'Content Editor'],
]
```

Then use:
```blade
<x-select-input
    option-value="slug"
    option-label="title"
    :options="$options"
/>
```

## Example Implementation

### Livewire Component (PHP)

```php
use Spatie\Permission\Models\Role;

class User extends Component
{
    public array $selectedRoles = [];

    public function getAllRolesProperty(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Sync roles by IDs
        $roles = Role::whereIn('id', $this->selectedRoles)->pluck('name')->all();
        $user->syncRoles($roles);
    }
}
```

### Blade Template (User Form)

```blade
<x-select-input
  label="Roles" 
  wire:model.defer="selectedRoles" 
  :options="$this->allRoles" 
  option-value="id"
  option-label="name"
  searchable
  multiple
  clearable
  hint="Select one or more roles for this user"
  icon="o-shield-check"
  placeholder="Choose roles..."
  no-result-text="No roles found"
/>
```

## Best Practices

1. **Use `.defer` for better performance**:
   ```blade
   wire:model.defer="selectedRoles"
   ```

2. **Always validate IDs**:
   ```php
   'selectedRoles' => ['array'],
   'selectedRoles.*' => ['integer', 'exists:roles,id'],
   ```

3. **Enable search for large lists**:
   ```blade
   searchable
   ```

4. **Add helpful hints**:
   ```blade
   hint="Select one or more roles"
   ```

5. **Use clearable for better UX**:
   ```blade
   clearable
   ```

6. **Add icons for visual context**:
   ```blade
   icon="o-shield-check"
   ```

## Common Issues & Solutions

### Issue: Options not displaying
**Solution**: Ensure options have the correct field names (`id` and `name` by default)

### Issue: Custom field names not working
**Solution**: Use `option-value` and `option-label` attributes

### Issue: Selected values not persisting
**Solution**: Make sure your Livewire property is typed as `array` for multiple selection

### Issue: Validation errors
**Solution**: Validate both the array and individual items:
```php
'selectedRoles' => ['array'],
'selectedRoles.*' => ['integer', 'exists:roles,id'],
```
