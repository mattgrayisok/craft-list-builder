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

use Craft;

/**
 * @author    Matt Gray
 * @package   ListBuilder
 * @since     1.0.0
 */
class EmailOctopusWrapper implements DestinationWrapperInterface
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

        foreach($signups as $signup){
            /*Craft::info(
                "Syncing email ".$signup->email." via webhook",
                __METHOD__
            );*/

            $url = 'https://emailoctopus.com/api/1.5/lists/'.$listId.'/contacts';

            $body = json_encode([
                    'api_key' => $apiKey,
                    'email_address' => $signup->email,
                    //'fields' => [],
                    //'status' => 'SUBSCRIBED',
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body))
            );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $serverResponse = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close ($ch);

            $decodedResponse = json_decode($serverResponse);

            //The request is only considered a success if we get a response containing an id
            if (!is_null($decodedResponse) && isset($decodedResponse->id)) {
                //Record that we made the attempt and it was a success
                $attempts[] = ['signupId' => $signup->id, 'success' => true];
                continue;
            }

            //Handle known errors
            if (!is_null($decodedResponse) &&
                isset($decodedResponse->error)
            ) {

                if($decodedResponse->error->code == 'MEMBER_EXISTS_WITH_EMAIL_ADDRESS') {
                    $attempts[] = ['signupId' => $signup->id, 'success' => true];
                    continue;
                } elseif($decodedResponse->error->code == 'API_KEY_INVALID') {
                    throw new DestinationException("Invalid API key");
                } elseif($decodedResponse->error->code == 'UNAUTHORISED') {
                    throw new DestinationException("Authorisation error. Check your list ID");
                }
            }

            //Handle unexpected errors
            if(!isset($config['disableonerror']) || $config['disableonerror']){
                //If we're disabling on unexpected error
                $error = "Problem communicating with Email Octopus";
                if(!is_null($decodedResponse) && isset($decodedResponse->error)){
                    $error = $decodedResponse->error->message;
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
