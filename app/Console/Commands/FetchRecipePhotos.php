<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

/**
 * One-off helper that downloads a freely licensed photo for each of the 18 seeded meals
 * from Wikimedia Commons, so the seeded recipes have pictures.
 *
 * Photos are saved in database/seeders/photos and committed to git, so seeding
 * doesn't need the internet afterwards. CREDITS.md in the same folder lists the
 * photographer and licence for each one (the CC licences require that).
 *
 * Usage:
 *   sail artisan recipes:fetch-photos                         download any that are missing
 *   sail artisan recipes:fetch-photos beef-chilli             re-download one (e.g. after changing its search)
 *   sail artisan recipes:fetch-photos beef-chilli --skip=1    don't like that one? try the next result
 *   sail artisan recipes:fetch-photos beef-chilli --search="chilli rice"   try different search words
 */
class FetchRecipePhotos extends Command
{
    protected $signature = 'recipes:fetch-photos
                            {meal? : Only this meal, e.g. beef-chilli}
                            {--skip=0 : Skip this many search results (to get a different photo)}
                            {--search= : Use your own search words for this meal instead of the built-in ones}';

    protected $description = 'Download freely licensed photos for the seeded recipes from Wikimedia Commons';

    // Wikimedia asks apps to say who they are in the User-Agent
    private const USER_AGENT = 'MacroMeals/1.0 (Swansea University student coursework project)';

    /**
     * The meal titles from RecipeFactory, with what to search for.
     * Some searches are more general than the title because that finds better photos.
     */
    private const SEARCHES = [
        'Chicken rice bowl' => 'teriyaki chicken rice',
        'Protein overnight oats' => 'overnight oats',
        'Beef chilli' => 'chili con carne',
        'Tuna pasta bake' => 'pasta bake',
        'Turkey mince wraps' => 'tortilla wrap',
        'Greek yoghurt bowl' => 'yogurt granola berries',
        'Salmon and sweet potato' => 'baked salmon',
        'Egg white omelette' => 'omelette',
        'Chicken fajita traybake' => 'chicken fajitas',
        'Lentil and chickpea curry' => 'chana masala',
        'Prawn stir fry' => 'shrimp stir fry',
        'Cottage cheese pancakes' => 'pancakes blueberries',
        'Steak and potato bowl' => 'steak potatoes',
        'Tofu peanut noodles' => 'tofu noodles',
        'Chicken pesto pasta' => 'pesto pasta',
        'Cod and chips' => 'fish and chips',
        'Protein smoothie' => 'banana smoothie',
        'Turkey meatballs' => 'spaghetti meatballs',
    ];

    public function handle(): int
    {
        $folder = database_path('seeders/photos');
        File::ensureDirectoryExists($folder);

        $creditsFile = $folder.'/credits.json';
        $credits = File::exists($creditsFile) ? json_decode(File::get($creditsFile), true) : [];

        $only = $this->argument('meal');
        $skip = (int) $this->option('skip');
        $customSearch = $this->option('search');

        if ($customSearch && ! $only) {
            $this->error('--search only works for one meal at a time, e.g. recipes:fetch-photos beef-chilli --search="chilli rice"');

            return self::FAILURE;
        }

        if ($only && ! in_array($only, array_map(fn ($title) => Str::slug($title), array_keys(self::SEARCHES)))) {
            $this->error("No meal called '{$only}'. Use one of the file names in database/seeders/photos, without .jpg");

            return self::FAILURE;
        }

        foreach (self::SEARCHES as $title => $search) {
            $slug = Str::slug($title);
            $search = $customSearch ?: $search;
            $path = "{$folder}/{$slug}.jpg";

            if ($only && $only !== $slug) {
                continue;
            }

            // already got one, and we weren't asked to replace it
            if (! $only && File::exists($path)) {
                $this->line("  already have {$slug}.jpg");

                continue;
            }

            // search Commons for image files and get each one's 1200px version and licence info.
            // If the server has a hiccup (5xx or timeout) try again after 3s, 8s, then 15s
            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(30)
                ->retry([3000, 8000, 15000], when: fn ($exception) => $this->worthRetrying($exception), throw: false)
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'format' => 'json',
                    'generator' => 'search',
                    'gsrsearch' => "filetype:bitmap {$search}",
                    'gsrnamespace' => 6, // the File: namespace
                    'gsrlimit' => 20,
                    'prop' => 'imageinfo',
                    'iiprop' => 'url|size|mime|extmetadata',
                    'iiurlwidth' => 1200,
                ]);

            if ($response->status() === 429) {
                $this->error('Wikimedia says too many requests. Wait a while and run the command again - it carries on where it stopped.');
                break;
            }

