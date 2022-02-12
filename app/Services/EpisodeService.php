<?php

namespace App\Services;

use App\Core\Database\Contracts\QueryBuilderContract;
use App\Enums\EpisodeFilter;
use InvalidArgumentException;
use App\Core\Request;
use App\Services\Contracts\EpisodeContract;
use PDO;

class EpisodeService implements EpisodeContract
{

    public function __construct(
        private Request $request,
        private QueryBuilderContract $qb
    ) {
    }

    public function recently(EpisodeFilter $type = EpisodeFilter::SUB): string
    {
        $tag = match ($type) {
            EpisodeFilter::SUB => 'sub',
            EpisodeFilter::RAW => 'raw',
            EpisodeFilter::MOVIE => 'movie',
            EpisodeFilter::KSHOW => 'show',
            EpisodeFilter::POPULAR => 'popular',
            EpisodeFilter::ONGOING_SERIES => 'ongoing'
        };
        $sub = $tag === 'raw' ? false : true;

        $total = $this->qb->getPdo()->query("SELECT COUNT(id) FROM episodes WHERE tag = '$tag' AND sub = '$sub'")->fetchColumn();

        $paginator = $this->paginator($total);

        $limit = 20;

        $offset = (($this->request->getBody()['page'] ?? 1) - 1) * $limit;
        $paginator['data'] = $this->qb->select('episodes', [], ['tag' => $tag, 'sub' => $sub], ['original_date' => 'DESC', 'updated_at' => 'DESC'], $limit, $offset);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($paginator);
    }

    private function paginator(int $total, int $limit = 20)
    {
        $totalPages = ceil($total / $limit);
        $currentPage = $this->request->getBody()['page'] ?? 1;
        $nextPage = $this->getNextPage($currentPage, $totalPages);
        $previousPage = $this->getPreviousPage($currentPage);
        $morePages = $this->getMorePages($currentPage, $totalPages);
        $firstPage =  empty($morePages) || in_array(1, $morePages) ? null : 1;
        $lastPage = empty($morePages) || in_array($totalPages, $morePages) ? null : $totalPages;
        return [
            'next_page_url' => $this->mapPageToPath($nextPage),
            'previous_page_url' => $this->mapPageToPath($previousPage),
            'active_url' => $this->mapPageToPath($currentPage),
            'first_page_url' => $this->mapPageToPath($firstPage),
            'last_page_url' => $this->mapPageToPath($lastPage),
            'more_urls' => array_map(fn ($p) => $this->mapPageToPath($p), $morePages)
        ];
    }

    public function mapPageToPath(int|null $page): string|null
    {
        if (is_null($page)) return null;

        $path = $this->request->path() . "?page=";
        return $path . $page;
    }

    private function getNextPage(int $current, int $totalPages): int|null
    {
        return $current + 1 <= $totalPages ? $current + 1 : null;
    }

    private function getPreviousPage(int $current): int|null
    {
        return $current - 1 >= 1 ? $current - 1 : null;
    }

    private function getMorePages(int $current, int $totalPages, int $left = 2, int $right = 2): array
    {
        $morePages = [];
        for ($i = max($current - $left, 1); $i < $current; ++$i) {
            $morePages[] = $i;
        }
        for ($i = 0; $current + $i <= $totalPages && $i <= $right; ++$i) {
            $morePages[] = $current + $i;
        }
        return $morePages;
    }

    public function random(): string
    {
        $results = $this->qb->getPdo()->query("SELECT * from episodes ORDER BY RAND() LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($results);
    }

    public function search(): string
    {
        $keyword = $this->request->getBody()['keyword'] ?? '';
        $page = $this->request->getBody()['page'] ?? 1;

        $q = "%$keyword%";

        $results = cache()->remember($keyword, function () use ($q) {
            $query = "SELECT DISTINCT * FROM episodes 
            WHERE (name LIKE '$q' OR video_title LIKE '$q' OR video_description LIKE '$q')";
            $results = $this->qb->getPdo()->query($query)->fetchAll(PDO::FETCH_ASSOC);
            $uniqueResults = [];
            foreach ($results as $result) {
                $uniqueResults[$result['video_cover']] = $result;
            }
            return json_encode(array_values($uniqueResults));
        }, 300);

        $results = json_decode($results);

        $limit = 20;
        $offset = ($page - 1) * $limit;

        $paginator = $this->paginator(count($results));

        $paginator['data'] = array_slice($results, $offset, $limit);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($paginator);
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function get(): string
    {
        $tag = $this->request->getParameters()['tag'] ?? 'sub';
        $slug = $this->request->getParameters()['slug'] ?? false;

        $slug = '/' . $tag . '/' . $slug;

        if ($slug === false) {
            http_response_code(404);
            return '404 Not Found';
        }

        $result = $this->qb->select('episodes', [], ['tag' => $tag, 'slug' => $slug]);

        if (!$result) {
            http_response_code(404);
            return '404 Not Found';
        }

        $result['related_episodes'] = $this->qb->select('episodes', [], ['tag' => $tag, 'video_cover' => $result['video_cover']], [], 2000);


        header('Content-Type: application/json; charset=utf-8');
        return json_encode($result);
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        $data['slug'] = $this->normaliseSlug($data['slug'], $data['tag']); // eg. /videos/abc-def-episode-1 to /sub/abc-def-episode-1
        $resultEpisode = $this->qb->upsert('episodes', [
            'slug' => $data['slug'],
            'tag' => $data['tag'],
            'video_cover' => $data['video']['cover'],
            'video_title' => $data['video']['title'],
            'video_description' => $data['video']['description'],
            'video_episode_count' => $data['video']['episode_count'],
            'original_id' => $data['id'],
            'embed' => $data['embed'],
            'name' => $data['name'],
            'number' => $data['number'],
            'image_src' => $data['image']['src'],
            'image_alt' => $data['image']['alt'],
            'sub' => $data['sub'],
            'original_date' => date('Y-m-d H:i:s', strtotime($data['date']))
        ], true);

        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        return json_encode(['episode' => $resultEpisode]);
    }

    public function getExistingEpisodes(): string
    {
        $data = $this->request->getBody();
        $videoCover = $data['video_cover'];
        $tag = $data['tag'];

        $results = $this->qb->select('episodes', ['slug'], ['video_cover' => $videoCover, 'tag' => $tag], [], 0);

        $results = array_map(fn ($result) => $result['slug'] = $this->reverseNormaliseSlug($result['slug'], $tag), $results);

        return json_encode($results);
    }

    private function normaliseSlug(string $slug, string $tag): string
    {
        return str_replace('videos', $tag, $slug);
    }

    private function reverseNormaliseSlug(string $slug, string $tag): string
    {
        return str_replace($tag, 'videos', $slug);
    }
}
