<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostrar pantalla de perfil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualizar nombre/email.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('ok', 'Perfil actualizado correctamente.');
    }

    /**
     * Actualizar la foto de perfil (bolsa de errores 'photo').
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validateWithBag('photo', [
            'avatar' => ['required','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ], [
            'avatar.required' => 'Selecciona una imagen.',
            'avatar.image'    => 'El archivo debe ser una imagen.',
            'avatar.mimes'    => 'Formatos permitidos: JPG, JPEG, PNG o WEBP.',
            'avatar.max'      => 'La imagen no puede superar 2 MB.',
        ]);

        $user = $request->user();

        // borrar anterior si existe
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return Redirect::route('profile.edit')->with('ok', 'Foto actualizada.');
    }

    /**
     * Eliminar cuenta.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
