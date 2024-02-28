<?php

namespace App\Http\Middleware;
use App\Models\IpLogging;

use Closure;

class CheckUserAgent
{
    public function handle($request, Closure $next)
    {
        try{
          $this->logIPAddress($request);
        } catch (Exception $e) {
        }
        
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

    private function logIPAddress($request)
    {
        $ip = $request->ip();
        $page = $request->path();
        $userAgent = $request->header('User-Agent');

        IpLogging::create([
            'ip' => $ip,
            'page' => $page,
            'user_agent' => $userAgent,
        ]);
    }
}
