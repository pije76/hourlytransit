<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Contact extends Controller
{
    function showContactForm(){
        return view('contact-form');
    }

    function sendMail(Request $request){

        $subject = "Contact dari " . $request->input('name');
        $name = $request->input('name');
        $emailAddress = $request->input('email');
        $message = $request->input('message');

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            // Pengaturan Server
           // $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.live.com';                  // Specify main and backup SMTP servers
            // $mail->Host = 'smtp.gmail.com';                  // Specify main and backup SMTP servers
            $mail->SMTPDebug = 2;
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'panjul76@hotmail.com';                 // SMTP username
            // $mail->Username = 'panjul76@gmail.com';                 // SMTP username
            $mail->Password = '@Ciangsana8';                           // SMTP password
            // $mail->Password = '@Pratomo76';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            // $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            // $mail->Port = 465;                                    // TCP port to connect to
            $mail->Port = 587;                                    // TCP port to connect to

            // Siapa yang mengirim email
            $mail->setFrom("panjul76@gmail.com", "Panjianom");

            // Siapa yang akan menerima email
            $mail->addAddress('panjul76@hotmail.com', 'Panjianom');     // Add a recipient
            // $mail->addAddress('ellen@hotmail.com');               // Name is optional

            // ke siapa akan kita balas emailnya
            $mail->addReplyTo($emailAddress, $name);

            // $mail->addCC('cc@hotmail.com');
            // $mail->addBCC('bcc@hotmail.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name


            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = $message;

            $mail->send();

            $request->session()->flash('status', 'Terima kasih, kami sudah menerima email anda.');
            return view('contact-form');

        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    }
}