            if ($response->failed()) {
                $this->warn("  couldn't search for {$title} (HTTP {$response->status()}), skipping");

                continue;
            }

            // results come back keyed by page id, so put them back in search order
            $results = collect($response->json('query.pages', []))
                ->sortBy('index')
                ->map(fn ($page) => $page['imageinfo'][0] ?? null)
                ->filter(fn ($info) => $info && $this->isLandscapePhoto($info))
                ->values()
                ->slice($skip);

            $saved = false;

            foreach ($results as $info) {
                if ($this->savePhoto($info['thumburl'] ?? $info['url'], $path)) {
                    $credits[$slug] = $this->creditFor($title, $info);

                    $this->info("  saved {$slug}.jpg - \"{$credits[$slug]['photo']}\" by {$credits[$slug]['creator']}");
                    $saved = true;
                    break;
                }
            }

            if (! $saved) {
                $this->warn("  no usable photo found for {$title}");
            }

            Sleep::for(1)->seconds(); // be polite to the free API
        }

        ksort($credits);
        File::put($creditsFile, json_encode($credits, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        File::put($folder.'/CREDITS.md', $this->creditsMarkdown($credits));

        $this->newLine();
        $this->info('Done. Photo credits are in database/seeders/photos/CREDITS.md');
        $this->line('Run "sail artisan migrate:fresh --seed" to put the photos on the recipes.');

        return self::SUCCESS;
    }

    /**
     * Only real photos (JPEG) that are wider than they are tall and big enough to look good.
     */
    private function isLandscapePhoto(array $info): bool
    {
        return ($info['mime'] ?? '') === 'image/jpeg'
            && ($info['width'] ?? 0) >= 800
            && ($info['width'] ?? 0) > ($info['height'] ?? 0);
    }

    /**
     * Pulls the photo name, photographer and licence out of the Commons metadata.
     * The Artist field is HTML (usually a link), so the tags get stripped.
     */
    private function creditFor(string $recipe, array $info): array
    {
        $meta = $info['extmetadata'] ?? [];
        $value = fn (string $key) => trim(html_entity_decode(strip_tags($meta[$key]['value'] ?? '')));

        return [
            'recipe' => $recipe,
            'photo' => $value('ObjectName') ?: Str::of($info['descriptionurl'] ?? 'Untitled')->afterLast('File:')->beforeLast('.')->replace('_', ' ')->toString(),
            'creator' => Str::limit($value('Artist') ?: 'Unknown', 80),
            'licence' => $value('LicenseShortName') ?: 'See source',
            'licence_url' => $value('LicenseUrl'),
            'source' => $info['descriptionurl'] ?? $info['url'],
        ];
    }

    /**
     * Only retry when the server is having a moment (timeouts and 5xx errors),
     * not for things that won't fix themselves, like a 429 rate limit.
     */
    private function worthRetrying(\Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException && $exception->response->serverError();
    }

    /**
     * Downloads one image, checks it really is a decent sized picture,
     * then makes sure it's no more than 1200px wide so the repo doesn't get huge.
     */
    private function savePhoto(string $url, string $path): bool
    {
        if ($url === '') {
            return false;
        }

        $download = Http::withUserAgent(self::USER_AGENT)->timeout(30)->get($url);

        if ($download->failed()) {
            return false;
        }

        // imagecreatefromstring fails if it's not a real image
        $image = @imagecreatefromstring($download->body());

        if (! $image || imagesx($image) < 600) {
            return false;
        }

        if (imagesx($image) > 1200) {
            $image = imagescale($image, 1200);
        }

        return imagejpeg($image, $path, 80);
    }

    /**
     * Builds the CREDITS.md file from the saved credits.
     */
    private function creditsMarkdown(array $credits): string
    {
        $lines = [
            '# Recipe photo credits',
            '',
            'The seeded recipe photos are Creative Commons or public domain images from',
            '[Wikimedia Commons](https://commons.wikimedia.org) (a few earlier ones came through',
            '[Openverse](https://openverse.org)). They were resized to 1200px wide.',
            'Each photo links to its original page.',
            '',
            '| Recipe | Photo | Creator | Licence |',
            '|---|---|---|---|',
        ];

        foreach ($credits as $credit) {
            // pipes would break the markdown table
            $clean = fn ($text) => str_replace('|', '-', (string) $text);
            $licence = $credit['licence_url'] ? "[{$clean($credit['licence'])}]({$credit['licence_url']})" : $clean($credit['licence']);
            $lines[] = "| {$clean($credit['recipe'])} | [{$clean($credit['photo'])}]({$credit['source']}) | {$clean($credit['creator'])} | {$licence} |";
        }

        return implode("\n", $lines)."\n";
    }
}
