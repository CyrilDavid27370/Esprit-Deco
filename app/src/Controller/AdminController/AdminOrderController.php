<?php

namespace App\Controller\AdminController;

use App\Entity\Order;
use App\Form\OrderStatusType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminOrderController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/admin/order', name: 'app_admin_order_list')]
    public function orderList(): Response
    {
        $orders = $this->orderRepository->findAllOrderedByDate();

        return $this->render('admin/order_list.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/admin/order/{id}/status', name: 'app_admin_order_status', requirements: ['id' => '\d+'])]
    public function editStatus(Request $request, #[MapEntity] Order $order): Response
    {
        $form = $this->createForm(OrderStatusType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Statut de la commande #' . $order->getId() . ' modifié avec succès.');
            return $this->redirectToRoute('app_admin_order_list');
        }

        return $this->render('admin/order_status.html.twig', [
            'form'  => $form,
            'order' => $order,
        ]);
    }
}
