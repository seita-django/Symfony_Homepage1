<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

/* お問い合せ用 */
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Symfony\Component\HttpFoundation\Request; /* リクエスト */
use App\Entity\ContactForm; /* データベース */

/* Mailer */
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;


class DefaultController extends AbstractController
{
    #[Route('/home', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /* お問い合せページ */
    #[Route('/contact_us', name: 'app_contact')]
    public function createAction(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer)
    {
        $contact = new ContactForm; /* DBオブジェクトのインスタンス化 */
        # フォームのフィールドを追加
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label' => 'name', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('email', TextType::class, array('label' => 'email', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('subject', TextType::class, array('label' => 'subject', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('message', TextareaType::class, array('label' => 'message', 'attr' => array('class' => 'form-control')))
            ->add('submit', SubmitType::class, array('label' => 'submit', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-top:15px')))
            ->getForm();
        # Handle form response
        $form->handleRequest($request);

        # formが送信されたかチェック
        if($form->isSubmitted() &&  $form->isValid()) {

            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $subject = $form['subject']->getData();
            $message = $form['message']->getData();

            # データをセット
            $contact->setName($name);           // 名前をセット
            $contact->setEmail($email);         // メアドをセット
            $contact->setSubject($subject);     // 要件をセット
            $contact->setMessage($message);     // メッセージをセット

            # DBにデータを保存
            // $em = $this->getDoctrine()->getManager();
            $em = $doctrine->getManager();
            $em->persist($contact);
            // $em->flush();   // DBに保存

            $message = (new Email())                          // Swift_Message()から変更
            ->from('seita99615@gmail.com')         // 自分(会社の公式)のメールアドレス
            ->to($email)                                  // メッセージ送信者のメールアドレス
            ->subject('*** 自動返信です ***')        // 要件名
            ->text('Sending emails is fun again!')   // メッセージ内容
            ->html('<p>See Twig integration for better HTML integration!</p>');

            // 以下はSwift Mailer => 廃止
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
