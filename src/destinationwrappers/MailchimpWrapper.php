<?php
/**
 * List Builder plugin for Craft CMS 3.x
 *
 * Email mailing list popup notification exit
 *
 * @link      https://mattgrayisok.com
 * @copyright Copyright (c) 2018 Matt Gray
 */

namespace mattgrayisok\listbuilder\destinationwrappers;

use mattgrayisok\listbuilder\ListBuilder;
use mattgrayisok\listbuilder\exceptions\DestinationException;
use mattgrayisok\listbuilder\exceptions\SignupSubmitException;

use \DrewM\MailChimp\MailChimp;
use \DrewM\MailChimp\Batch;

use Craft;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class MailchimpWrapper implements DestinationWrapperInterface
{
    public function syncSignups($signups, $destination = null)
    {

        if(is_null($destination)){
            return;
        }

        $config = json_decode($destination->config, true);
        $listId = $config['listid'];
        $apiKey = $config['apikey'];

        $attempts = [];

        $MailChimp = new MailChimp($apiKey);

        foreach($signups as $signup){

            $result = $MailChimp->post("lists/$listId/members", [
				'email_address' => $signup->email,
				'status'        => 'subscribed',
			]);

            if($MailChimp->success()){
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
                continue;
            }

            $errorBody = json_decode($MailChimp->getLastResponse()['body']);

            //Handle known errors
            if (!is_null($errorBody)) {
                if($errorBody->title == 'Member Exists'){
                    $attempts[] = ['signupId' => $signup->id, 'success' => true];
                    continue;
                }else if($errorBody->title == 'API Key Invalid'){
                    throw new DestinationException("Invalid API key");
                }else if($errorBody->title == 'API Key Missing'){
                    throw new DestinationException("Invalid API key");
                }else if($errorBody->title == 'Resource Not Found'){
                    throw new DestinationException("Invalid list ID");
                }
            }

            //Handle unexpected errors
            if(!isset($config['disableonerror']) || $config['disableonerror']){
                //If we're disabling on unexpected error
                $error = "An unknown error occured communicating with Mailchimp";
                if(!is_null($errorBody) && isset($errorBody->title)){
                    $error = $errorBody->title;
                }
                throw new DestinationException($error);
            }else{
                //If we're ignoring errors just set this as a failure so it isn't retried over and over
                $attempts[] = ['signupId' => $signup->id, 'success' => false];
            }

        }

        return $attempts;

    }
}
