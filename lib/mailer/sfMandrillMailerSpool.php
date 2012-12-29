<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Grad
 * Date: 29-12-12
 * Time: 18:10
 * To change this template use File | Settings | File Templates.
 */
class sfMandrillMailerSpool
{
    protected $mailer;
    protected $messageLimit = 10;
    protected $timeLimit = 30;

    public function __construct(sfMandrillMailer $mailer) {
        $this->mailer = $mailer;
    }

    public function setMessageLimit($limit) {
        $this->messageLimit = $limit;
    }

    public function setTimeLimit($limit) {
        $this->timeLimit = $limit;
    }

    public function flushQueue() {
        $sent = 0;

        $messages = OutboundEmailTable::getInstance()->createQuery('m')
            ->orderBy('m.created_at ASC')
            ->limit($this->messageLimit)
            ->execute();

        foreach($messages as $message) {
            $success = $this->mailer->send($message);
            if ($success) {
                $message->delete();
                $sent++;
            }
        }

        return $sent;
    }
}
