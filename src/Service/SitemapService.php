<?php

namespace App\Service;

use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use \DateTime;

class SitemapService  extends BaseService
{
    protected $router;

    public function __construct(RouterInterface $router, ObjectManager $em)
    {
        parent::__construct($em);
        $this->router = $router;
    }

    /**
     * Génère l'ensemble des valeurs des balises <url> du sitemap.
     *
     * @return array Tableau contenant l'ensemble des balise url du sitemap.
     */
    public function getUrls()
    {
        $urls = array();

        $urls[] = $this->get('homepage');

        $sources = $this->em->getRepository('AdminBundle:Source')->findAll();
        foreach ($sources as $source) {
            $urls[] = $this->get('_source', ['id' => $source->getId()]);

            foreach ($source->getSubjects() as $subject) {
                if ($subject->isPublic()) {
                    foreach ($subject->getTopics() as $topic) {
                        foreach ($topic->getQuestions() as $question) {
                            $urls[] = $this->get('_review-question_show', ['id' => $question->getId()], $question->getUpdated());
                        }
                    }
                }
            }
        }

/*        $topics = $this->em->getRepository('AdminBundle:Topic')->findAll();
        foreach ($topics as $topic) {
            $urls[] = array(
                'loc' => $this->router->generate('_review-question_index', array('topic' => $topic->getId()), UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod' => date_format($topic->getUpdated(), "Y-m-d")
            );
        }*/

        return $urls;
    }

    private function get($url = '', $args = [], $when = null) {

        $when = $when ?? new DateTime();

        return array(
            'loc' => $this->router->generate($url, $args, UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => date_format($when, "Y-m-d"),
            'changefreq' => 'monthly'
        );
    }
}