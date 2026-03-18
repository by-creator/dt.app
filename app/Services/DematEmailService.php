<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

class DematEmailService
{
    private const DEFAULT_RECIPIENTS = [
        'iosid242@gmail.com',
        'noreplysitedt@gmail.com',
    ];

    private const DEFAULT_DIRECTOR_EMAIL = 'bongoyebamarcdamien@yahoo.fr';

    public function sendValidationEmail(
        string $nom,
        string $prenom,
        string $email,
        ?string $numeroBl,
        ?string $maisonTransit,
        ?UploadedFile $fileBl,
        ?UploadedFile $fileBadShipping,
        ?UploadedFile $fileDeclaration,
    ): void {
        $files = [
            'BL' => $this->fileInfo($fileBl),
            'BAD SHIPPING' => $this->fileInfo($fileBadShipping),
            'DECLARATION' => $this->fileInfo($fileDeclaration),
        ];

        $attachments = array_values(array_filter([$fileBl, $fileBadShipping, $fileDeclaration]));

        $subject = sprintf('[Dakar Terminal] Nouvelle demande de validation - %s %s', $nom, $prenom);
        $html = $this->buildRequestHtml(
            type: 'Demande de validation',
            accentColor: '#1565C0',
            nom: $nom,
            prenom: $prenom,
            email: $email,
            numeroBl: $numeroBl,
            maisonTransit: $maisonTransit,
            fichiers: $files,
        );

        $this->sendToAll($subject, $html, $attachments);
    }

    public function sendRemiseEmail(
        string $nom,
        string $prenom,
        string $email,
        ?string $numeroBl,
        ?string $maisonTransit,
        ?UploadedFile $fileDemandeManuscrite,
        ?UploadedFile $fileBadShipping,
        ?UploadedFile $fileBl,
        ?UploadedFile $fileFacture,
        ?UploadedFile $fileDeclaration,
    ): void {
        $files = [
            'DEMANDE MANUSCRITE' => $this->fileInfo($fileDemandeManuscrite),
            'BAD SHIPPING' => $this->fileInfo($fileBadShipping),
            'BL' => $this->fileInfo($fileBl),
            'FACTURE' => $this->fileInfo($fileFacture),
            'DECLARATION' => $this->fileInfo($fileDeclaration),
        ];

        $attachments = array_values(array_filter([
            $fileDemandeManuscrite,
            $fileBadShipping,
            $fileBl,
            $fileFacture,
            $fileDeclaration,
        ]));

        $subject = sprintf('[Dakar Terminal] Nouvelle demande de remise - %s %s', $nom, $prenom);
        $html = $this->buildRequestHtml(
            type: 'Demande de remise',
            accentColor: '#4B49AC',
            nom: $nom,
            prenom: $prenom,
            email: $email,
            numeroBl: $numeroBl,
            maisonTransit: $maisonTransit,
            fichiers: $files,
        );

        $this->sendToAll($subject, $html, $attachments, [$this->directorEmail()]);
    }

    public function sendValidationApprovedEmail(string $toEmail, ?string $nom, ?string $prenom, string $bl): void
    {
        $subject = '[Dakar Terminal] Votre dossier a ete valide - Ndeg BL '.$bl;
        $html = $this->buildResultHtml(
            approved: true,
            nom: $nom,
            prenom: $prenom,
            bl: $bl,
            motif: null,
            platformUrl: url('/demat'),
            title: 'Dossier valide',
            successMessage: 'Votre dossier a ete valide avec succes.',
        );

        $this->sendToOne($toEmail, $subject, $html);
    }

    public function sendValidationRejectedEmail(string $toEmail, ?string $nom, ?string $prenom, string $bl, ?string $motif): void
    {
        $subject = '[Dakar Terminal] Votre dossier a ete rejete - Ndeg BL '.$bl;
        $html = $this->buildResultHtml(
            approved: false,
            nom: $nom,
            prenom: $prenom,
            bl: $bl,
            motif: $motif,
            platformUrl: url('/demat'),
            title: 'Dossier rejete',
            successMessage: 'Votre dossier a ete rejete.',
        );

        $this->sendToOne($toEmail, $subject, $html);
    }

