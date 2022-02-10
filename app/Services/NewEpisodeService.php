<?php

namespace App\Services;

use App\Core\Database\Contracts\QueryBuilderContract;
use App\Enums\EpisodeFilter;
use InvalidArgumentException;
use App\Core\Request;
use App\Services\Contracts\EpisodeContract;
use PDO;

class NewEpisodeService implements EpisodeContract
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
        $total = $this->qb->getPdo()->query("SELECT COUNT(id) FROM episodes WHERE tag = '$tag'")->fetchColumn();

        $paginator = $this->paginator($total);

        $limit = 20;

        $offset = (($this->request->getBody()['page'] ?? 1) - 1) * $limit;
        $paginator['data'] = $this->qb->select('episodes', [], ['tag' => $tag], ['original_date' => 'DESC', 'updated_at' => 'DESC'], $limit, $offset);

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($paginator);
    }

    private function paginator(int $total, int $limit = 20)
    {
        $path = $this->request->path() . "?page=";
        $totalPages = ceil($total / $limit);
        $currentPage = $this->request->getBody()['page'] ?? 1;
        $nextPage = $this->getNextPage($currentPage, $totalPages);
        $previousPage = $this->getPreviousPage(2);
        $moreUrls = $this->getMoreUrls($currentPage, $totalPages);
        return [
            'next_page_url' => $nextPage,
            'previous_page_url' => $previousPage,
            'active_url' => $path . $currentPage,
            'more_urls' => $moreUrls
        ];
    }

    private function getNextPage(int $current, int $totalPages): string|null
    {
        $path = $this->request->path() . "?page=";
        return $current + 1 <= $totalPages ? $path . $current + 1 : null;
    }

    private function getPreviousPage(int $current): string|null
    {
        $path = $this->request->path() . "?page=";
        return $current - 1 > 1 ? $path . $current - 1 : null;
    }

    private function getMoreUrls(int $current, int $totalPages, int $left = 2, int $right = 2): array
    {
        $path = $this->request->path() . "?page=";
        $moreUrls = [];
        for ($i = $current - $left; $i >= 1 && $i < $current; ++$i) {
            $moreUrls[] = $path . $i;
        }
        for ($i = 0; $current + $i <= $totalPages && $i <= $right; ++$i) {
            $moreUrls[] = $path . $current + $i;
        }
        return $moreUrls;
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
}
