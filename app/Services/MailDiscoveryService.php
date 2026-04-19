<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;

class MailDiscoveryService
{
    /**
     * @return array<string, mixed>|null
     */
    public function discover(string $email): ?array
    {
        $domain = Str::lower(Str::after($email, '@'));
        if ($domain === '') {
            return null;
        }

        $preset = $this->knownPresets($domain);
        if ($preset !== null) {
            return $preset;
        }

        return $this->fetchThunderbirdAutoconfig($domain);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function knownPresets(string $domain): ?array
    {
        $map = [
            'gmail.com' => $this->gmailLike(),
            'googlemail.com' => $this->gmailLike(),
            'outlook.com' => $this->outlookLike(),
            'hotmail.com' => $this->outlookLike(),
            'live.com' => $this->outlookLike(),
            'yahoo.com' => $this->yahooLike(),
            'yahoo.co.uk' => $this->yahooLike(),
        ];

        return $map[$domain] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function gmailLike(): array
    {
        return [
            'imap_host' => 'imap.gmail.com',
            'imap_port' => 993,
            'imap_security' => 'ssl',
            'imap_auth' => 'password',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 465,
            'smtp_security' => 'ssl',
            'smtp_auth' => 'password',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function outlookLike(): array
    {
        return [
            'imap_host' => 'outlook.office365.com',
            'imap_port' => 993,
            'imap_security' => 'ssl',
            'imap_auth' => 'password',
            'smtp_host' => 'smtp.office365.com',
            'smtp_port' => 587,
            'smtp_security' => 'starttls',
            'smtp_auth' => 'password',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function yahooLike(): array
    {
        return [
            'imap_host' => 'imap.mail.yahoo.com',
            'imap_port' => 993,
            'imap_security' => 'ssl',
            'imap_auth' => 'password',
            'smtp_host' => 'smtp.mail.yahoo.com',
            'smtp_port' => 465,
            'smtp_security' => 'ssl',
            'smtp_auth' => 'password',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchThunderbirdAutoconfig(string $domain): ?array
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'LaravelMailClient/1.0'])
                ->get('https://autoconfig.thunderbird.net/v1.1/'.$domain);

            if (! $response->successful()) {
                return null;
            }

            $xml = @simplexml_load_string($response->body());
            if (! $xml instanceof SimpleXMLElement) {
                return null;
            }

            return $this->parseThunderbirdXml($xml);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseThunderbirdXml(SimpleXMLElement $xml): ?array
    {
        $incoming = null;
        foreach ($xml->xpath('//incomingServer') ?: [] as $node) {
            $type = (string) ($node['type'] ?? '');
            if (strtolower($type) === 'imap') {
                $incoming = $node;
                break;
            }
        }

        $outgoing = null;
        foreach ($xml->xpath('//outgoingServer') ?: [] as $node) {
            $type = (string) ($node['type'] ?? '');
            if (strtolower($type) === 'smtp') {
                $outgoing = $node;
                break;
            }
        }

        if (! $incoming || ! $outgoing) {
            return null;
        }

        return [
            'imap_host' => (string) $incoming->hostname,
            'imap_port' => (int) $incoming->port,
            'imap_security' => $this->mapSocketType((string) $incoming->socketType),
            'imap_auth' => $this->mapAuth((string) $incoming->authentication),
            'smtp_host' => (string) $outgoing->hostname,
            'smtp_port' => (int) $outgoing->port,
            'smtp_security' => $this->mapSocketType((string) $outgoing->socketType),
            'smtp_auth' => $this->mapAuth((string) $outgoing->authentication),
        ];
    }

    protected function mapSocketType(string $socketType): string
    {
        return match (strtoupper($socketType)) {
            'SSL', 'SSL/TLS' => 'ssl',
            'STARTTLS' => 'starttls',
            default => 'none',
        };
    }

    protected function mapAuth(string $authentication): string
    {
        $a = strtolower($authentication);

        return str_contains($a, 'oauth') ? 'oauth' : 'password';
    }
}