    public function sendRemiseDirectionNotifEmail(string $directorEmail, ?string $nom, ?string $prenom, string $bl, ?string $maison): void
    {
        $subject = '[Dakar Terminal] Demande de remise en attente de validation - Ndeg BL '.$bl;
        $html = $this->wrapHtml(
            'Demande de remise en attente de validation',
            '#4B49AC',
            implode('', [
                $this->infoRow('Nom', $nom),
                $this->infoRow('Prenom', $prenom),
                $this->infoRow('Ndeg BL', $bl),
                $this->infoRow('Maison de transit', $maison),
                $this->buttonRow(url('/facturation/remises'), 'Voir la demande'),
            ]),
            'Recue le <strong>'.$this->escape($this->nowFormatted()).'</strong>',
        );

        $this->sendToOne($directorEmail, $subject, $html);
    }

    public function sendRemiseValidatedByDirectionEmail(string $toEmail, ?string $nom, ?string $prenom, string $bl, mixed $pourcentage): void
    {
        $pct = $pourcentage !== null ? rtrim(rtrim((string) $pourcentage, '0'), '.').' %' : '-';

        $subject = '[Dakar Terminal] Votre demande de remise a ete approuvee - Ndeg BL '.$bl;
        $html = $this->wrapHtml(
            'Remise approuvee',
            '#28a745',
            implode('', [
                '<tr><td colspan="2" style="padding:14px;text-align:center;font-size:15px;font-weight:700;color:#28a745;background:#28a74515">Votre demande de remise a ete approuvee.</td></tr>',
                $this->infoRow('Nom', $nom),
                $this->infoRow('Prenom', $prenom),
                $this->infoRow('Ndeg BL', $bl),
                $this->infoRow('Taux de remise accorde', '<strong style="color:#4B49AC">'.$this->escape($pct).'</strong>', true),
                $this->buttonRow(url('/demat'), 'Acceder a la plateforme'),
            ]),
            'Traitee le <strong>'.$this->escape($this->nowFormatted()).'</strong>',
        );

        $this->sendToOne($toEmail, $subject, $html);
    }

    public function sendRemiseRejectedEmail(string $toEmail, ?string $nom, ?string $prenom, string $bl, ?string $motif): void
    {
        $subject = '[Dakar Terminal] Votre demande de remise a ete rejetee - Ndeg BL '.$bl;
        $html = $this->buildResultHtml(
            approved: false,
            nom: $nom,
            prenom: $prenom,
            bl: $bl,
            motif: $motif,
            platformUrl: url('/demat'),
            title: 'Demande de remise rejetee',
            successMessage: 'Votre demande de remise a ete rejetee.',
        );

        $this->sendToOne($toEmail, $subject, $html);
    }

    public function directorEmail(): string
    {
        return (string) config('demat.director_email', self::DEFAULT_DIRECTOR_EMAIL);
    }

    private function sendToAll(string $subject, string $html, array $attachments = [], array $additionalRecipients = []): void
    {
        $recipients = array_unique(array_filter(array_merge(
            config('demat.recipients', self::DEFAULT_RECIPIENTS),
            $additionalRecipients,
        )));

        foreach ($recipients as $recipient) {
            $this->sendToOne($recipient, $subject, $html, $attachments);
        }
    }

    private function sendToOne(string $to, string $subject, string $html, array $attachments = []): void
    {
        Mail::send([], [], function ($message) use ($to, $subject, $html, $attachments): void {
            $message->to($to)
                ->subject($subject)
                ->html($html);

            foreach ($attachments as $attachment) {
                if (! $attachment instanceof UploadedFile || ! $attachment->isValid()) {
                    continue;
                }

                $message->attachData(
                    file_get_contents($attachment->getRealPath()),
                    $attachment->getClientOriginalName() ?: 'fichier',
                    ['mime' => $attachment->getMimeType() ?: 'application/octet-stream']
                );
            }
        });
    }

