@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height:85vh; background: linear-gradient(180deg,#f8fafc 0%, #eef2ff 100%);">
  <div class="card shadow-sm" style="max-width:500px; width:100%;">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <h1 class="h3 fw-bold text-primary">Récupération de mot de passe</h1>
        <p class="text-muted mb-0">Entrez votre email enregistré pour recevoir un lien de réinitialisation.</p>
      </div>

      @if(session('status'))
      <div class="alert alert-success">Nous avons envoyé le lien de réinitialisation à votre adresse email si elle est enregistrée.</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary btn-lg">Envoyer le lien</button>
        </div>

        <div class="text-center">
          <a href="{{ route('login') }}">Retour à la connexion</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection