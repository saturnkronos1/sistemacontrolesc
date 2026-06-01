<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $foto_perfil_upload = null;
    public bool $removingFoto = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $this->validate([
            'foto_perfil_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle photo upload
        if ($this->foto_perfil_upload) {
            $path = $this->foto_perfil_upload->store('fotos', 'public');
            $user->foto_perfil = $path;
            $this->foto_perfil_upload = null;
        }

        // Handle photo removal
        if ($this->removingFoto) {
            $user->foto_perfil = null;
            $this->removingFoto = false;
        }

        $user->save();

        $this->dispatch('toast', message: __('Profile updated.'), type: 'success');
    }

    public function removeFoto(): void
    {
        $this->removingFoto = true;
        $this->foto_perfil_upload = null;
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    #[Computed]
    public function fotoUrl(): ?string
    {
        $user = Auth::user();
        if (! $user->foto_perfil) {
            return null;
        }

        return Storage::disk('public')->url($user->foto_perfil);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Photo --}}
            <div>
                <flux:heading size="sm">{{ __('Photo') }}</flux:heading>
                <div class="mt-2 flex items-center gap-6">
                    @if ($this->fotoUrl && ! $this->removingFoto)
                        <img src="{{ $this->fotoUrl }}" alt="Foto" class="h-16 w-16 rounded-full object-cover">
                    @else
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700 text-lg font-semibold text-zinc-600 dark:text-zinc-300">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif

                    <div class="flex items-center gap-2">
                        <flux:button tag="label" variant="primary" class="cursor-pointer">
                            {{ __('Upload') }}
                            <flux:input type="file" wire:model="foto_perfil_upload" accept="image/*" class="hidden" />
                        </flux:button>

                        @if ($this->fotoUrl && ! $this->removingFoto)
                            <flux:button variant="subtle" wire:click="removeFoto">
                                {{ __('Remove') }}
                            </flux:button>
                        @endif
                    </div>
                </div>

                @error('foto_perfil_upload')
                    <flux:text class="mt-1 text-red-600">{{ $message }}</flux:text>
                @enderror

                <flux:text class="mt-1 text-xs text-zinc-500">JPG, PNG o WEBP. Máximo 1MB.</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
