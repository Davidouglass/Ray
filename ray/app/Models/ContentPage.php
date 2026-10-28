<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $fillable = ['key', 'title', 'body', 'updated_label', 'updated_by'];

    public static function defaults(): array
    {
        return [
            'terms' => [
                'title'   => 'Terms of Service',
                'updated' => 'October 2026',
                'sections' => [
                    ['heading' => '1. Acceptance', 'body' => [
                        'By accessing or using Veridian you agree to these Terms of Service. If you do not agree, do not use the platform.',
                    ]],
                    ['heading' => '2. Eligibility', 'body' => [
                        'You must be at least 18 years old and legally permitted to use digital-asset services in your jurisdiction.',
                    ]],
                    ['heading' => '3. Use of the Service', 'body' => [
                        'Veridian provides interfaces for discovering tokens, tracking portfolios and routing on-chain trades. You are responsible for your own wallets, private keys and transaction approvals.',
                        'All information shown is provided for informational purposes only and is not financial advice.',
                    ]],
                    ['heading' => '4. Risks', 'body' => [
                        'Digital assets are volatile and you may lose the entire value of your holdings. On-chain transactions are irreversible once confirmed.',
                    ]],
                    ['heading' => '5. Changes', 'body' => [
                        'We may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the revised terms.',
                    ]],
                ],
            ],
            'privacy' => [
                'title'   => 'Privacy Policy',
                'updated' => 'October 2026',
                'sections' => [
                    ['heading' => '1. Overview', 'body' => [
                        'This policy explains what information Veridian collects and how it is used when you use the platform.',
                    ]],
                    ['heading' => '2. Information We Collect', 'body' => [
                        'Account details you provide (such as username and email) and public wallet addresses you choose to connect or track.',
                        'Basic technical data such as browser type and approximate usage needed to operate and secure the service.',
                    ]],
                    ['heading' => '3. How We Use Information', 'body' => [
                        'To provide and improve the platform, personalise your experience, and keep the service secure.',
                        'We do not sell your personal information.',
                    ]],
                    ['heading' => '4. Blockchain Data', 'body' => [
                        'On-chain activity is public by nature. Addresses and transactions you interact with are visible on the relevant public blockchains.',
                    ]],
                    ['heading' => '5. Contact', 'body' => [
                        'For privacy questions, reach out through the community channels listed in the app.',
                    ]],
                ],
            ],
        ];
    }

    public static function defaultFor(string $key): array
    {
        return self::defaults()[$key] ?? ['title' => ucfirst($key), 'updated' => now()->format('F Y'), 'sections' => []];
    }

    public static function defaultHtml(string $key): string
    {
        $html = '';
        foreach (self::defaultFor($key)['sections'] as $section) {
            $html .= '<h2>' . e($section['heading']) . '</h2>' . "\n";
            foreach ($section['body'] as $paragraph) {
                $html .= '<p class="legal-p">' . e($paragraph) . '</p>' . "\n";
            }
        }

        return $html;
    }

    /** Render the stored body (HTML) or the built-in default sections. */
    public static function render(string $key): array
    {
        $page = self::where('key', $key)->first();
        $default = self::defaultFor($key);

        return [
            'page'     => $page,
            'title'    => $page->title ?? $default['title'],
            'updated'  => $page->updated_label ?? $default['updated'],
            'html'     => ($page && filled($page->body)) ? $page->body : null,
            'sections' => $default['sections'],
        ];
    }
}
