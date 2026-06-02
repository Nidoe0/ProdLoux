@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#FFFFFF;">
  <div class="card shadow border-0" style="max-width:700px;width:100%;border-radius:16px;overflow:hidden;">
    <div style="background:#0D6EFD;padding:2rem;text-align:center;">
      <h4 style="color:#FFFFFF; font-weight:700;margin:0;font-size:1.4rem;">
        <i class="bi bi-bag-heart-fill me-2"></i>Tsena Mora
      </h4>
      <p style="color:rgba(255,255,255,.7);margin:.4rem 0 0;font-size:.85rem;">Marketplace Malgache</p>
    </div>

    <div class="card-body p-4">
      <h5 class="fw-bold mb-1  padding:5rem;text-align:center " style="color: #0046af;font-size:2rem ">Connexion</h5>
      <p class="text-muted mb-4" style="font-size:.85rem;">Accès vendeurs et administrateurs</p>

      @if(session('status'))
      <div class="alert alert-success py-2" style="font-size:.85rem;">{{ session('status') }}</div>
      @endif

      @if($errors->any())
      <div class="alert alert-danger py-2" style="font-size:.85rem;">
        {{ $errors->first() }}
      </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" style="font-size:.88rem;">Adresse email</label>
          <input type="email" name="email" value="{{ old('email') }}"
                 class="form-control @error('email') is-invalid @enderror"
                 placeholder="E-mail" required autofocus>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" style="font-size:.88rem;">Mot de passe</label>
          <input type="password" name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 placeholder="Mot de passe" required>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label text-muted" for="remember" style="font-size:.82rem;">Se souvenir de moi</label>
          </div>
          <a href="{{ route('password.request') }}" class="text-primary" style=" font-size:.82rem;">Mot de passe oublié ?</a>
        </div>

      <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
          <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
      </form>
    </div>

    <div class="text-center py-3 border-top" style="background:#F9FBE7;">
      <small class="text-muted">Compte acheteur ? Utilisez l'application mobile.</small>
    </div>
  </div>
</div>
@endsection
