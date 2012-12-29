<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Grad
 * Date: 29-12-12
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */
class sfMandrillMailer
{
    protected $options;
    public function __construct(sfEventDispatcher $dispatcher, $options) {
        $this->options = $options;
    }

    public function compose($from = null, $to = null, $subject = null, $body = null)
    {
        return OutboundEmail::newInstance()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ;
    }

    protected $spool;
    public function getSpool() {
        if (!isset($this->spool)) $this->spool = new sfMandrillMailerSpool($this);

        return $this->spool;
    }

    public function flushQueue() {
        return $this->getSpool()->flushQueue();
    }

    public function send(OutboundEmail $email) {
        $data = array('key' => $this->options['api_key'], 'async' => false);

        $data['message'] = $email->getMessageDataObject();

        if ($email->hasTemplate()) {
            $endpoint = '/messages/send-template.json';
            $data['template_name'] = $email->template_name;
            $data['template_content'] = json_decode($email->template_content);
        } else {
            $endpoint = '/messages/send.json';
        }

        $uri = 'https://mandrillapp.com/api/1.0'.$endpoint;
        $data = json_encode($data);

        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );

        $result = curl_exec($ch);


//        var_dump($result);
//        die();
        return true;
    }
}
