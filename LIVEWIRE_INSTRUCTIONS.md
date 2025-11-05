# LaraKit Livewire 4 Instructions

**Last Updated**: November 4, 2025  
**Framework**: Laravel 12 + Livewire 4 + Tailwind 4 + MaryUI

---

## 📋 Table of Contents

1. [Component Structure](#component-structure)
2. [Property Management](#property-management)
3. [Lifecycle Methods](#lifecycle-methods)
4. [State Management](#state-management)
5. [Forms & Validation](#forms--validation)
6. [File Uploads](#file-uploads)
7. [Table Operations](#table-operations)
8. [Modal Patterns](#modal-patterns)
9. [Alpine.js Integration](#alpinejs-integration)
10. [Events & Dispatching](#events--dispatching)
11. [Common Patterns](#common-patterns)

---

## Component Structure

### Basic Template

```php
<?php

namespace App\Livewire\App;

use App\Models\YourModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class YourComponent extends Component
{
    use Toast, WithFileUploads, WithPagination;
    
    // ... properties and methods
    
    public function render()
    {
        return view('livewire.app.your-component', [
            'items' => $this->data,
        ]);
    }
}
```

### Key Traits

- **`Toast`** - For success/error notifications using `$this->alert()` or `$this->success()`
- **`WithFileUploads`** - For handling file uploads with temporary URLs
- **`WithPagination`** - For paginated results with `->paginate()` and `resetPage()`

---

## Property Management

### Table/List Properties

```php
public $selectedRows = [];          // Array of selected row IDs
public $selectPageRows = false;     // Checkbox to select all current page
public $itemPerPage = 10;           // Items per page for pagination
public $orderBy = 'id';             // Current sort field
public $orderDirection = 'asc';     // Sort direction: asc or desc
public $search = '';                // Search query string
public $searchBy = 'title';         // Field to search in
public $itemStatus = null;          // Filter by status (nullable)
```

### Form Properties

Name form properties after model attributes:

```php
// Form fields (match your model)
public $title = '';
public $slug = '';
public $content = 'Sample content';
public $status = 'draft';
public $excerpt = '';
public $tags = '';
public $meta_title = '';
public $meta_description = '';
public $published_at = null;
public $category_id = null;
public $user_id = null;

// Current model being edited
public $post = null;

// File uploads
public $photo = [];
public $image_url = '';
```

### Query String Parameters

```php
protected $queryString = [
    'search' => ['except' => ''],           // Persist search in URL
    'itemStatus' => ['except' => null],     // Persist filter in URL
];
```

---

## Lifecycle Methods

### Mount

Runs once when component initializes:

```php
public function mount()
{
    $this->authorize('posts.view');
    // Initialize data if needed
}
```

### Render

Must return a view with data:

```php
public function render()
{
    $this->authorize('posts.index');
    
    return view('livewire.app.post', [
        'items' => $this->data,
        'categories' => \App\Models\Category::whereNotNull('parent_id')->get(),
    ]);
}
```

---

## State Management

### Computed Properties

Use `#[Computed]` or `getDataProperty()` for computed data:

```php
public function getDataProperty()
{
    return Post::where($this->searchBy, 'like', '%' . $this->search . '%')
        ->orderBy($this->orderBy, $this->orderDirection)
        ->when($this->itemStatus, function ($query) {
            return $query->where('status', $this->itemStatus);
        })
        ->paginate($this->itemPerPage)
        ->withQueryString();
}
```

Access in view as `$this->data` or `$data`.

### Reactive Updates

Automatically update properties with `wire:model`:

```blade
<!-- Debounced search -->
<input wire:model.live.debounce.500ms="search" type="text" />

<!-- Per page selector -->
<input wire:model.live="itemPerPage" type="number" />

<!-- Status filter -->
<button wire:click="$set('itemStatus', 'published')">Published</button>
```

### Reset Pagination on Filter

Always reset pagination when filters change:

```php
public function updatedSearch()
{
    $this->resetPage();
}

public function updatedItemPerPage()
{
    $this->resetPage();
}

public function updatedItemStatus()
{
    $this->resetPage();
}
```

---

## Forms & Validation

### Validation Rules

Define rules as a method (not property):

```php
protected function rules()
{
    return [
        'title' => 'required|string|max:255',
        'slug' => 'required|string|alpha_dash|unique:posts,slug,' . $this->post?->id,
        'content' => 'required|string',
        'status' => 'required|in:draft,published',
        'category_id' => 'required|exists:categories,id',
        'excerpt' => 'nullable|string',
        'tags' => 'nullable|string',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:255',
        'published_at' => 'nullable|date',
        'photo.*' => 'nullable|image|max:2048',
        'image_url' => 'nullable|url',
    ];
}
```

### Save (Create or Update)

```php
public function saveData()
{
    $this->authorize('posts.create');
    
    // Pre-process data
    $this->user_id = Auth::id();
    $this->tags = json_encode(array_map('trim', explode(',', $this->tags)));
    $this->meta_title = $this->meta_title ?: $this->title;
    $this->meta_description = $this->meta_description ?: $this->excerpt;
    
    // Validate
    $data = $this->validate();
    
    // Create or update
    $model = Post::create($data);
    
    // Handle media
    $this->handleMediaUpload($model);
    
    // Notify
    $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
    $this->goToPage($this->getDataProperty()->lastPage());
    $this->alert('success', __('Data saved successfully!'));
    
    // Reset
    $this->resetData();
}
```

### Edit (Load Data)

```php
public function loadData(Post $post)
{
    $this->resetData();
    
    $this->title = $post->title;
    $this->content = $post->content;
    $this->slug = $post->slug;
    $this->status = $post->status;
    $this->excerpt = $post->excerpt;
    $this->tags = implode(',', json_decode($post->tags ?? '[]'));
    $this->meta_title = $post->meta_title;
    $this->meta_description = $post->meta_description;
    $this->published_at = $post->published_at;
    $this->category_id = $post->category_id;
    $this->user_id = $post->user_id;
    
    $this->post = $post;
}

public function editData()
{
    $this->authorize('posts.edit');
    
    // Same pre-processing as saveData
    $this->tags = json_encode(array_map('trim', explode(',', $this->tags)));
    
    $data = $this->validate();
    $this->post->update($data);
    
    $this->handleMediaUpload($this->post);
    
    $this->dispatch('dataAdded', dataId: "item-id-{$this->post->id}");
    $this->alert('success', __('Data updated successfully'));
    $this->resetData();
}
```

### Reset Form

```php
public function resetData()
{
    $this->reset([
        'title', 'image_url', 'photo', 'content', 'slug', 'status',
        'excerpt', 'tags', 'meta_title', 'meta_description',
        'published_at', 'category_id', 'user_id', 'post'
    ]);
    
    // Re-initialize defaults if needed
    $this->status = 'draft';
    $this->content = 'Sample content';
}
```

### Auto-Generate Slug

```php
public function updatedTitle()
{
    $this->slug = \Str::slug($this->title);
}
```

---

## File Uploads

### Handle Both URL and File Uploads

```php
protected function handleMediaUpload($model)
{
    // URL upload (priority)
    if ($this->image_url) {
        $extension = pathinfo(
            parse_url($this->image_url, PHP_URL_PATH),
            PATHINFO_EXTENSION
        );
        
        $media = $model->addMediaFromUrl($this->image_url)
            ->usingFileName($model->id . '.' . $extension)
            ->toMediaCollection('postImages');
        
        // Cleanup if needed
        $path = storage_path('app/public/Post/' . $media->id . '/' . $media->file_name);
        if (file_exists($path)) {
            unlink($path);
        }
    }
    // File upload (fallback)
    elseif ($this->photo) {
        foreach ($this->photo as $file) {
            $media = $model->addMedia($file->getRealPath())
                ->usingFileName($model->id . '.' . $file->extension())
                ->toMediaCollection('postImages');
            
            $path = storage_path('app/public/Post/' . $media->id . '/' . $media->file_name);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
```

### Delete Media

```php
public function deleteMedia(Post $post, $key)
{
    $media = $post->getMedia('postImages');
    $media[$key]->delete();
    $this->alert('success', __('Image was deleted successfully'));
}
```

### Display Upload Progress

In Blade:

```blade
<div class="flex flex-col items-center"
     x-data="{ isUploading: false, progress: 5 }"
     x-on:livewire-upload-start="isUploading = true"
     x-on:livewire-upload-finish="isUploading = false"
     x-on:livewire-upload-error="isUploading = false"
     x-on:livewire-upload-progress="progress = $event.detail.progress">
    
    <div x-show="isUploading" class="w-full mt-4">
        <div class="relative pt-1">
            <div class="flex w-full bg-gray-200 dark:bg-gray-700 rounded-full">
                <div class="bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
                     x-bind:style="'width: ' + progress + '%'"
                     x-text="progress + '%'">
                </div>
            </div>
        </div>
    </div>
    
    <input type="file" wire:model="photo" accept="image/*" multiple />
</div>
```

---

## Table Operations

### Sorting

```php
public function orderByDirection($field)
{
    if ($this->orderBy == $field) {
        $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->orderBy = $field;
        $this->orderDirection = 'asc';
    }
}
```

In Blade (with x-field component):

```blade
<x-field :OB="$orderBy" :OD="$orderDirection" :field="'title'">
    {{ __('Title') }}
</x-field>

<!-- Or manual -->
<th class="cursor-pointer" wire:click="orderByDirection('title')">
    {{ __('Title') }}
</th>
```

### Row Selection

```php
public function updatedSelectPageRows($value)
{
    if ($value) {
        $this->selectedRows = $this->data->pluck('id')->map(function ($id) {
            return (string) $id;
        });
    } else {
        $this->reset('selectedRows', 'selectPageRows');
    }
}
```

In Blade:

```blade
<input x-model="selectPage" type="checkbox" wire:model.live="selectPageRows" />

@foreach($items as $item)
    <input x-model="rows" value="{{ $item->id }}" type="checkbox" 
           wire:model.live="selectedRows" />
@endforeach
```

### Bulk Operations

```php
public function deleteMultiple()
{
    $this->authorize('posts.delete');
    
    Post::whereIn('id', $this->selectedRows)->delete();
    
    $this->selectPageRows = false;
    $this->selectedRows = [];
    $this->alert('success', __('Data deleted successfully'));
}
```

### Single Delete

```php
public function deleteSingle(Post $post)
{
    $this->authorize('posts.delete');
    $post->delete();
    $this->alert('success', __('Data deleted successfully'));
}
```

---

## Modal Patterns

### Alpine.js Modal Management

```php
// In Livewire component
public $isOpen = false;
public $editMode = false;
```

```blade
<div x-data="post()" @class="m-0 md:m-2">
    <!-- Trigger buttons -->
    <button @click="toggleModal">{{ __('Add New') }}</button>
    
    <!-- Modal (Alpine controls) -->
    <div x-show="isOpen" @click.outside="closeModal">
        <form @submit.prevent="editMode ? $wire.editData() : $wire.saveData()">
            <!-- Form fields -->
        </form>
    </div>
</div>

@script
<script>
    Alpine.data('post', () => ({
        isOpen: false,
        editMode: false,
        
        toggleModal() {
            this.isOpen = !this.isOpen;
        },
        
        closeModal() {
            this.isOpen = false;
            this.editMode = false;
            $wire.resetData();
        },
        
        editModal(id) {
            $wire.loadData(id);
            this.isOpen = true;
            this.editMode = true;
        }
    }))
</script>
@endscript
```

---

## Alpine.js Integration

### Data-Driven Modal

```blade
<div x-data="{
    isOpen: false,
    editMode: false,
    openCreate() {
        this.editMode = false;
        $wire.resetForm();
        this.isOpen = true;
    },
    openEdit(id) {
        this.editMode = true;
        $wire.loadData(id);
        this.isOpen = true;
    }
}">
    <button @click="openCreate">{{ __('New') }}</button>
    
    <div x-show="isOpen" @click.outside="isOpen = false">
        <form @submit.prevent="editMode ? $wire.editData() : $wire.saveData()">
            <!-- Fields -->
        </form>
    </div>
</div>
```

### Status Toggle

```blade
<div @click="$wire.changeStatus({{ $item->id }})">
    {{ $item->status }}
</div>
```

### Conditional Display

```blade
<div x-show="!editMode">
    {{ __('Add new post') }}
</div>
<div x-show="editMode">
    {{ __('Edit this post') }}
</div>
```

---

## Events & Dispatching

### Dispatch Events

From Livewire to Alpine:

```php
$this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
$this->dispatch('delete', [
    'title' => __('Are you sure?'),
    'text' => __('This cannot be undone'),
    'icon' => 'error',
    'actionName' => 'deleteSingle',
    'itemId' => $item->id
]);
```

### Listen in Alpine

```javascript
Alpine.data('post', () => ({
    init() {
        $wire.on('dataAdded', (e) => {
            this.isOpen = false;
            this.editMode = false;
            
            $nextTick(() => {
                const element = document.getElementById(e.dataId);
                element.scrollIntoView({ behavior: 'smooth' });
                element.classList.add('animate-pulse');
            });
            
            setTimeout(() => {
                element.classList.remove('animate-pulse');
            }, 5000);
        });
    }
}))
```

### Global Delete Confirmation

```javascript
document.addEventListener('delete', function (event) {
    Swal.fire({
        title: event.detail.title,
        text: event.detail.text,
        icon: event.detail.icon,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
    }).then((result) => {
        if (result.isConfirmed) {
            $wire[event.detail.actionName](event.detail.itemId);
        }
    });
});
```

---

## Common Patterns

### Status Change Action

```php
public function changeStatus(Post $post)
{
    $this->authorize('posts.edit');
    
    $post->status == 'draft'
        ? $post->update(['status' => 'published'])
        : $post->update(['status' => 'draft']);
    
    $this->alert('success', __('Data updated successfully'));
}
```

### PDF Export

```php
public function pdfGenerate()
{
    $this->authorize('posts.edit');
    
    return response()->streamDownload(function () {
        $posts = $this->data;
        $pdf = Pdf::loadView('pdf.words', compact('posts'));
        
        return $pdf->stream('posts.pdf');
    }, 'posts.pdf');
}
```

### Conditional Status Badges

```blade
<div class="inline-flex items-center px-3 py-1 rounded-full gap-x-2 bg-emerald-100/60 dark:bg-gray-800">
    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
    <button type="button"
            wire:click="changeStatus({{ $item->id }})"
            class="cursor-pointer text-sm font-normal {{ $item->status == 'published' ? 'text-emerald-500' : 'text-pink-500' }}">
        {{ $item->status }}
    </button>
</div>
```

### Loading States

```blade
<!-- Show while saving -->
<button wire:loading.attr="disabled" wire:target="saveData,editData">
    {{ __('Save') }}
</button>

<!-- Show during upload -->
<div wire:loading wire:target="photo">
    {{ __('Uploading...') }}
</div>

<!-- Remove on load complete -->
<button wire:loading.remove wire:target="editData,saveData" type="submit">
    {{ __('Submit') }}
</button>
```

---

## Best Practices

### ✅ DO

- Use `wire:model.live.debounce` for search inputs
- Always call `resetPage()` when filters change
- Validate all user input before database operations
- Authorize all sensitive actions
- Reset form state after successful save/delete
- Use meaningful property and method names
- Keep computed properties efficient
- Use `wire:key` in loops
- Return translated strings with `__()` or `@lang()`

### ❌ DON'T

- Use `wire:model` without `.live` for reactive updates
- Hardcode strings without translation
- Skip authorization checks
- Leave form data populated after operations
- Use `env()` directly (use `config()` instead)
- Store model instances in properties (use IDs)
- Forget to call `resetPage()` on filter updates
- Overload computed properties with heavy logic

---

## Testing Livewire Components

```php
use Livewire\Livewire;

it('creates post', function () {
    $user = User::factory()->create();
    
    Livewire::actingAs($user)
        ->test(PostComponent::class)
        ->set('title', 'My Post')
        ->set('slug', 'my-post')
        ->set('content', 'Content here')
        ->set('category_id', 1)
        ->call('saveData')
        ->assertDispatched('dataAdded');
    
    expect(Post::where('title', 'My Post')->exists())->toBeTrue();
});

it('requires authorization to delete', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    Livewire::actingAs($user)
        ->test(PostComponent::class)
        ->call('deleteSingle', $post->id)
        ->assertForbidden();
});
```

---

## Resources

- [Livewire 4 Documentation](https://livewire.laravel.com)
- [Laravel Validation](https://laravel.com/docs/validation)
- [Alpine.js](https://alpinejs.dev)
- [MaryUI Components](https://maryui.cssninja.io)

---

**Last Updated**: November 4, 2025

