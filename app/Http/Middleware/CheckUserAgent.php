<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserAgent
{
    public function handle($request, Closure $next)
    {
        $userAgent = $request->header('User-Agent');
        $blockedUserAgents = [
            'Baiduspider',
            'Sogou web spider',
            'Sogou inst spider',
            '360Spider',
            'YisouSpider',
            'Sosospider',
            'YoudaoBot',
            'bingbot',
            'proximic',
            'Bytespider',
            'Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)',
            'Mozilla/5.0 (compatible; GrapeshotCrawler/2.0; +http://www.grapeshot.co.uk/crawler.php)',
            'PetalBot',
            'Amazonbot/0.1',
            // Add more user agents if necessary.
            // Block Googlebot
            'Googlebot',
            'Googlebot-Mobile',
            'Googlebot-Image',
            'Googlebot-Video',
            'Mediapartners-Google',
            'AdsBot-Google',
            'GoogleOther',
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.6167.139 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.6167.139 Mobile Safari/537.36 (compatible; GoogleOther)',
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.94 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ];

        foreach ($blockedUserAgents as $blockedAgent) {
            if (stripos($userAgent, $blockedAgent) !== false) {
                return response()->json(['message' => 'Access denied for Chinese bot/scraping tool.'], 403);
            }
        }

        // Check for specific user agents
        $specificUserAgents = [
            'Mozilla/5.0 (compatible; AwarioBot/1.0; +https://awario.com/bots.html)',
            'python-requests/2.25.1',
            'Mozilla/5.0 (compatible; GoogleDocs; apps-spreadsheets; +http://docs.google.com)',
            'axios/0.19.2',
            'Mozilla/5.0 (compatible; Discordbot/2.0; +https://discordapp.com)',
            'Mozilla/5.0 (compatible; Adsbot/3.1)',
            // Add more specific user agents if necessary.
        ];

        if (in_array($userAgent, $specificUserAgents)) {
            return response()->json(['message' => 'Website scraping is not allowed on Heroes Profile. If you have any questions, please contact zemill AT heroesprofile DOT com for any concerns regarding this issue'], 403);
        }

        return $next($request);
    }
}
