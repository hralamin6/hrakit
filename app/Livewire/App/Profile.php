<?php

namespace App\Livewire\App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Profile')]
#[Layout('layouts.app')]
class Profile extends Component
{
    use Toast;
    use WithFileUploads;

    // Tabs
    #[Url]
    public string $tab = 'general';

    // General
    public string $name = '';
    public string $email = '';

    // Password
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Image
    public $photo; // TemporaryUploadedFile|null
    public string $photo_url = '';

    public ?string $avatarUrl = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->refreshAvatarUrl();
    }

    public function refreshAvatarUrl(): void
    {
        $user = Auth::user();
        $this->avatarUrl = userImage($user);
    }

    public function saveGeneral(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($emailChanged) {
            // Reset verification if email changed
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
            $this->info('Profile updated. Check your inbox to verify the new email.', position: 'toast-bottom');
        } else {
            $this->success('Profile updated.', position: 'toast-bottom');
        }

        $this->refreshAvatarUrl();
    }

    public function savePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $this->password; // cast 'hashed' on model will hash
        $user->save();

        // Clear password fields
        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->success('Password changed successfully.', position: 'toast-bottom');
    }

    public function savePhoto(): void
    {
        $user = Auth::user();

        $this->validate([
            'photo' => ['required', 'image', 'max:10240'], // 10MB
        ]);

        // Save to Spatie Media Library (single file collection)
        $user
            ->addMedia($this->photo->getRealPath())
            ->toMediaCollection('profile');

        // Reset upload state and refresh avatar
        $this->reset('photo');
        $this->refreshAvatarUrl();

        $this->success('Profile image updated.', position: 'toast-bottom');
    }

    public function savePhotoFromUrl(): void
    {
        $this->validate([
            'photo_url' => ['required', 'url', 'starts_with:https://,http://'],
        ]);

        $url = $this->photo_url;

        try {
            $response = Http::timeout(12)->get($url);
        } catch (\Throwable $e) {
            $this->error('Could not fetch image URL.', position: 'toast-bottom');
            return;
        }

        if (! $response->ok()) {
            $this->error('Invalid response from image URL.', position: 'toast-bottom');
            return;
        }

        $contentType = $response->header('Content-Type', '');
        if (! str_starts_with(strtolower($contentType), 'image/')) {
            $this->error('URL must point to an image.', position: 'toast-bottom');
            return;
        }

        $body = $response->body();
        $max = 10 * 1024 * 1024; // 10MB
        if (strlen($body) > $max) {
            $this->error('Image is larger than 10MB.', position: 'toast-bottom');
            return;
        }

        // Determine extension
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];
        $ext = $map[strtolower($contentType)] ?? pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';

        $tmpDir = storage_path('app/media-url');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $tmpFile = $tmpDir . '/' . uniqid('urlimg_', true) . '.' . $ext;

        file_put_contents($tmpFile, $body);

        $user = Auth::user();
        $user->addMedia($tmpFile)->toMediaCollection('profile');

        // Cleanup
        @unlink($tmpFile);

        $this->reset('photo_url');
        $this->refreshAvatarUrl();

        $this->success('Profile image updated from URL.', position: 'toast-bottom');
    }

    public function removePhoto(): void
    {
        $user = Auth::user();
        $user->clearMediaCollection('profile');
        $this->reset(['photo', 'photo_url']);
        $this->refreshAvatarUrl();
        $this->warning('Profile image removed.', position: 'toast-bottom');
    }

    public function render()
    {
        return view('livewire.app.profile');
    }
}
