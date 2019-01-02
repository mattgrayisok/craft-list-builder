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
use mattgrayisok\listbuilder\Enums;
use mattgrayisok\listbuilder\exceptions\DestinationException;
use mattgrayisok\listbuilder\exceptions\SignupSubmitException;

use Craft;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class DripWrapper implements DestinationWrapperInterface
{
    public function syncSignups($signups, $destination = null)
    {

        if(is_null($destination)){
            return;
        }

        $config = json_decode($destination->config, true);
        $accountId = $config['accountid'];
        $apiKey = $config['apikey'];
        $campaignId = $config['campaignid'];

        $attempts = [];

        $drip = new \Drip\Client($apiKey, $accountId);

        foreach ($signups as $signup) {

            $response = $drip->subscribe_subscriber([
                'campaign_id' => $campaignId,
                'email' => $signup->email,
                'eu_consent' => $signup->consent == Enums::CONSENT__YES ? 'granted' : 'denied'
            ]);

            if(is_a($response, \Drip\SuccessResponse::class)){
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
                continue;
            }

            //Handle known errors
            $errors = $response->get_errors();
            if(sizeof($errors) > 0){
                $error = $errors[0];
                $code = $error->get_code();
                if($code == 'uniqueness_error'){
                    $attempts[] = ['signupId' => $signup->id, 'success' => true];
                    continue;
                }
                if($code == 'authentication_error'){
                    throw new DestinationException("Invalid API key");
                }
                if($code == 'authorization_error'){
                    throw new DestinationException("This Drip user doesn't have permission to add subscribers to campaigns");
                }
                if($code == 'not_found_error'){
                    throw new DestinationException("Invalid account or campaign ID");
                }
            }

            //Handle unexpected errors
            if(!isset($config['disableonerror']) || $config['disableonerror']){
                //If we're disabling on unexpected error
                $error = "An unknown error occured communicating with Drip";
                if(sizeof($errors) > 0){
                    $errorObj = $errors[0];
                    $error = $errorObj->get_message();
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
