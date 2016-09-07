<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Entity\Beer;
use Application\Form\Beer as BeerForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $entityManager->getRepository('Application\Entity\Beer');

        $beers = $repository->findAll();

        return new ViewModel(array(
            'beers' => $beers
        ));
    }

    public function saveAction()
    {
        $form = new BeerForm();
        $request = $this->getRequest();
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        if($this->params()->fromRoute('id',0))
        {
            $id =  $this->params()->fromRoute('id',0);
            $beer = $entityManager->find('Application\Entity\Beer',$id);

            $hydrator = new DoctrineHydrator($entityManager);
            $dataArray = $hydrator->extract($beer);
            $form->setData($dataArray);
            $form->setAttribute('action',$id);

            if($request->isPost())
            {
                $form->setData($request->getPost());
                if($form->isValid())
                {
                    $data = $request->getPost()->toArray();

                    $entity = $entityManager->getReference('Application\Entity\Beer', $id);

                    $beer = $hydrator->hydrate($data, $entity);

                    $entityManager->persist($beer);
                    $entityManager->flush();

                    return $this->redirect()->toRoute('home', array('action'=>'index'));
                }
            }
        }

        if($request->isPost())
        {
            $form->setData($request->getPost());
            if($form->isValid())
            {
                $data = $request->getPost()->toArray();
                $beer = new Beer();
                $beer->setName($data['name']);
                $beer->setStyle($data['style']);
                $beer->setImg($data['img']);

                $entityManager->persist($beer);
                $entityManager->flush();

                return $this->redirect()->toRoute('home', array('action'=>'index'));
            }
        }

        return new ViewModel(array('form'=>$form));
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute("id", 0);
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $beer = $entityManager->find('Application\Entity\Beer', $id);

        $entityManager->remove($beer);
        $entityManager->flush();

        return $this->redirect()->toRoute('home', array('action'=>'index'));
    }

}
