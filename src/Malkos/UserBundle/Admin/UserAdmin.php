<?php

namespace Malkos\UserBundle\Admin;

use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('username', 'text', array('label' => 'User Name'))
            ->add('email', 'text', array('label' => 'Email Name'))
            ->add('password', null, array('label' => 'Password'))
            ->add('enabled', null, ['required' => false]) //if no type is specified, SonataAdminBundle tries to guess it
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('email')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->addIdentifier('email', null, array(
                'route' => array('name' => 'show')
            ))
            ->add('enabled')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper

            /*
             * The default option is to just display the value as text (for boolean this will be 1 or 0)
             */
            ->add('username')
            ->add('email')
            ->add('enabled')
        ;

    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }

    public function preUpdate($user)
    {
        $user->setPlainPassword($user->getPassword());
        $this->getUserManager()->updatePassword($user);
    }

    public function prePersist($user)
    {
        $user->setPlainPassword($user->getPassword());
        $this->getUserManager()->updateUser($user);
    }

    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    protected function configureTabMenu(ItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
//        parent::configureTabMenu($menu, $action, $childAdmin);

        $menu->addChild('comments', array('attributes' => array('dropdown' => true)));
        $menu['comments']->addChild('list', array('uri' => $this->generateUrl('list', array('id' => 1))));
        $menu['comments']->addChild('create', array('uri' => $this->generateUrl('create', array('id' => 2))));

        parent::configureTabMenu($menu, $action, $childAdmin);
    }
}