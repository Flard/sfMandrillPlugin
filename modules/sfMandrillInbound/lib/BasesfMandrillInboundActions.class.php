<?php

/**
 * Base actions for the sfMandrillPlugin sfMandrillInbound module.
 * 
 * @package     sfMandrillPlugin
 * @subpackage  sfMandrillInbound
 * @author      Grad van Horck <grad@dreksbak.nl>
 */
abstract class BasesfMandrillInboundActions extends sfActions
{
    /**
     * Action that processes a Mandrill inbound event
     * @param sfWebRequest $request
     * @see http://help.mandrill.com/entries/22092308-what-is-the-format-of-inbound-email-webhooks
     */
    public function executeProcess(sfWebRequest $request) {

        // Load the "mandrill_events" from the POST-data
        $rawEvents = $request->getPostParameter('mandrill_events', false);
        if ($rawEvents === false) {
            throw new sfException('mandrill_events parameter not found');
        }

        // decode the JSON payload
        $events = json_decode($rawEvents);

        // Loop through the events (should be one, but you never know)
        foreach($events as $event) {

            // Log the event (if the logging is enabled)
            if (sfConfig::get('sf_logging_enabled')) {
                $this->getEventDispatcher()->notify(new sfEvent('sfMandrillPlugin', 'application.log', array('Received event "'.$event->event.'"', 'priority' => sfLogger::INFO)));
            }

            // inbound emails have event type 'inbound'
            if ($event->event == 'inbound') {

                // Created a new message based on the event data
                $message = InboundEmail::fromMandrillEvent($event);

                // Save the message
                $message->save();

                // Throw an event, so other plugins can handle this e-mail.
                $this->getEventDispatcher()->notify(new sfEvent('sfMandrillPlugin', 'inbound.postSave', array('event' => $event, 'message' => $message)));
            }
        }

        return sfView::NONE;

    }
}
