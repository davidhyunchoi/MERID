<?php

namespace Application\Service;

use Aws\Ses\SesClient,
    Application\Document\UserDocument,
    Application\Document\VerificationDocument,
    Application\Document\ProjectDocument;

class Email {
    /*
     * @var String
     */
    private $accesskey;
    
    /*
     * @var String
     */
    private $secretkey;
    
    /*
     * @var String
     */
    private $fromemail;
    
     /*
     * @var String
     */
    private $fromname;
    
    /*
     * @var String
     */
    private $region;
    
    public function __construct($config){
        $this->setOptions($config);
    }
    
    public function setOptions($options){
        $this->accesskey = isset($options['accesskey'])? $options['accesskey']: $this->accesskey;
        $this->secretkey = isset($options['secretkey'])? $options['secretkey']: $this->secretkey;
        $this->fromemail = isset($options['fromemail'])? $options['fromemail']: $this->fromemail;
        $this->fromname = isset($options['fromname'])? $options['fromname']: $this->fromname;
        $this->region = isset($options['region'])? $options['region']: $this->region;
    }
    
    /**
     * 
     * @param type $toEmail address to where email should be sent
     * @param type $message associative array containing html and subject
     * @throws \Application\Service\Exception
     */
    public function sendEmail($toEmail, $message){        
        $client = SesClient::factory(array(
            'key'    => $this->accesskey,
            'secret' => $this->secretkey,
            'region' => $this->region,
        ));

        //Now that you have the client ready, you can build the message 
        $msg = array();
        $msg['Source'] = "cu.merid@gmail.com";
        //ToAddresses must be an array
        $msg['Destination']['ToAddresses'][] = $toEmail;

        $msg['Message']['Subject']['Data'] = $message['subject'];
        $msg['Message']['Subject']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Text']['Data'] = $message['text'];
        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";

        try{
             $client->sendEmail($msg);
        } catch (Exception $e) {
             //An error happened and the email did not get sent
             throw $e;
        } 
    }
    
    public function registerResearcherMessage($baseUrl, UserDocument $user, VerificationDocument $code){
        return array('text'=>"User with email $user->email has requested a researcher accout.\n"
                . "Go to $baseUrl/user/authAccount?id=$user->id&urlCode=$code->id"
                . " to send an account verification email to the user.",
            'subject' => "Researcher Account Requested - $user->email");
    }
    
    public function activateAccountMessage($baseUrl, UserDocument $user, VerificationDocument $code){
        return array('text'=>"Your request for an account has been approved!\n"
            . "Please go to $baseUrl/user/verify?urlCode=$code->urlCode"
                . " to activate your account. Your verification code is below.\n"
            . "code: $code->code ",
            'subject' => "MERID Account Confirmation");
    }
    
    public function inviteToMeridMessage($baseUrl, UserDocument $user, VerificationDocument $code){
        return array('text'=>"You have been invited to the MERID system!\n"
            . "please go to $baseUrl/user/verify?urlCode=$code->urlCode"
                . " to activate your account. Your verification code is below.\n"
            . "code: $code->code ",
            'subject' => "MERID System Invitation");
    }
    
    public function invitedToProject(UserDocument $user, ProjectDocument $project){
        return array('text'=> "You have been invited to a project in the MERID system.\n"
            . "", 'subject' => "MERID Project Invite - $project->name");
    } 
    
    public function invitedToSurvey($baseUrl, UserDocument $user, SurveyDocument $survey){
        return array('text', "", 'subject' => "MERID Survey Invite - $survey->title");
    }
}
