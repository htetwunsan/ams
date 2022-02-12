<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Application;
use Symfony\Component\DomCrawler\Crawler;

$urls = [
    '-s' => ['url' => 'https://asianembed.io', 'tag' => 'sub'],
    '-r' => ['url' => 'https://asianembed.io/recently-added-raw', 'tag' => 'raw'],
    '-m' => ['url' => 'https://asianembed.io/movies', 'tag' => 'movie'],
    '-k' => ['url' => 'https://asianembed.io/kshow', 'tag' => 'show'],
    '-p' => ['url' => 'https://asianembed.io/popular', 'tag' => 'popular'],
    '-o' => ['url' => 'https://asianembed.io/ongoing-series', 'tag' => 'ongoing']
];

class Scraper
{
    private $baseUrl = 'https://asianembed.io';
    // private $postUrl = 'https://ams.htetwunsan.com';
    private $postUrl = 'http://localhost:8001';

    private function getCrawler(string $url): Crawler
    {
        while (true) {
            $content = file_get_contents($url);
            if ($content === false) {
                echo "Cannot fetch content from $url" . PHP_EOL;
                sleep(5);
                continue;
            }
            return new Crawler($content);
        }
    }

    private function getEpisodeSlugs(Crawler $crawler): array
    {
        return $crawler->filter('li.video-block')->each(function (Crawler $episode) {

            $slug = $episode->filter('a')->attr('href');

            return $slug;
        });
    }

    private function getEpisodeDetail(Crawler $crawler): array
    {
        $embed = $crawler->filter('div.play-video')->filter('iframe');
        $embed = $crawler->filter('div.play-video')->filter('iframe');
        $url = parse_url($embed->attr('src'));
        parse_str($url['query'], $q);
        $description = trim($crawler->filter('div.video-details')->filter('div.post-entry')->text());

        return [
            'id' => $q['id'],
            'embed' => $embed->attr('src'),
            'video' => [
                'title' => $q['title'],
                'cover' => $q['cover'],
                'description' => $description,
            ]
        ];
    }

    private function getAllEpisodes(Crawler $crawler): array
    {
        return $crawler->filter('li.video-block')->each(function (Crawler $episode) {
            $data = $this->getEpisodeNormalData($episode);
            $sub = trim($episode->filter('div.type')?->text()) == 'SUB';
            $data['sub'] = $sub;
            return $data;
        });
    }

    private function getEpisodeNormalData(Crawler $episode): array
    {
        $slug = $episode->filter('a')->attr('href');
        $explodedSlug = explode('-', $slug);
        $number = end($explodedSlug);
        $name = trim($episode->filter('div.name')->text());
        $image = $episode->filter('div.img')->filter('img');
        $date = trim($episode->filter('span.date')->text());
        $meta = trim($episode->filter('div.meta')->text());

        return [
            'slug' => $slug,
            'name' => $name,
            'number' => $number,
            'image' => [
                'src' => $image->attr('src'),
                'alt' => $image->attr('alt')
            ],
            'meta' => $meta,
            'date' => $date
        ];
    }

    private function getExistingEpisodes(string $videoCover, string $tag): array
    {
        $postData = http_build_query([
            'video_cover' => $videoCover,
            'tag' => $tag
        ]);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ]);

        while (true) {
            $url = $this->postUrl . '/api/episodes/existing';
            $result = file_get_contents($url, false, $context);
            if ($result === false) {
                echo "Cannot post content to $url" . PHP_EOL;
                sleep(5);
            }
            break;
        }
        return json_decode($result, true);
    }

    private function uploadEpisode(array $data)
    {
        $postData = http_build_query($data);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postData
            ]
        ]);
        while (true) {
            $url = $this->postUrl . '/api/episodes';
            $result = file_get_contents($url, false, $context);
            if ($result === false) {
                echo "Cannot post content to $url" . PHP_EOL;
                sleep(5);
            }
            break;
        }
        $result = json_decode($result, true);
        $resultEpisode = $result['episode'];
        echo "Upserting episode " . $data['name'] . " completed. Result: $resultEpisode" . PHP_EOL;
    }

    public function run(string $tag, string $url)
    {
        for ($page = 1; $page <= 10; ++$page) {
            $newUrl = $url . "?page=$page";

            echo "Start scraping $newUrl with tag $tag." . PHP_EOL;
            // crawling list
            $crawler = $this->getCrawler($newUrl);

            $slugs = $this->getEpisodeSlugs($crawler); // list of slugs from list page

            foreach ($slugs as $slug) {
                // crawling detail
                $crawler = $this->getCrawler($this->baseUrl . $slug)->filter('div.video-info-left');

                $allEpisodes = $this->getAllEpisodes($crawler);
                $allEpisodeCount = count($allEpisodes);

                $videoCover = $this->getEpisodeDetail($crawler)['video']['cover'];

                $existingEpisodeSlugs = array_flip($this->getExistingEpisodes($videoCover, $tag));

                foreach ($allEpisodes as $key => $episode) {
                    if (array_key_exists($episode['slug'], $existingEpisodeSlugs)) {
                        echo "Episode " . $episode['name'] . " is already existed. Skipping..." . PHP_EOL;
                        continue;
                    }
                    // crawling detail
                    $crawler = $this->getCrawler($this->baseUrl . $episode['slug'])->filter('div.video-info-left');

                    $episode = $this->getEpisodeDetail($crawler);
                    $episode['video']['episode_count'] = $allEpisodeCount;

                    $this->uploadEpisode(array_merge($episode, $allEpisodes[$key], ['tag' => $tag]));
                }
            }
        }
    }
}

$app = new Application(__DIR__);

(require base_path() . '/app/bootstrap.php')($app);

/**
 * @var Scraper $scraper
 */
$scraper = $app->get(Scraper::class);

$arg = $urls[$argv[1]];
$url = $arg['url'];
$tag = $arg['tag'];

$scraper->run($tag, $url);
