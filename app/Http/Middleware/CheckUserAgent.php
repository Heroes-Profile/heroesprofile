<?php

namespace App\Http\Middleware;

use App\Services\WhitelistedIPsService;
use Closure;

class CheckUserAgent
{
    public function handle($request, Closure $next)
    {
        // Check if IP is whitelisted - if so, bypass user agent checks
        // Use shared IP extraction method for consistency
        $ip = \App\Services\WhitelistedIPsService::getClientIp($request);
        if (WhitelistedIPsService::isWhitelisted($ip)) {
            return $next($request);
        }

        $userAgent = $request->header('User-Agent');

        // Allow legitimate Googlebot crawlers (but not Google-Display-Ads-Bot)
        if ($userAgent && stripos($userAgent, 'Googlebot') !== false && stripos($userAgent, 'Google-Display-Ads-Bot') === false) {
            return $next($request);
        }

        $blockedUserAgents = [
            // Chinese search engines and scrapers
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

            // SEO and marketing bots (consolidated keywords)
            'SemrushBot',
            'GrapeshotCrawler',
            'PetalBot',
            'Amazonbot',
            'MetadataScraper',
            'peer39_crawler',
            'IAS Crawler',
            'Palo Alto Networks',
            'admantx',
            'integralads',
            'thetradedesk',
            'carbon-umbrella-bot',
            'Clickagy Intelligence Bot',
            'weborama-fetcher',
            'ExteContextCrawl',
            'Leikibot',

            // AI/LLM bots (consolidated)
            'ChatGPT',
            'ClaudeBot',
            'GPTBot',
            'PerplexityBot',
            'ShapBot',
            'OAI-SearchBot',

            // Advertising bots (consolidated)
            'AudigentAdBot',
            'CriteoBot',
            'AmazonAdBot',
            'MicroAdBot',
            'AdmixerBot',
            'Google-Display-Ads-Bot',

            // Data collection and scraping bots
            'SirdataBot',
            'AdkernelTopicCrawler',
            'FFZBot',
            'BLEXBot',
            'coccocbot-web',
            'emetriqContextualBot',
            'uipbot',
            'AwarioSmartBot',
            'GlancelotBot',
            'keys-so-bot',
            'Xpanse-bot',
            'DataForSeoBot',
            'Qwarrybot',
            'ZumBot',
            'ZoominfoBot',
            'AliyunSecBot',
            'McontextualBot',
            'SynthesiBot',
            'Timpibot',
            'SurdotlyBot',
            'Orbbot',
            'ImagesiftBot',
            'ZoomBot',
            'Konturbot',
            'DVbot',
            'Thinkbot',
            'ABEvalBot',
            'KStandBot',
            'Nlp-Scraping',
            'wpbot',
            '2ip bot',
            'intelx.io_bot',
            'GoParserBot',
            'MixrankBot',
            'ScannerBot',
            'Storebot-Google',
            'SummalyBot',
            'DotBot',
            'LMArenaUnfurlBot',
            'WPCheckBot',
            'LookoutBot',
            'Mattermost-Bot',
            't3versionsBot',
            'Gulper Web Bot',
            'TinEye-bot',
            'Quora-Bot',
            'SeobilityBot',
            'iAskBot',
            'SitesOverPagesBot',
            'CybaaBot',
            'Website-info.net-Robot',
            'NoAICrawler',
            'HaloLeadsBot',
            'HuaweiCrawler',
            'Bot/1.0',
            'ArchiveTeam ArchiveBot',
            'SuperBot',
            'The Lounge IRC Client',
            'Nextcloud Server Crawler',
            'FaviconFetcherBot',
            'Gaisbot',
            'OutageButtonBot',
            'TurnitinBot',
            'VeryHip Bot',
            'Exabot',
            'SixthCoastBot',
            'LinkupBot',
            'NiueBot',
            'Spideraf-Bot',

            // Archive.org bots (consolidated)
            'archive.org_bot',

            // Qwant bots (consolidated)
            'Qwantbot',

            // Security and scanning bots
            'StingrayBot',
            'Richaudience Brandsafety Bot',

            // Social media bots (be careful - some might be legitimate)
            'redditbot',
            'TelegramBot',
            'Twitterbot',
            'Facebot',

            // Other specific bots
            'Brightbot',
            'DuckAssistBot',
            'IbouBot',
            'ContextualBot',
            'HanaleiBot',
            'MojeekBot',
            'SeznamBot',
            'Mail.RU_Bot',
            'YandexUserproxy',
            'YandexImages',
            'YandexAccessibilityBot',
            'YandexNews',
            'YandexMobileBot',
            'serpstatbot',
            'Slackbot-LinkExpanding',
            'Snap URL Preview Service',
            'DuckDuckBot-Https',
            'DuckDuckBot',
            'CCBot',
            'facebookexternalhit',
            'star-finder.de Bot',
            'StartmeBot',

            // Python scraping tools and libraries
            'python-requests',
            'Python/',
            'aiohttp/',
            'urllib',
            'httpx',

            // Scraping frameworks
            'Scrapy/',
            'newspaper/',
            'trafilatura/',
            'colly',

            // Command line tools
            'curl/',
            'curl ',
            'wget/',
            'Wget/',

            // Security testing tools
            'Fuzz Faster U Fool',
            'sqlmap/',

            // SEO scraping tools
            'Screaming Frog SEO Spider',

            // Custom apps
            'HotsPicker',

            // Microsoft Office data extraction
            'Microsoft.Data.Mashup',
            'Microsoft Office Excel',

            // Malformed and placeholder user agents
            '{uagent}',

            // Ad fraud and analytics crawlers
            'Pixalate.com',
            'DnBCrawler-Analytics',
            'Owler',

            // HTTP clients and libraries
            'got (https://github.com/sindresorhus/got)',
            'node-fetch/',
            'ktor-client',
            'GuzzleHttp/',
            'libcurl-agent',
            'gvfs/',
            'udu/',

            // Web scraping and parsing tools
            'LinuxGetparser',
            'WordPress/',
            'YahooMailProxy',
            'LinkWalker',
            'SiteChecker',
            'sfFeedReader',
            'Embarcadero URI Client',
            'CheckMarkNetwork',
            'DefaultLangchainUserAgent',
            'pan.baidu.com',
            'domains-pool-worker',
            'ExperimentalCrawler',
            'MyEducationalCrawler',
            'MyScraper',
            'SuperFastScraper',
            'search-engine-indexer',
            'facebookscraper',
            'URL-Tester',
            'MiniWebCrawler',
            'Observer',
            'site-checker',
            'CSE-Agent',
            'ATTENTION! FREE! NEW BLOCKCHAIN SEARCH ENGINE!',

            // Meta/Facebook crawlers
            'meta-externalads',

            // Web scraping tools and frameworks
            'Jaunt/',
            'Java Browser',
            'Ruby',
            'Go-http-client/',
            'undici',
            'got/',
            'axios/',
            'Apache-HttpClient/',
            'Java/',
            'PostmanRuntime/',

            // Crawlers and bots
            'MaxPointCrawler',
            'crawler_eb_germany',
            'Discourse Forum Onebox',

            // Mobile apps and chat clients
            'chatterino-api-cache',
            'WhatsApp/',
            'com.tinyspeck.chatlyio',

            // Streaming devices
            'Roku Dynamic Menu',
            'The Roku Channel',
            'YouViewHTML',

            // Gaming platforms
            'Valve/Steam HTTP Client',

            // Streaming devices and smart TVs
            'VIZIO',
            'Hulu/',
            'FilmRise/',
            'WatchFreeFlix/',
            '7plus/',
            'TF1/',

            // HTTP clients and libraries
            'fasthttp',
            'RestSharp/',
            'Faraday v',
            'go-resty/',
            'rustls-client',
            'AHC/',
            'node-fetch',
            'curl',
            'node',
            'wise',
            'ALittle Client',
            'Hydra/',
            'VLC/',
            'eMClient/',
            'PSheet',

            // Web scraping and testing tools
            'insomnia/',
            'Wappalyzer',
            'CSP-Analysis-Tool/',
            'Jigsaw/',
            'WebCopier',
            'TactiScout/',
            'URLCategorizer/',
            'UppinaMonitor/',
            'GoSmartValidatorPro/',
            'vcache-crawler/',
            'LinkBloom/',
            'Swisscows Favicons',
            'Iframely/',
            'Links (',
            'ELinks/',
            'Lynx/',
            'Peach/',
            'Twingly Recon-Klondike/',
            'everyfeed-spider/',
            'EmailWolf',
            'P3P Validator',
            'WDG_Validator/',
            'GoFastValidator/',
            'Domain-Verification-Tool/',
            'HubSpot Connect',

            // Mobile apps and chat clients
            'GrokAppAndroid/',
            'WebexTeams',
            'DeepSeek Chat/',
            'Instapaper/',
            'DingTalkBot-LinkService/',
            'website-logo/',
            'Client/',
            'ArcMobile2/',
            'StreamPerf/',
            'URL%20Manager%20Pro/',
            'Najdu (',
            'Bloglines/',
            'OpenAI/',
            'GodotEngine/',

            // Malformed and suspicious user agents
            'Mozila/5.0',
            'Microsoft Windows Network Diagnostics',
            '-k',
            'return navigator.userAgent.replace',
            'acebookexternalhit/',
            '*',
            'doors_user',

            // Old mobile devices (legacy)
            'SonyEricsson',
            'BlackBerry',
            'Nokia',
            'MOT-',
            'DoCoMo/',
            'POLARIS/',
        ];

        foreach ($blockedUserAgents as $blockedAgent) {
            if (stripos($userAgent, $blockedAgent) !== false) {
                return response()->json(['message' => 'Access denied for bot/scraping tool.'], 403);
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
            'Verity/1.1 (https://gumgum.com/verity; verity-support@gumgum.com)',
            'ias-va/3.3 (former https://www.admantx.com + https://integralads.com/about-ias/)',
            'ias-or/3.3 (former https://www.admantx.com + https://integralads.com/about-ias/)',
        ];

        if (in_array($userAgent, $specificUserAgents)) {
            return response()->json(['message' => 'Website scraping is not allowed on Heroes Profile. If you have any questions, please contact zemill AT heroesprofile DOT com for any concerns regarding this issue'], 403);
        }

        return $next($request);
    }
}
