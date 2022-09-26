<?php

namespace App\Controller\Admin;

use App\Entity\{Carrier, Category, Offer, Order, Product, Shop, User};
use App\Entity\Model\{Customer,Shipper,Supplier};
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud,Dashboard,MenuItem,UserMenu};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('WiniWini')->renderSidebarMinimized();
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateTimeFormat('medium', 'short');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $me = parent::configureUserMenu($user);

        if($user instanceof User) {
            if($user->getAvatar())
                $me->setAvatarUrl('/uploads/'.$user->getAvatar());
            $me->setName($user->getName());
        }

        return $me;
    }

    public function configureMenuItems(): iterable
    {
        //if ($this->isGranted('ROLE_ADMIN')) {}
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard'),// yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
            MenuItem::section('Orders'),
            MenuItem::linkToCrud('Orders', 'fa fa-shopping-cart', Order::class),
            MenuItem::linkToCrud('Partners', 'fa fa-bank', Shop::class),
            MenuItem::linkToCrud('Carriers', 'fa fa-truck', Carrier::class),
            MenuItem::section('Products'),
            MenuItem::linkToCrud('Offres', 'fa fa-gift', Offer::class),
            MenuItem::linkToCrud('Products', 'fa fa-list', Product::class),
            MenuItem::linkToCrud('Categories', 'fa fa-table', Category::class),
            MenuItem::section('Users'),
            MenuItem::linkToCrud('Customers', 'fa fa-user-plus', Customer::class),
            MenuItem::linkToCrud('Suppliers', 'fa fa-users-gear', Supplier::class),
            MenuItem::linkToCrud('Shippers', 'fa fa-user-group', Shipper::class),
        ];
    }

    #[Route('/admin', name: 'admin'),]
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $controller = $this->isGranted('ROLE_SUPP') ? ProductCrudController::class : UserCrudController::class;

        if ('jane' === $this->getUser()->getUserIdentifier()) return parent::index();
        return $this->isGranted('ROLE_ADMIN') ? $this->render('admin/dashboard.html.twig', ['userStats' => $this->userRepository->count([])]) :
        $this->redirect($routeBuilder->setController($controller)->generateUrl());
    }
}
