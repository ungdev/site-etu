<?php

namespace Etu\Module\NewsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Etu\Module\NewsBundle\Entity\Article;

/**
 * @Route("/news")
 */
class MainController extends Controller
{
  /**
   * @Route("", name="news_index")
   * @Template()
   */
  public function indexAction()
  {
      $this->denyAccessUnlessGranted('ROLE_NEWS_READ');
      /** @var $em EntityManager */
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder()
          ->select('a, o')
          ->from('EtuModuleNewsBundle:Article', 'a')
          ->leftJoin('a.orga', 'o')
          ->where('a.publishedAt is not NULL')
          ->andWhere('a.validatedAt is not NULL');
      /** @var $articles Article[] */
      $articles = $query
          ->orderBy('a.createdAt', 'DESC')
          ->getQuery()
          ->getResult();
      return ['articles' => $articles];
  }

  /**
   * @Route("/moderate", name="news_moderate")
   * @Template()
   */
  public function moderateAction()
  {
      $this->denyAccessUnlessGranted('ROLE_NEWS_ADMIN');
      /** @var $em EntityManager */
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder()
          ->select('a, o')
          ->from('EtuModuleNewsBundle:Article', 'a')
          ->leftJoin('a.orga', 'o')
          ->where('a.validatedAt is NULL')
          ->andWhere('a.publishedAt is not NULL');
      /** @var $articles Article[] */
      $articles = $query
          ->orderBy('a.createdAt', 'DESC')
          ->getQuery()
          ->getResult();
      return ['articles' => $articles];
  }
    /**
     * @Route("/articles/{id}", name="news_view")
     * @Template()
     * 
     * @param mixed $id
     */
    public function viewAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_NEWS_READ');


        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('a, au, o')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->leftJoin('a.author', 'au')
            ->leftJoin('a.orga', 'o')
            ->where('a.id = :id')
            ->setParameter('id', $id);

        /** @var $article Article */
        $article = $query
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if(!$article){
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'news.main.article.notFound',
            ]);
            return $this->redirect($this->generateUrl('news_index'));
        }
        /** @var $memberships Member[] */
        $memberships = $em->createQueryBuilder()
            ->select('m, o')
            ->from('EtuUserBundle:Member', 'm')
            ->leftJoin('m.organization', 'o')
            ->andWhere('m.user = :user')
            ->setParameter('user', $this->getUser()->getId())
            ->orderBy('m.role', 'DESC')
            ->addOrderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();

        $membership = null;

        foreach ($memberships as $m) {
            if ($m->getOrganization()->getLogin() == $article->getOrga()->getLogin()) {
                $membership = $m;
                break;
            }
        }
        $canEdit = false;
        if($this->isGranted('ROLE_NEWS_EDIT') && $membership != null) {
            $canEdit = $membership->hasPermission('news');
        }
        if(!$this->isGranted('ROLE_NEWS_ADMIN') && !$canEdit && $article->getValidatedAt() == null) {
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'news.main.article.notFound',
            ]);
            return $this->redirect($this->generateUrl('news_index'));
        }
        return [
          'article' => $article,
          'canEdit' => $canEdit,
        ];
    }


    /**
     * @Route(
     *      "/article/{id}/validate",
     *      name="news_validate"
     * )
     * @Template()
     *
     * @param mixed $id
     */
    public function validateAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_NEWS_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }
        $article->setValidatedAt(new \DateTime());
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'news.main.article.confirmValidate',
        ]);

        $em->persist($article);
        $em->flush();

        return $this->redirect($this->generateUrl('news_view', ['id' => $id]));
    }

    /**
     * @Route(
     *      "/article/{id}/unvalidate",
     *      name="news_unvalidate"
     * )
     * @Template()
     *
     * @param mixed $id
     */
    public function unvalidateAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_NEWS_ADMIN');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }
        $article->setValidatedAt(null);
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'warning',
            'message' => 'news.main.article.confirmUnvalidate',
        ]);

        $em->persist($article);
        $em->flush();

        return $this->redirect($this->generateUrl('news_view', ['id' => $id]));
    }
}
