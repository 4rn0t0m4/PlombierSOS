<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 0; background-color: #f4f5f7;">
    <div style="background-color: #1e3a8a; padding: 30px 20px; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Plombier SOS</h1>
    </div>

    <div style="background-color: #ffffff; padding: 30px 25px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Bonjour {{ $name }},</h2>

        <p>Nous avons examiné votre demande de réclamation pour la fiche <strong>{{ $plumberName }}</strong>.</p>

        <p>Malheureusement, votre demande n'a pas pu être approuvée.</p>

        @if($adminNotes)
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 20px 0; font-size: 14px;">
                <strong>Motif :</strong> {{ $adminNotes }}
            </div>
        @endif

        <p>Si vous pensez qu'il s'agit d'une erreur, n'hésitez pas à nous contacter à <a href="mailto:contact@plombier-sos.fr" style="color: #3b82f6;">contact@plombier-sos.fr</a>.</p>
    </div>

    <div style="background-color: #f1f5f9; padding: 20px 25px; text-align: center;">
        <p style="font-size: 12px; color: #94a3b8; margin: 0;">Plombier SOS — Annuaire des plombiers en France</p>
    </div>
</body>
</html>
