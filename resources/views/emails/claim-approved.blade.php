<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 0; background-color: #f4f5f7;">
    <div style="background-color: #1e3a8a; padding: 30px 20px; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Plombier SOS</h1>
        <p style="color: #93c5fd; margin: 5px 0 0; font-size: 14px;">Espace Professionnel</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px 25px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Bienvenue {{ $name }} !</h2>

        <p>Bonne nouvelle ! Votre demande de réclamation pour la fiche <strong>{{ $plumberName }}</strong> a été <span style="color: #16a34a; font-weight: bold;">approuvée</span>.</p>

        @if($adminNotes)
            <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 12px 16px; margin: 20px 0; font-size: 14px;">
                <strong>Note de l'équipe :</strong> {{ $adminNotes }}
            </div>
        @endif

        <p>Vous pouvez dès maintenant accéder à votre espace professionnel pour mettre à jour vos informations : coordonnées, horaires, description, services proposés, etc.</p>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 25px 0;">
            <p style="margin: 0 0 5px; font-size: 14px; color: #64748b;">Votre identifiant de connexion :</p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">{{ $email }}</p>
        </div>

        <p>Cliquez sur le bouton ci-dessous pour définir votre mot de passe et accéder à votre espace :</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #1e3a8a; color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 8px; font-weight: bold; font-size: 16px;">Créer mon mot de passe</a>
        </p>

        <p style="font-size: 13px; color: #888; text-align: center;">Ce lien est valable 60 minutes.</p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">

        <h3 style="color: #1e3a8a; font-size: 16px;">Que pouvez-vous faire depuis votre espace ?</h3>
        <ul style="color: #475569; font-size: 14px; padding-left: 20px;">
            <li style="margin-bottom: 8px;">Modifier vos coordonnées (téléphone, email, site web)</li>
            <li style="margin-bottom: 8px;">Mettre à jour vos horaires d'ouverture</li>
            <li style="margin-bottom: 8px;">Rédiger votre description</li>
            <li style="margin-bottom: 8px;">Indiquer vos spécialités et services</li>
            <li style="margin-bottom: 8px;">Activer/désactiver le service d'urgence 24h</li>
        </ul>
    </div>

    <div style="background-color: #f1f5f9; padding: 20px 25px; text-align: center;">
        <p style="font-size: 12px; color: #94a3b8; margin: 0;">Plombier SOS — Annuaire des plombiers en France</p>
        <p style="font-size: 12px; color: #94a3b8; margin: 5px 0 0;">
            <a href="{{ url('/') }}" style="color: #3b82f6; text-decoration: none;">www.plombier-sos.fr</a>
        </p>
    </div>
</body>
</html>
