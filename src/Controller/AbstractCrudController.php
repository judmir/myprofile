<?php

namespace App\Controller;

use App\Entity\EntityInterface;
use App\Repository\OwnerDataRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCrudController extends AbstractController
{
    public const AREA = null;
    public const PREFIX = null;

    public function index(OwnerDataRepositoryInterface $repository): Response
    {
        return $this->render(sprintf('%s/%s.html.twig', static::AREA, static::PREFIX), array(
            'data' => $repository->getOwnerData($this->getUser()),
        ));
    }

    public function save(Request $request, string $formTypeClass, EntityInterface $object): Response
    {
        $form = $this->createForm($formTypeClass, $object);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();

            $this->addFlash('success', 'messages.item_saved');

            return $this->redirectToRoute(sprintf('%s_%s_index', static::AREA, static::PREFIX));
        }

        return $this->render(sprintf('%s/save.html.twig', static::AREA), [
            'form' => $form->createView(),
            'back_path' => sprintf('%s_%s_index', static::AREA, static::PREFIX),
            'title' => sprintf('form.%s.head.title', static::PREFIX)
        ]);
    }

    public function delete(Request $request, EntityInterface $entity): Response
    {
        if ($this->isCsrfTokenValid('delete' . $entity->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($entity);
            $entityManager->flush();

            $this->addFlash('success', 'messages.item_removed');
        }

        return $this->redirectToRoute(sprintf('%s_%s_index', static::AREA, static::PREFIX));
    }
}
