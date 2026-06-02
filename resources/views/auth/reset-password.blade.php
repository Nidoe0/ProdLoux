@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height:85vh; background: linear-gradient(180deg,#f8fafc 0%, #eef2ff 100%);">
  <div class="card shadow-sm" style="max-width:500px; width:100%;">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <h1 class="h3 fw-bold">Réinitialiser le mot de passe</h1>
        <p class="text-muted mb-0">Choisissez un nouveau mot de passe sécurisé.</p>
      </div>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ $email ?? old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Mot de passe</label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Confirmez le mot de passe</label>
          <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary btn-lg">Réinitialiser</button>
        </div>

        <div class="text-center">
          <a href="{{ route('login') }}">Retour à la connexion</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection