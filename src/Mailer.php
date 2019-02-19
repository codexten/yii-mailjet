<?php

namespace codexten\yii\mailjet;

use Exception;
use Mailjet\Client;
use Mailjet\Resources;
use yii\base\InvalidConfigException;
use codexten\yii\mailqueue\MailQueue;

/**
 * This component allow user to send an email
 */
class Mailer extends MailQueue
{

    private $apikey;

    private $secret;
    /**
     * @var string
     */
    public $apiSecret;
    /**
     * @var boolean
     */
    public $enable = true;
    /**
     * @var string
     */
    public $apiVersion = 'v3.1';
    /**
     * @var string
     */
    public $apiUrl;
    /**
     * @var bool
     */
    public $secured = true;
    /**
     * @inheritdoc
     */
    public $messageClass = Message::class;

    public function init()
    {

        if (!$this->apikey) {
            throw new InvalidConfigException(sprintf('"%s::apikey" cannot be null.', get_class($this)));
        }

        if (!$this->secret) {
            throw new InvalidConfigException(sprintf('"%s::secret" cannot be null.', get_class($this)));
        }
        parent::init();
    }

    /**
     * Sets the API secret key for Mailjet
     *
     * @param string $secret
     *
     * @throws InvalidConfigException
     */
    public function setSecret($secret)
    {

        if (!is_string($secret)) {
            throw new InvalidConfigException(sprintf('"%s::secret" should be a string, "%s" given.', get_class($this),
                gettype($apikey)));
        }
        $trimmedSecret = trim($secret);
        if (!strlen($trimmedSecret) > 0) {
            throw new InvalidConfigException(sprintf('"%s::secret" length should be greater than 0.',
                get_class($this)));
        }
        $this->secret = $trimmedSecret;

    }

    /**
     * Sets the API key for Mailjet
     *
     * @param string $apikey the Mailjet API key
     *
     * @throws InvalidConfigException
     */
    public function setApikey($apikey)
    {
        if (!is_string($apikey)) {
            throw new InvalidConfigException(sprintf('"%s::apikey" should be a string, "%s" given.', get_class($this),
                gettype($apikey)));
        }
        $trimmedApikey = trim($apikey);
        if (!strlen($trimmedApikey) > 0) {
            throw new InvalidConfigException(sprintf('"%s::apikey" length should be greater than 0.',
                get_class($this)));
        }
        $this->apikey = $trimmedApikey;
    }

    /**
     * @param \yii\mail\MessageInterface $message
     *
     * @return bool
     * @throws Exception
     */
    public function sendMessage($message)
    {
        try {
            if ($this->apikey === null) {
                throw new InvalidConfigException('API Key is missing');
            }
            if ($this->secret === null) {
                throw new InvalidConfigException('API Secret is missing');
            }
            $settings = [
                'secured' => $this->secured,
                'version' => $this->apiVersion,
            ];
            if ($this->apiUrl !== null) {
                $settings['url'] = $this->apiUrl;
            }
            $client = new Client($this->apikey, $this->secret, $this->enable, $settings);
            $fromEmails = Message::convertEmails($message->getFrom());
            $toEmails = Message::convertEmails($message->getTo());
            $mailJetMessage = [
                // 'FromEmail' => $fromEmails[0]['Email'],
                'From' => $fromEmails[0],
                'To' => $toEmails,
            ];
            /*
            if (isset($fromEmails[0]['Name']) === true) {
                $mailJetMessage['FromName'] = $fromEmails[0]['Name'];
            }
            */
            /*
            $sender = $message->getSender();
            if (empty($sender) === false) {
                $sender = Message::convertEmails($sender);
                $mailJetMessage['Sender'] = $sender[0];
            }
            */
            $cc = $message->getCc();
            if (empty($cc) === false) {
                $cc = Message::convertEmails($cc);
                $mailJetMessage['Cc'] = $cc;
            }
            $bcc = $message->getBcc();
            if (empty($cc) === false) {
                $bcc = Message::convertEmails($bcc);
                $mailJetMessage['Bcc'] = $bcc;
            }
            $attachments = $message->getAttachments();
            if ($attachments !== null) {
                $mailJetMessage['Attachments'] = $attachments;
            }
            //Get inilined attachments
            $inlinedAttachments = $message->getInlinedAttachments();
            if ($inlinedAttachments !== null) {
                $mailJetMessage['InlinedAttachments'] = $inlinedAttachments;
            }
            $headers = $message->getHeaders();
            if (empty($headers) === false) {
                $mailJetMessage['Headers'] = $headers;
            }
            $mailJetMessage['TrackOpens'] = $message->getTrackOpens();
            $mailJetMessage['TrackClicks'] = $message->getTrackClicks();
            $templateModel = $message->getTemplateModel();
            if (empty($templateModel) === false) {
                $mailJetMessage['Variables'] = $templateModel;
            }
            $templateId = $message->getTemplateId();
            if ($templateId === null) {
                $mailJetMessage['Subject'] = $message->getSubject();
                $textBody = $message->getTextBody();
                if (empty($textBody) === false) {
                    $mailJetMessage['TextPart'] = $textBody;
                }
                $htmlBody = $message->getHtmlBody();
                if (empty($htmlBody) === false) {
                    $mailJetMessage['HTMLPart'] = $htmlBody;
                }
                $sendResult = $client->post(Resources::$Email, [
                    'body' => [
                        'Messages' => [
                            $mailJetMessage,
                        ],
                    ],
                ]);
            } else {
                $mailJetMessage['TemplateID'] = $templateId;
                $processLanguage = $message->getTemplateLanguage();
                if ($processLanguage === true) {
                    $mailJetMessage['TemplateLanguage'] = $processLanguage;
                }
                $sendResult = $client->post(Resources::$Email, [
                    'body' => [
                        'Messages' => [
                            $mailJetMessage,
                        ],
                    ],
                ]);
            }

            //TODO: handle error codes and log stuff
            return $sendResult->success();
        } catch (Exception $e) {
            throw $e;
        }
    }
}