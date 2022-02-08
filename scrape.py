from time import sleep
import httpx
import json
import sys
from bs4 import BeautifulSoup
from urllib.parse import urlparse, parse_qs

proxies = [
    None,
    '190.26.201.194:8080',
    '164.70.72.55:3128',
    '52.183.8.192:3128',
    '162.55.84.170:3128',
    '201.184.171.244:999'
]
base_url = "https://asianembed.io"


def fetchContent(url):
    while True:
        for proxy in proxies:
            try:
                return httpx.get(url=url, follow_redirects=False, proxies=proxy).text
            except Exception:
                print('Switching proxy server %s.' % proxy)
                sleep(5)
                continue


def fetchMovies(page):
    content = fetchContent(base_url + '/?page=%s' % page)

    soup = BeautifulSoup(content, 'lxml')

    # list of movies
    movies = soup.find_all('li', class_='video-block')

    print('Found %s movies. #%s' % (len(movies), page))

    if (len(movies) <= 0):
        raise Exception('No movies found for this page #%s.' % page)

    for movie in movies:
        movieLink = base_url + movie.find('a')['href']
        content = fetchContent(movieLink)
        soup = BeautifulSoup(content, 'lxml')
        main = soup.find('div', class_='video-info-left')

        q = parse_qs(
            urlparse(main.find('div', class_='play-video').find('iframe')['src']).query)
        title = q['title'][0]
        cover = q['cover'][0]

        print('Start scraping %s movie.' % title)
        movieDetail = {
            'title': title,
            'cover': cover,
            'description': '',
            'episodes': []
        }

        episodes = main.find_all('li', class_='video-block')

        print('Found %s episodes from %s movie.' %
              (len(episodes), q['title'][0]))

        for episode in episodes:
            episodeLink = base_url + episode.find('a')['href']
            content = fetchContent(episodeLink)
            soup = BeautifulSoup(content, 'lxml')
            main = soup.find('div', class_='video-info-left')
            embed = main.find('div', class_='play-video').find('iframe')
            description = main.find(
                'div', class_='video-details').find('div', class_='post-entry').text.strip()

            q = parse_qs(urlparse(embed['src']).query)

            image = episode.find('div', class_='img').find('img')
            date = episode.find('span', class_='date').text
            slug = episode.find('a')['href'].split('/')[-1]
            name = episode.find('div', class_='name').text.strip()
            print('Start scraping %s.' % name)
            episodeDetail = {
                'id': q['id'][0],
                'embed': embed['src'],
                'typesub': q['typesub'][0],
                'slug': slug,
                'number': slug.split('-')[-1],
                'image': {
                    'alt': image['alt'],
                    'src': image['src']
                },
                'name': name,
                'meta': episode.find('div', class_='meta').text.strip(),
                'created_at': date,
                'updated_at': date
            }

            movieDetail['title'] = q['title'][0]
            movieDetail['cover'] = q['cover'][0]
            movieDetail['description'] = description
            movieDetail['episodes'].append(episodeDetail)

        with open('movies/%s-%s.json' % (title, cover), 'w') as f:
            f.write(json.dumps(movieDetail))
            f.close()
        print('Completed scraping %s movie.' % title)
    else:
        print('Completed scraping page #%s.' % page)


page = int(sys.argv[1]) or 1
endPage = int(sys.argv[2]) or 10

while page <= endPage:
    try:
        fetchMovies(page)
    except Exception:
        break
    page += 1


print("Scraping completed.")
