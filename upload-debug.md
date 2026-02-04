## Step 1: Check the Actual Request in Browser
Open DevTools (F12) → Network tab → Check "Disable cache"
Try uploading, then click on the failed upload-file request. Share:

Request Headers - Look for:

Cookie: (contains session ID)
X-Livewire: header
X-CSRF-TOKEN:


Response Headers - Look for:

WWW-Authenticate: (indicates auth failure)


Response body - Click "Response" tab, share what it says


## Step 2: Test if Regular Livewire Works
Create a simple test component to see if the issue is specific to file uploads:
```bash
docker exec -it intellihatch_app bash

# Create test component
php artisan make:livewire TestUpload

# Edit it
nano app/Livewire/TestUpload.php
```

Add this simple code:

```php
<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithFileUploads;

class TestUpload extends Component
{
    use WithFileUploads;
    public $photo;

    public function save()
    {
        $this->validate(['photo' => 'image|max:1024']);
        $this->photo->store('photos');
        session()->flash('message', 'Photo uploaded!');
    }

    public function render()
    {
        return view('livewire.test-upload');
    }
}
```

Create the view:

```bash
nano resources/views/livewire/test-upload.blade.php
```

```html
<div>
    @if (session()->has('message'))
        <div>{{ session('message') }}</div>
    @endif

    <form wire:submit="save">
        <input type="file" wire:model="photo">
        <button type="submit">Upload</button>
    </form>
</div>
```

Add route in routes/web.php:

```php
Route::get('/test-upload', App\Livewire\TestUpload::class);
```

- Exit and visit: https://intellihatch.bfcgroup.ph/test-upload
- Try uploading there. Does it work?

## Step 3: Check Middleware
The 401 suggests authentication middleware. Check:

```bash
docker exec -it intellihatch_app bash

# Check if Livewire routes are protected
cat bootstrap/app.php | grep -A 20 "middleware"

# Or check the old Kernel if it exists
cat app/Http/Kernel.php | grep -A 30 "'web'"
```

Look for any auth middleware applied globally to the web group.

## Step 4: The Nuclear-er Option
- If all else fails, bypass Livewire file uploads entirely:
- Use a traditional form with direct upload (no Livewire temporary files). Sometimes the juice isn't worth the squeeze.