<?php
/**
 * Mailer minimalista para envios transaccionales.
 *
 * Soporta dos modos:
 *   1. SMTP via REM_SMTP_HOST / REM_SMTP_PORT / REM_SMTP_USER / REM_SMTP_PASS.
 *   2. Fallback: registrar en log si SMTP no esta configurado.
 *
 * En produccion se debe configurar SMTP para que los usuarios
 * reciban las credenciales por email.
 */

class Mailer
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $from;
    private $fromName;
    private $secure;
    private $enabled;

    public function __construct()
    {
        $this->host     = getenv('REM_SMTP_HOST') ?: '';
        $this->port     = (int) (getenv('REM_SMTP_PORT') ?: 587);
        $this->user     = getenv('REM_SMTP_USER') ?: '';
        $this->pass     = getenv('REM_SMTP_PASS') ?: '';
        $this->from     = getenv('REM_SMTP_FROM') ?: 'no-reply@rem.local';
        $this->fromName = getenv('REM_SMTP_FROM_NAME') ?: 'Sistema de Observaciones REM';
        $this->secure   = getenv('REM_SMTP_SECURE') ?: 'tls';
        $this->enabled  = !empty($this->host) && !empty($this->user);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Envia un correo. Retorna true si fue enviado o registrado en log
     * como fallback. Retorna false solo si falla totalmente.
     */
    public function send($to, $subject, $body, $altBody = '')
    {
        if (!$this->enabled) {
            return $this->logFallback($to, $subject, $body);
        }

        return $this->sendSmtp($to, $subject, $body, $altBody);
    }

    public function sendPasswordReset($to, $username, $newPassword, $resetBy = 'Supervisor')
    {
        $subject = 'Contraseña restablecida - Sistema de Observaciones REM';
        $body = $this->renderPasswordResetBody($username, $newPassword, $resetBy);
        $alt = "Su contraseña ha sido restablecida. Nueva contraseña: {$newPassword}";
        return $this->send($to, $subject, $body, $alt);
    }

    private function renderPasswordResetBody($username, $newPassword, $resetBy)
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Sistema REM';
        $safeUser = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $safePass = htmlspecialchars($newPassword, ENT_QUOTES, 'UTF-8');
        $safeReset = htmlspecialchars($resetBy, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>{$subject}</title></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.5; color: #1f2937;">
<div style="max-width: 560px; margin: 24px auto; padding: 24px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;">
  <h2 style="margin: 0 0 16px 0; color: #0f172a;">{$appName}</h2>
  <p>Hola <strong>{$safeUser}</strong>,</p>
  <p>Tu contraseña ha sido restablecida por <strong>{$safeReset}</strong>.</p>
  <p style="margin: 24px 0; padding: 16px; background: #f1f5f9; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 16px; text-align: center;">
    {$safePass}
  </p>
  <p>Ingresa al sistema con esta contraseña temporal y cámbiala de inmediato desde tu perfil.</p>
  <p style="margin-top: 32px; font-size: 12px; color: #64748b;">
    Si no solicitaste este cambio, contacta al administrador del sistema.
  </p>
</div>
</body>
</html>
HTML;
    }

    private function sendSmtp($to, $subject, $body, $altBody)
    {
        $remote = ($this->secure === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port;
        $errno = 0;
        $errstr = '';
        $timeout = 10;
        $socket = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            error_log("Mailer SMTP connect failed: {$errstr} ({$errno})");
            return $this->logFallback($to, $subject, $body);
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->smtpRead($socket);
            $this->smtpWrite($socket, 'EHLO localhost');
            $this->smtpRead($socket);

            if ($this->secure === 'tls') {
                $this->smtpWrite($socket, 'STARTTLS');
                $this->smtpRead($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpWrite($socket, 'EHLO localhost');
                $this->smtpRead($socket);
            }

            if (!empty($this->user)) {
                $this->smtpWrite($socket, 'AUTH LOGIN');
                $this->smtpRead($socket);
                $this->smtpWrite($socket, base64_encode($this->user));
                $this->smtpRead($socket);
                $this->smtpWrite($socket, base64_encode($this->pass));
                $this->smtpRead($socket);
            }

            $this->smtpWrite($socket, 'MAIL FROM:<' . $this->from . '>');
            $this->smtpRead($socket);
            $this->smtpWrite($socket, 'RCPT TO:<' . $to . '>');
            $this->smtpRead($socket);
            $this->smtpWrite($socket, 'DATA');
            $this->smtpRead($socket);

            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->from . '>',
                'To: ' . $to,
                'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8'),
                'Date: ' . date('r'),
            ];

            $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";
            $this->smtpWrite($socket, $payload);
            $this->smtpRead($socket);
            $this->smtpWrite($socket, 'QUIT');
            @fclose($socket);
            return true;
        } catch (Exception $e) {
            error_log("Mailer SMTP error: " . $e->getMessage());
            @fclose($socket);
            return $this->logFallback($to, $subject, $body);
        }
    }

    private function smtpWrite($socket, $line)
    {
        fwrite($socket, $line . "\r\n");
    }

    private function smtpRead($socket)
    {
        $data = '';
        while ($line = fgets($socket, 1024)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }

    private function logFallback($to, $subject, $body)
    {
        $log = "/var/log/rem/mail.log";
        if (!is_writable(dirname($log))) {
            $log = sys_get_temp_dir() . '/rem-mail.log';
        }
        $entry = sprintf(
            "[%s] TO=%s SUBJECT=%s BODY=%s\n---\n",
            date('c'),
            $to,
            $subject,
            strip_tags($body)
        );
        @file_put_contents($log, $entry, FILE_APPEND);
        return true;
    }

    /**
     * Genera una contraseña aleatoria que cumple la politica del sistema:
     * 12 caracteres, al menos una mayuscula, una minuscula y un numero.
     */
    public static function generateRandomPassword($length = 12)
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $digits = '23456789';
        $special = '!@#$%^&*?';
        $all = $upper . $lower . $digits . $special;

        $password = '';
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }
}