    private function buildRequestHtml(
        string $type,
        string $accentColor,
        ?string $nom,
        ?string $prenom,
        ?string $email,
        ?string $numeroBl,
        ?string $maisonTransit,
        array $fichiers,
    ): string {
        $fileRows = '';

        foreach ($fichiers as $label => $value) {
            $fileRows .= $this->infoRow(
                $label,
                $value ? '<span style="color:#28a745">Fourni ('.$value.')</span>' : '<span style="color:#dc3545">Non fourni</span>',
                true,
            );
        }

        return $this->wrapHtml(
            $type,
            $accentColor,
            implode('', [
                $this->infoRow('Nom', $nom),
                $this->infoRow('Prenom', $prenom),
                $this->infoRow('Email', $email),
                $this->infoRow('Numero de BL', $numeroBl),
                $this->infoRow('Maison de transit', $maisonTransit),
                $fileRows,
            ]),
            'Recue le <strong>'.$this->escape($this->nowFormatted()).'</strong>',
        );
    }

    private function buildResultHtml(
        bool $approved,
        ?string $nom,
        ?string $prenom,
        string $bl,
        ?string $motif,
        string $platformUrl,
        string $title,
        string $successMessage,
    ): string {
        $accentColor = $approved ? '#28a745' : '#dc3545';
        $motifRow = ! $approved && filled($motif)
            ? $this->infoRow('Motif du rejet', '<span style="color:#dc3545">'.$this->escape($motif).'</span>', true)
            : '';

        return $this->wrapHtml(
            $title,
            $accentColor,
            implode('', [
                '<tr><td colspan="2" style="padding:14px;text-align:center;font-size:15px;font-weight:700;color:'.$accentColor.';background:'.$accentColor.'15">'.$successMessage.'</td></tr>',
                $this->infoRow('Nom', $nom),
                $this->infoRow('Prenom', $prenom),
                $this->infoRow('Ndeg BL', $bl),
                $motifRow,
                $this->buttonRow($platformUrl, 'Acceder a la plateforme'),
            ]),
            'Traite le <strong>'.$this->escape($this->nowFormatted()).'</strong>',
        );
    }

    private function wrapHtml(string $title, string $accentColor, string $rows, string $intro): string
    {
        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f4f7ff;font-family:Helvetica,Arial,sans-serif;">'
            .'<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7ff;padding:40px 0"><tr><td align="center">'
            .'<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">'
            .'<tr><td style="padding:24px 32px;text-align:center;border-bottom:3px solid '.$accentColor.'">'
            .'<p style="color:'.$accentColor.';margin:0;font-size:18px;font-weight:700;letter-spacing:.4px">'.$this->escape($title).'</p>'
            .'</td></tr>'
            .'<tr><td style="padding:32px">'
            .'<p style="color:#555;font-size:13px;margin:0 0 20px">'.$intro.'</p>'
            .'<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">'.$rows.'</table>'
            .'</td></tr>'
            .'<tr><td style="background:#f8f9ff;padding:18px 32px;text-align:center;border-top:1px solid #e8e8f0">'
            .'<p style="color:#aaa;font-size:11px;margin:0">&copy; 2026 DakarTerminal - Ce message est genere automatiquement.</p>'
            .'</td></tr></table></td></tr></table></body></html>';
    }

    private function infoRow(string $label, mixed $value, bool $raw = false): string
    {
        if ($value === null || $value === '') {
            $display = '-';
        } elseif ($raw) {
            $display = (string) $value;
        } else {
            $display = $this->escape((string) $value);
        }

        return '<tr style="border-bottom:1px solid #f0f0f0">'
            .'<td style="padding:10px 0;font-size:13px;font-weight:700;color:#444;width:180px;vertical-align:top">'.$this->escape($label).'</td>'
            .'<td style="padding:10px 0;font-size:13px;color:#333;vertical-align:top">'.$display.'</td>'
            .'</tr>';
    }

    private function buttonRow(string $url, string $label): string
    {
        return '<tr><td colspan="2" style="padding:28px 0 8px;text-align:center">'
            .'<a href="'.$this->escape($url).'" style="display:inline-block;background:#4B49AC;color:#fff;text-decoration:none;border-radius:8px;padding:12px 28px;font-size:14px;font-weight:700;letter-spacing:.3px">'.$this->escape($label).'</a>'
            .'</td></tr>';
    }

    private function fileInfo(?UploadedFile $file): ?string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        return ($file->getClientOriginalName() ?: 'fichier').' ('.(int) ceil($file->getSize() / 1024).' Ko)';
    }

    private function nowFormatted(): string
    {
        return now()->format('d/m/Y H:i');
    }

    private function escape(string $value): string
    {
        return e($value);
    }
}
