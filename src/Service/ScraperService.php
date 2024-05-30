<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ScraperService
{
    public function scrapeArticles(array $urls): array
    {
        $httpClient = HttpClient::create();
        $articlesData = [];

        foreach ($urls as $url) {
            $response = $httpClient->request('GET', $url);
            $content = $response->getContent();

            $crawler = new Crawler($content);

            $articles = $crawler->filter('#main-content .post');

            foreach ($articles as $article) {
                $articleCrawler = new Crawler($article);

                $titleElement = $articleCrawler->filter('h1.entry-title a');
                $contentElement = $articleCrawler->filter('.entry-content');
                $imageElement = $articleCrawler->filter('img.attachment-post-thumbnail');

                if ($titleElement->count() > 0 && $contentElement->count() > 0 && $imageElement->count() > 0) {
                    $title = $titleElement->text();
                    $content = $contentElement->text();
                    $image = $imageElement->attr('src');

                    $articlesData[] = [
                        'title' => $title,
                        'content' => $content,
                        'image' => $image,
                    ];
                }
            }
        }

        return $articlesData;
    }
}
