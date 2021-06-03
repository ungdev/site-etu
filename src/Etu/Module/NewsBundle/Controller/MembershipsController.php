<?php

namespace Etu\Module\NewsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\NewsBundle\Entity\Article;
use Etu\Core\CoreBundle\Form\EditorEmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MembershipsController extends Controller
{
   
    /**
     * @Route(
     *      "/user/membership/{login}/news/{id}",
     *      defaults={"id" = "0"},
     *      name="memberships_orga_news"
     * )
     * @Template()
     *
     * @param mixed $login
     * @param mixed $id
     */
    public function newsAction($login, $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_NEWS_EDIT');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

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
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('news')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        /** @var $unpublishedArticles Article[] */
        $unpublishedArticles = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->where('a.validatedAt is NULL')
            ->andWhere('a.id != :id')
            ->andWhere('a.orga = :orga')
            ->setParameter('id', $id)
            ->setParameter('orga', $orga->getId())
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
            ->getResult();

        $available = [];
        $future = [];
        if($id != '0') {
            $future['new'] = 'new';
        }
        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->andWhere('a.orga = :orga')
            ->setParameter('orga', $orga->getId())
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $day = new \DateTime();

        foreach ($unpublishedArticles as $unpublishedArticle) {
            $available[$unpublishedArticle->getId()] = ['id' => $unpublishedArticle->getId(), 'name' => $unpublishedArticle->getTitle()];
        }

        $available = array_merge($future, $available);

        if (!$article) {
            $article = new Article($orga, $this->getUser());
        }

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class, ['required' => true, 'label' => 'news.main.article.labels.title', 'attr' => ['maxlength' => 100]])
            ->add('body', EditorEmailType::class, ['required' => true, 'label' => 'news.main.article.labels.body'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            if ($request->request->has('_publish')) {
                $article->setPublishedAt(new \DateTime());
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'success',
                    'message' => 'news.main.article.confirmPublish',
                ]);
            }
            elseif ($request->request->has('_unpublish')) {
                $article->setPublishedAt(null);
                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'warning',
                    'message' => 'news.main.article.confirmUnpublish',
                ]);
            }
            else {
                if($article->getPublishedAt() != null) {
                    $this->get('session')->getFlashBag()->set('message', [
                        'type' => 'success',
                        'message' => 'news.main.article.confirm',
                    ]);
                } else {
                  $this->get('session')->getFlashBag()->set('message', [
                      'type' => 'warning',
                      'message' => 'news.main.article.confirmWarning',
                  ]);
                }
            }
            $em->persist($article);
            $em->flush();

            if ($request->request->has('_saveandsee') || $request->request->has('_publish')) {
                return $this->redirect($this->generateUrl('news_view', [
                    'id' => $article->getId(),
                ]));
            }

            return $this->redirect($this->generateUrl('memberships_orga_news', [
                    'login' => $login,
                    'id' => $article->getId(),
                ]));
        }
        return [
            'memberships' => $memberships,
            'membership' => $membership,
            'orga' => $orga,
            'form' => $form->createView(),
            'article' => $article,
            'available' => $available,
            'currentDay' => $day,
            'day' => $day->format('d-m-Y'),
        ];
    }


    /**
     * @Route(
     *      "/user/membership/{login}/news/{id}/remove",
     *      name="news_remove"
     * )
     * @Template()
     *
     * @param mixed $login
     * @param mixed $id
     */
    public function removeAction($login, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_NEWS_EDIT');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

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
            if ($m->getOrganization()->getLogin() == $login) {
                $membership = $m;
                break;
            }
        }

        if (!$membership) {
            throw $this->createNotFoundException('Membership or organization not found for login '.$login);
        }

        if (!$membership->hasPermission('news')) {
            return $this->createAccessDeniedResponse();
        }

        $orga = $membership->getOrganization();

        /** @var $article Article */
        $article = $em->createQueryBuilder()
            ->select('a')
            ->from('EtuModuleNewsBundle:Article', 'a')
            ->where('a.id = :id')
            ->andWhere('a.orga = :orga')
            ->setParameter('orga', $orga->getId())
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $em->remove($article);
        $em->flush();

        return $this->redirect($this->generateUrl('news_index'));
    }
}
