<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

/* Contact */
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Symfony\Component\HttpFoundation\Request; /* Request */
use App\Entity\ContactDB1; /* Database */

/* Mailer */
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


/**
 *
 * default controller class
 */
class DefaultController extends AbstractController
{
    /**
     * @return render('home/index.html.twig')
     */
    #[Route('/home', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * contact page
     *
     * @link https://symfony.com/doc/current/mailer.html mailer参照
     *
     * @param \ManagerRegistry $doctrine
     * @param \MailerInterface $mailer
     *
     * @return render('home/result.html.twig')  もしメールが送信された場合
     * @return render('home/contact_us.html.twig') 通常時
     */
    /* contact page */
    #[Route('/contact_us', name: 'app_contact')]
    public function createAction(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer)
    {
        $contact = new ContactDB1(); /* DB obj instance */
        # form
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label' => 'name', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('email', TextType::class, array('label' => 'email', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('subject', TextType::class, array('label' => 'subject', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('message', TextareaType::class, array('label' => 'message', 'attr' => array('class' => 'form-control')))
            ->add('submit', SubmitType::class, array('label' => 'submit', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-top:15px')))
            ->getForm();
        # Handle form response
        $form->handleRequest($request);

        # check if form is sent
        if($form->isSubmitted() &&  $form->isValid()) {

            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $subject = $form['subject']->getData();
            $message = $form['message']->getData();

            # set data
            $contact->setName($name);           // name
            $contact->setEmail($email);         // email
            $contact->setSubject($subject);     // title
            $contact->setMessage($message);     // message

            # save data in DB
            // $em = $this->getDoctrine()->getManager();
            $em = $doctrine->getManager();
            $em->persist($contact);
            $em->flush();   // save

            $message = (new Email())                          // change from Swift_Message()
            ->from('seita99615@gmail.com')         // My(company) email address
            ->to($email)                                  // customer's email address
            ->subject('*** auto reply ***')        // title
            ->text('Sending emails is fun again!')   // message
            ->html('<p>See Twig integration for better HTML integration!</p>');

            // Swift Mailer => NO MORE USING
            /*
            ->setSubject($subject)
                ->setFrom('seita99615@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView('hello/email.txt.twig',[
                        'name' => $name
                    ])
                );
                $this->get('mailer')->send($message);
            */

            $mailer->send($message);

            return $this->render('home/result.html.twig');
        }

        return $this->render('home/contact_us.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
