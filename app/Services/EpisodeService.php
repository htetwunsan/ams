<?php

namespace App\Services;

use App\Enums\EpisodeFilter;
use App\Enums\EpisodeFilter as EnumsEpisodeFilter;
use InvalidArgumentException;
use App\Core\Request;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class EpisodeService
{

    public function __construct(
        public Request $request
    ) {
    }

    private function getCrawler(string $url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        if ($result === false) {
            throw new Exception("Cannot fetch contents.");
        }
        return new Crawler($result);
    }

    public function recently(EpisodeFilter $type = EpisodeFilter::SUB): string
    {
        $page = $this->request->getBody()['page'] ?? 1;
        $path = $this->request->path();
        switch ($type) {
            case EpisodeFilter::SUB:
                $url = 'https://asianembed.io';
                $key = 'recently-sub';
                break;
            case EnumsEpisodeFilter::RAW:
                $url = 'https://asianembed.io/recently-added-raw';
                $key = 'recently-raw';
                break;
            case EnumsEpisodeFilter::MOVIE:
                $url = 'https://asianembed.io/movies';
                $key = 'recently-movies';
                break;
            case EnumsEpisodeFilter::KSHOW:
                $url = 'https://asianembed.io/kshow';
                $key = 'recently-kshow';
                break;
            case EnumsEpisodeFilter::POPULAR:
                $url = 'https://asianembed.io/popular';
                $key = 'recently-popular';
                break;
            case EnumsEpisodeFilter::ONGOING_SERIES:
                $url = 'https://asianembed.io/ongoing-series';
                $key = 'recently-ongoing-series';
                break;
        }

        $url = $url . "?page=$page";
        $key = $key . "-$page";

        return cache()->remember($key, function () use ($url, $path) {
            // $content = $this->getContent($url);

            $crawler =  $this->getCrawler($url);

            $episodes = $this->getEpisodes($crawler);

            $paginator = $this->getPaginator($crawler, $episodes, $path);

            return json_encode($paginator);
        }, 180);
    }

    public function search(): string
    {
        $keyword = $this->request->getBody()['keyword'] ?? '';
        $page = $this->request->getBody()['page'] ?? 1;

        $url = "https://asianembed.io/search.html?keyword=$keyword&page=$page";
        $key = "search-$keyword-$page";
        return cache()->remember($key, function () use ($url, $keyword) {
            // $content = $this->getContent($url);

            $crawler =  $this->getCrawler($url);

            $episodes = $this->getEpisodes($crawler);

            $paginator = $this->getPaginator($crawler, $episodes, $this->request->path(), $keyword);

            return json_encode($paginator);
        }, 180);
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function get(): string
    {
        $slug = $this->request->getParameters()['slug'] ?? false;
        if ($slug === false) {
            throw new InvalidArgumentException('Request must have a valid slug.');
        }

        $url = "https://asianembed.io/videos/$slug";
        $key = "detail-$slug";

        return cache()->remember($key, function () use ($url) {
            // $content = $this->getContent($url);

            $crawler =  $this->getCrawler($url);

            $crawler = $crawler->filter('div.video-info-left');

            $episodes = $this->getEpisodes($crawler, true);

            $embed = $crawler->filter('div.play-video')->filter('iframe');
            $url = parse_url($embed->attr('src'));
            parse_str($url['query'], $q);
            $description = trim($crawler->filter('div.video-details')->filter('div.post-entry')->text());

            $episodeDetail = [
                'id' => $q['id'],
                'embed' => $embed->attr('src'),
                'video' => [
                    'title' => $q['title'],
                    'cover' => $q['cover'],
                    'description' => $description,
                ],
                'related_episodes' => $episodes
            ];

            return json_encode($episodeDetail);
        }, 180);
    }

    private function getPaginator(Crawler $crawler, array $episodes, string $path, string $keyword = ""): array
    {
        $previousPage = null;
        $nextPage = null;
        $activePage = null;
        $urls = [];

        $crawler->filter('ul.pagination')->filter('li')->each(function (Crawler $item) use (&$previousPage, &$nextPage, &$activePage, &$urls) {
            $class = $item->attr('class') ?? '';
            $href = $item->filter('a')->attr('href');
            if (str_contains($class, 'previous')) {
                $previousPage = $href;
            } else if (str_contains($class, 'next')) {
                $nextPage = $href;
            } else {
                if (str_contains($class, 'active')) {
                    $activePage = $href;
                }
                $urls[] = $href;
            }
        });

        return [
            'count' => count($episodes),
            'previous_page_url' => is_string($previousPage) ? $path . $previousPage . ($keyword ? "&keyword=$keyword" : "") : $previousPage,
            'next_page_url' => is_string($nextPage) ? $path . $nextPage . ($keyword ? "&keyword=$keyword" : "")  : $nextPage,
            'active_url' => is_string($activePage) ? $path . $activePage . ($keyword ? "&keyword=$keyword" : "")  : $activePage,
            'more_urls' => array_map(fn ($url) => $path . $url . ($keyword ? "&keyword=$keyword" : ""), $urls),
            'data' => $episodes
        ];
    }

    private function getEpisodes(Crawler $crawler, $includeSub = false): array
    {
        return $crawler->filter('li.video-block')->each(function (Crawler $episode) use ($includeSub) {

            $data = $this->getEpisodeNormalData($episode);

            if ($includeSub) {
                try {
                    $sub = trim($episode->filter('div.type')?->text()) == 'SUB';
                } catch (InvalidArgumentException $e) {
                    $sub = false;
                    unset($e);
                } finally {
                    $data['sub'] = $sub;
                }
            }
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
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
